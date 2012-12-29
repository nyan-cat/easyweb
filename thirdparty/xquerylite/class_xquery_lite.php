<?php
// ##################################################################################
// Title                     : XqueryLite (class_xquery_lite.php)
// Version                   : 0.4 beta
// Author                    : Luis Argerich (lrargerich@yahoo.com)
// Last modification date    : 05-21-2002
// Description               : This is an implementation of a subset of the Xquery
//                             language with intention to add new features in 
//                             next releases. It is based on flwr expressions.
// ##################################################################################
// History: 
// 05-21-2002                : First release of this class from my prototype engine
// 05-17-2002                : Some changes to solve W3C use-cases
// ##################################################################################
// To-Dos:
// ##################################################################################
// How to use it:
// ##################################################################################


class XqueryLite {
  var $result_sets=Array();
  var $bindings=Array();

  function init() {
    $this->result_sets=Array();
    $this->bindings=Array(); 
  }

  function _tokenize($exp) {
    $exprs=Array();
    $current='';
    $level=0;
    $tok = strtok($exp," \n\t");
    while ($tok) {
      // Now see if there's a "{" in the token or a "}" in the token
      for($i=0;$i<strlen($tok);$i++) {
        if(substr($tok,$i,1)=="{") {
          $level++;
        }
        if(substr($tok,$i,1)=="}") {
          $level--;
        }  
      }
      
      if($level==0) {
        if(in_array(trim(strtoupper($tok)),Array("FOR","LET","RETURN","WHERE"))) {
          if(strlen($current)>0) {
            $exprs[]=$current; 
            $current='';
          }
        }
      }
      $current.=$tok.' ';
      $tok = strtok(" \n\t");
    }
    if(strlen($current)>0) {
      $exprs[]=$current; 
      $current=$tok;
    }
    
    return $exprs;
  }

  // This function is the "main" function of the flwr-lite engine, it evaluates a flwr expression
  // returning an XML fragment as a string.
  // The function won't be called only for top-level flwr expressions but for inner sub-expressions
  // recursively as well. 
  function evaluate_xqueryl($expr) {
    $result='';
    $qexpr='';
    $i=0;
    $chr=substr($expr,$i,1);
    $level=0;
    $query='';
  
    while($i<strlen($expr)) {
      if($chr=='{') {
        $level++; 
      }
      if($chr=='}') {
        $level--; 
      }
      if( (($level>0) && ($chr<>'{')) || ($level>1)) {
        $query.=$chr;
      } 
      if($chr=='}') {
        if($level==0) {
          if(strlen($query)>0) {
            $result.=$this->_parse_query($query); 
          } 
          $query='';
        }
      }
      if( ($chr<>"{") && ($chr<>"}") &&($level==0) ){
        $result.=$chr;
      }
      $i++;
      $chr=substr($expr,$i,1);   
    }
   
    return $result;
  }

  // This function returns the root element tagname of an XML
  // fragment that is later used for auto-adding the root
  // path to path expressions
  function _get_root_name($node) {
    $name=$node->node_name();
    return $name; 
  }

  // This parses a flwr-lite FOR expression binding and
  // returns the name of the flwr-lite variable associated
  // the nodeset is stored in the result_sets array.
  // A flwr-lite FOR expression can be:
  // FOR $name IN xmlmem($xml)/xpath_expression 
  // or
  // FOR $name IN xmldoc($xml)/xpath_expression
  // or
  // FOR $name IN $name/xpath_expression
  function _parse_for($expr) {
    $result='';
    $tokens=explode(" ",$expr);
    $name=$tokens[1];
    if(strtoupper($tokens[2])<>"IN") {
      trigger_error("Invalid FOR expresion $expr <br/>",E_USER_WARNING);
      return false; 
    }
    $path=$tokens[3];
  
    // while the beginning of path is not $ or document then 
    // queues the function and repeat
    $functions=Array();
    $cosa=substr($path,0,6);
    
    while( (substr($path,0,1)<>'$') && (substr($path,0,8)<>"document") && (substr($path,0,6)<>"xmlmem")) {
       preg_match("/([^(]*)\((.*)\)/",$path,$regs);
       $path=$regs[2];
       $path=substr($path,0,strlen($path));
       
       array_unshift($functions,$regs[1]);
    }
    
   
    $parts=explode("/",$path,2);
    $xml_source=$parts[0];
    $path='/'.$parts[1];
    
    // Source maybe xmldoc($path)
    //           or xmlmem($xml)
    //           or $x
    if(substr($xml_source,0,8)=='document') {
      /* PROCESSING FROM A FILE */
      ereg("document\((.*)\)",$xml_source,$regs);
      $source=$regs[1];
      $name_doc=str_replace('"','',$source);
      if(!file_exists($name_doc)) {
        trigger_error("$name_doc file not found", E_USER_WARNING); 
      }
      $doc=xmldocfile($name_doc);
      //$rootname=_get_root_name($doc->document_element());
      //$path='/'.$rootname.$path;
      
      if(!$doc) {
        trigger_error("XML source document $name_doc was not well formed",E_USER_WARNING); 
      }
      
      $xpath=$doc->xpath_init();
      $ctx = $doc->xpath_new_context();
      $result=$ctx->xpath_eval($path);
      $nodes=$result->nodeset;
      foreach($functions as $f) {
           
           if($f=="distinct-values") {$f="distinct";}
           $nodes=$this->$f($nodes);
      }
      $nodeset=Array();
      foreach($nodes as $node) {
        if($node->node_type()==XML_ATTRIBUTE_NODE) {
          
          $nodeset[]=$node->value;
        } else {
          $nodeset[]=$node->dump_node($node);
        }
      }
      unset($xpath);
      unset($doc);
      unset($cts);
      unset($result);
    } elseif(substr($xml_source,0,6)=='xmlmem') {
      /* PROCESSING FROM MEM */
           
      ereg("xmlmem\((.*)\)",$xml_source,$regs);
      $source=$regs[1];
      $source=str_replace('"','',$source);
      
      $name_var=substr($source,1);
      // NOTE THAT THE XML STRING MUST BE GLOBAL
      if(!isset($GLOBALS[$name_var])) {
        trigger_error("$name_var is not visible from here plase use a global string for XML data",E_USER_WARNING);
        return false; 
      }
      $data=$GLOBALS[$name_var];
      if(strlen($data)>0) {
        $doc=xmldoc($data);
        $rootname=$this->_get_root_name($doc->document_element());
        //$path='/'.$rootname.$path;
        
        if(!$doc) {
          trigger_error("XML source was not well formed",E_USER_WARNING); 
        }
        
        $xpath=$doc->xpath_init();
        $ctx = $doc->xpath_new_context();
        $result=$ctx->xpath_eval($path);
        $nodes=$result->nodeset;
        foreach($functions as $f) {
           
           if($f=="distinct-values") {$f="distinct";}
           $nodes=$this->$f($nodes);
        }
        $nodeset=Array();
        foreach($nodes as $node) {
          if($node->node_type()==XML_ATTRIBUTE_NODE) {
            $nodeset[]=$node->value;
            
          } else {
            $nodeset[]=$node->dump_node($node);
          }
        }
      }
      unset($xpath);
      unset($doc);
      unset($cts);
      unset($result);
    } elseif(substr($xml_source,0,1)=='$') {
      
      /* PROCESS FROM A VARIABLE */
      //ereg("xmlmem\((.*)\)",$xml_source,$regs);
      $source=$xml_source;
      
      $var_name=substr($source,1);
      $data=$this->bindings[$var_name];
      if(strlen($data)>0) {
        $doc=xmldoc($data);
        $rootname=$this->_get_root_name($doc->document_element());
        $path='/'.$rootname.$path;
        
        if(!$doc) {
          trigger_error("XML source variable $name_var was not well formed",E_USER_WARNING); 
        }
        
        $xpath=$doc->xpath_init();
        $ctx = $doc->xpath_new_context();
        $result=$ctx->xpath_eval($path);
        $nodes=$result->nodeset;
        foreach($functions as $f) {
           
           if($f=="distinct-values") {$f="distinct";}
           $nodes=$this->$f($nodes);
        }
        $nodeset=Array();
        foreach($nodes as $node) {
          if($node->node_type()==XML_ATTRIBUTE_NODE) {
            $nodeset=$node->value;
            
          } else {
            $nodeset[]=$node->dump_node($node);
          }
        }
      }
      unset($xpath);
      unset($doc);
      unset($cts);
      unset($result);
    } else {
      trigger_error("Invalid xml source $xml_source <br/>",E_USER_WARNING);
      return false; 
    }
    $name_of_name=substr($name,1);
    
    // Here's where the node_set is set but (but!) we may need to apply a function
    $this->result_sets[$name_of_name]=$nodeset;
    return $name_of_name;
  }

  // Functions that can be applied to a resulting nodeset of a FOR expression
  // List: _distinct-values

  // This function eliminate duplicate results from the nodeset
  // We store a normalized representation of each node in the nodeset and

  function _distinct($nodeset) {
    
    $new_nodeset=Array();
    $seen=Array();
    $cant=count($nodeset);
    
    foreach($nodeset as $node) {
      $normalized=$this->normalize_elements($node);
      
      
      if(!in_array($normalized,$seen)) {
        
        $new_nodeset[]=$node;
        $seen[]=$normalized;
        
      }
    }
    $cant=count($new_nodeset);
    
    
    return $new_nodeset; 
  }

  // Normalize can eliminate all the tags
  // If the node has only one child and it is text then just the text is returned
  function _normalize_elements($node) {

    if($node->node_type()==XML_ATTRIBUTE_NODE) {
      
      return $node->value; 
    }
    $data=trim($node->dump_node($node));
    
    
    preg_match_all("/<([^>]*)>[^<]*<\/[^>]*>/",$data,$foo);
    
    if(count($foo[1])==1) {
      
      $data=trim(preg_replace("/<.*>(.*)<\/.*>/","$1",$data));
    } else {
      if($node->node_type()==XML_ELEMENT_NODE) {
        $data=preg_replace("/\n/"," ",$data);
        $data=preg_replace("/\t/"," ",$data);
        $data=preg_replace("/\>\s*\</","><",$data);
      }
    }
    
    return $data;
  }

  // This function parses an expression in the form:
  // $name/xpath_expression
  // outside a FOR expression so it aways returns a
  // string, if the xpath expression returned an element
  // the element is normalized.
  function _parse_var($expr,$norm) {
    $result='';
    
    // If it is a var is $name/expr
    $parts=explode("/",$expr,2);
    $var_name=substr($parts[0],1);
    if(strlen($parts[1])>0) {
      $path="/".$parts[1];
    }
    $data=$this->bindings[$var_name];
    if(strlen($data)==0) {
      return ''; 
    }
    
    
    
    if(strlen($path)>0) {
      $doc=xmldoc($data);
      $rootname=$this->_get_root_name($doc->document_element());
      $path='/'.$rootname.$path;
      
      if(!$doc) {
        trigger_error("cannot evaluate a xpath expression because $data is not xml ",E_USER_WARNING);
      }
      
    
      $xpath=$doc->xpath_init(); 
      $ctx = $doc->xpath_new_context();
      $result_xp=$ctx->xpath_eval($path);
      $nodes=$result_xp->nodeset;
      if(count($nodes)>0) {
        foreach($nodes as $a_node  ) {
          if($norm) {
            $res=$this->_normalize_elements($a_node);
            $result.=$res;
          } else {
            if($a_node->node_type()==XML_ATTRIBUTE_NODE) {
              
              $res=$a_node->value;
            
              $result.=$res;
            } else {
              $res=$a_node->dump_node($a_node); 
              $result.=$res;
            }
          }
        }
      } else {
        $result=''; 
      }
    } else {
      $result=$data;
      /*
      $doc=xmldoc($data);
      $root=$doc->document_element($doc);
      if($norm) {
        $result=_normalize_elements($root); 
      } else {
        print("El resultado es el dump simple <br />");
        if($root->node_type()==XML_ATTRIBUTE_NODE) {
          print("dumping an attribute <br/>");
          $result=$root->value; 
        } else {
          $result=$root->dump_node($root); 
        }
      }
      */
    }
    unset($xpath);
    unset($ctx);
    unset($result_xp);
    unset($doc);
    
    return $result;
  }

  // This function is very similar to _parse_var BUT
  // instead of returning the result or the variable
  // it just counts the number of elements in the nodeset
  function _count_var($expr) {
    $result='';
    
    // If it is a var is $name/expr
    $parts=explode("/",$expr,2);
    $var_name=substr($parts[0],1);
    if(strlen($parts[1])>0) {
      $path="/".$parts[1];
    }
    $data=$this->bindings[$var_name];
    if(strlen($data)==0) {
      return ''; 
    }
    
    
    
    if(strlen($path)>0) {
      $doc=xmldoc($data);
      $rootname=$this->_get_root_name($doc->document_element());
      $path='/'.$rootname.$path;
      
      if(!$doc) {
        trigger_error("cannot evaluate a xpath expression because $data is not xml ",E_USER_WARNING);
      }
      
      $xpath=$doc->xpath_init();
      $ctx = $doc->xpath_new_context();
      $result_xp=$ctx->xpath_eval($path);
      $nodes=$result_xp->nodeset;
      unset($xpath);
      unset($ctx);
      unset($result_xp);
      unset($doc);
    
      return count($nodes);
    } else {
      return 1;
      //$result=$data;
      /*
      $doc=xmldoc($data);
      $root=$doc->document_element($doc);
      if($norm) {
        $result=_normalize_elements($root); 
      } else {
        print("El resultado es el dump simple <br />");
        if($root->node_type()==XML_ATTRIBUTE_NODE) {
          print("dumping an attribute <br/>");
          $result=$root->value; 
        } else {
          $result=$root->dump_node($root); 
        }
      }
      */
    }
    
    return $result;
  }

  // This function parses a flwr-lite where expression returning 
  // true/false depending on the expression value
  // First flwr variables followed or not by an expression are
  // evaluated and replaced by their values
  // then and/or are replaced by &&/||
  // then a PHP eval construction is used to eval the expression
  // :TODO: code this function
  function _parse_where($expr) {
    $result=true;
    $expr=ltrim($expr);
    $wexpr=substr($expr,5);
    
    $wexpr=preg_replace("/([^A-Za-z0-9])and([^A-Za-z0-9])/","$1&&$2",$wexpr);  $wexpr=preg_replace("/([^A-Za-z0-9])or([^A-Za-z0-9])/","$1||$2",$wexpr);
    $wexpr=preg_replace("/([^=><!])=([^=])/","$1==$2",$wexpr);
    
    if(strstr($wexpr,"count")) {
      
    }
    preg_match_all("/count\(([^)]*)\)/",$wexpr,$counts);
    
    for($i=0;$i<count($counts[1]);$i++) {
      
      $cant=$this->_count_var($counts[1][$i]);
      
      $cosa=$counts[0][$i];
      $wexpr=str_replace($cosa,"$cant",$wexpr);
      
    }
    $vars=Array();
    $is_a_var=false;
    $a_var='';
    for($i=0;$i<strlen($wexpr);$i++) {
      $chr=substr($wexpr,$i,1);
      if($chr=="$") {
        $a_var=''; 
        $is_a_var=true;
      }  
      if($is_a_var) {
        if(in_array($chr,Array(' ',"\t","\n",';',"\n"))) {
           $is_a_var=false;  
        }         
        if($chr=="[") {
          $predicate=true;
        } 
        if($chr=="]") {
          $predicate=false; 
        }
        if(!$predicate) {
          if(in_array($chr,Array('=','>','<','+','-','*',';',"\n"))) {
            $is_a_var=false; 
          } 
        }
        if(!$is_a_var) {
          $vars[]=$a_var;
          $a_var='';
        }
      }
      if($is_a_var && $chr<>"$") {
        $a_var.=$chr;
      }
    }
    if($is_a_var) {
      $vars[]=$a_var; 
    }
    
    
    
    // Now each variable must be evaluated
    foreach($vars as $exp) {
      $exp='$'.$exp;
      
      $ret=$this->_parse_var($exp,1);
      
      // And now strreplace $exp for the value
      $ret='"'.$ret.'"';
      $wexpr=str_replace($exp,$ret,$wexpr);
      
    }
    $php_code='return('.$wexpr.');';
    
    $result=eval($php_code);
    
    return $result;
  }

  // This function parses a flwr-lite RETURN expression
  // basically a return expression just contains the word
  // RETURN followed by another flwr-lite query that can
  // contain flwr-lite expressions.
  function _parse_return($expr) {
    $expr=ltrim($expr);
    $result='';
    
    // A return expr is 
    $retexp=substr($expr,6);
    $sub=$this->evaluate_xqueryl($retexp);
    
    return $sub;
  }

  // This function parses a flwr-lite LET expression
  // a LET statement only binds an evaluation to a variable
  // name
  // Let won't normalize path expressions
  function parse_let($expr) {
    $expr=ltrim($expr);
    // A let is in the form LET $name := value
    $letexpr=substr($expr,4);
    
    $tokens=explode(":=",$letexpr);
    $var_name=$tokens[0];
    $var_value=$tokens[1];
    $var_value=trim($var_value);
    $var_name=trim($var_name);
    if(strstr($var_value,'$')) {
      // We are assigning to a var value
      //$var_value=substr($var_value,1);
      
      $var_value=$this->_parse_var($var_value,false); 
    }  
    $var_name=substr($var_name,1);
    $this->bindings[$var_name]=$var_value;
    
    
  }

  function _split_fors($expr) {
    
    $fors=Array();
    $afor='';
    $level=0;
    for($i=0;$i<strlen($expr);$i++) {
      $chr=substr($expr,$i,1);
      if($chr=="[") {
        $level++; 
      }
      if($chr=="]") {
        $level--; 
      }
      if( ($chr==',') && ($level==0) ) {
        if(strlen($afor)>0) {
          $fors[]=$afor;
          $afor=''; 
        } 
      } else {
        $afor.=$chr; 
      }
      
    } 
    if(strlen($afor)>0) {
      $fors[]=$afor;
      $afor=''; 
    } 
    return $fors;
  }

  // This function parses a flwr-lite expression 
  // this function is called after filtering out XML constructs from
  // a flwr-lite query
  function _parse_query($query) {
    $result='';
    
  
    $exprs=$this->_tokenize($query);
    
    $expr=array_shift($exprs);
    $expr=trim($expr);
    $tokens=explode(" ",$expr);
    $what=trim($tokens[0]);
  
    if(substr($what,0,1)=="$") {
      $result.=$this->_parse_var($what,0); 
    } else {
      switch(strtoupper($what)) {
        case "FOR":
          
          
          // This produces a result-set
          // and then the rest of the expression
          // is evaluated for each element in the
          // node set
          // Expresion should be split in commas
          // but don't count commas inside pairs of []
          $multi_fors=$this->_split_fors($expr);
          
          if(count($multi_fors)>1) {
            // then we have to append for lines next
            for($i=count($multi_fors)-1;$i>0;$i--) {
              $afor=ltrim($multi_fors[$i]);
              if(strtoupper(substr($afor,3))<>'FOR') {
                $afor='FOR '.$afor; 
              }
              $afor=rtrim($afor);
              
              array_unshift($exprs,$afor);
            } 
            $expr=$multi_fors[0];
          }
        
          
          
                 
          $name=$this->_parse_for($expr);
          $nodes=$this->result_sets[$name];
          foreach($nodes as $node) {
            $this->bindings[$name]=$node;
            
            
            $query=implode("\n",$exprs);    // What follows the FOR expr
            
            $result.=$this->_parse_query($query);  // is parsed
          }
          break;
        case "WHERE":
          // If we have a where then the rest is evaluated only if the WHERE is true
          if($this->_parse_where($expr)) {
            // :TODO: cambiar el implode
            $query=implode("\n",$exprs);
            $result.=$this->_parse_query($query);  
          }
          break;      
        case "RETURN":
          // If we have a return we parse the return and nothing can follow a return
          // Theres nothing after a return
          $result.=$this->_parse_return($expr);
          break;
        case "LET":
          // Parse the LET statement and continue evaluating the query
          $this->parse_let($expr);
          // :TODO: cambiar el implode
          $query=implode("\n",$exprs);
          $result.=$this->_parse_query($query);    
          break;
        default:
          // If we have something else (whitespace I hope) we process what follows
          // :TODO: cambiar el implode
          $query=implode("\n",$exprs);
          $result.=$this->_parse_query($query);
      }
    }
    return $result;
  }
}

/*
$case1='
<bib>
 {
  for $b in xmlmem($bib)/bib/book
  where $b/publisher = "Addison-Wesley" and $b/@year > 1991
  return
    <book year="{ $b/@year }">
     { $b/title }
    </book>
 }
</bib> ';

$bib='<bib>
    <book year="1994">
        <title>TCP/IP Illustrated</title>
        <author><last>Stevens</last><first>W.</first></author>
        <publisher>Addison-Wesley</publisher>
        <price> 65.95</price>
    </book>
 
    <book year="1992">
        <title>Advanced XML Programming in the Unix environment</title>
        <author><last>Stevens</last><first>W.</first></author>
        <publisher>Addison-Wesley</publisher>
        <price>65.95</price>
    </book>
 
    <book year="2000">
        <title>Data on the Web</title>
        <author><last>Abiteboul</last><first>Serge</first></author>
        <author><last>Buneman</last><first>Peter</first></author>
        <author><last>Suciu</last><first>Dan</first></author>
        <publisher>Morgan Kaufmann Publishers</publisher>
        <price> 39.95</price>
    </book>
 
    <book year="1999">
        <title>The Economics of Technology and Content for Digital TV</title>
        <editor>
               <last>Gerbarg</last><first>Darcy</first>
                <affiliation>CITI</affiliation>
        </editor>
            <publisher>Kluwer Academic Publishers</publisher>
        <price>129.95</price>
    </book>
 
</bib>';




$xq=new XqueryLite();
$result=$xq->evaluate_xqueryl($case1);
print("Result:<br />");
print("<textarea rows='20' cols='50'>$result</textarea>");
*/
?>
