<?php

class censor
{
    static function load($filename)
    {
        return new censor(xml::load($filename));
    }

    function match($mixed)
    {
        if(is_array($mixed))
        {
            foreach($mixed as $text)
            {
                if($result = $this->match_text($text))
                {
                    return $result;
                }
            }
            return null;
        }
        else
        {
            return $this->match_text($mixed);
        }
    }
    
    private function match_text($text)
    {
        $text = mb_strtolower($text, 'UTF-8');

        foreach($this->rules->query('//rule') as $rule)
        {
            $matches = $this->rules->query('match', $rule);

            $threshold = isset($rule['@match']) ? $rule['@match'] : 1;
            $threshold = $threshold == '*' ? count($matches) : $threshold;
            $triggered = 0;

            foreach($matches as $match)
            {
                $type = isset($match['@type']) ? $match['@type'] : 'substring';
                $count = isset($match['@count']) ? $match['@count'] : 1;
                $value = mb_strtolower($match->value(), 'UTF-8');

                if($type == 'substring')
                {
                    if(substr_count($text, $value) >= $count)
                    {
                        ++$triggered;
                    }
                }
                else if($type == 'regex')
                {
                    if(preg_match_all("/$value/", $text) >= $count)
                    {
                        ++$triggered;
                    }
                }

                if($threshold <= $triggered)
                {
                    return
                    [
                        'action' => $rule['@action']
                    ];
                }
            }
        }

        return null;
    }

    private function __construct($rules)
    {
        $this->rules = $rules;
    }

    private $rules = null;
}

?>