<?php

class foursquare_procedure extends procedure
{
    function __construct($datasource, $vars, $name, $method, $params, $empty, $root, $output = array(), $permission = null, $cache = true)
    {
        parent::__construct($vars, $name, $params, $empty, $root, $output, $permission, $cache);
        $this->datasource = $datasource;
        $this->method = $method;
    }

    function query($args, $document)
    {
        $this->validate($args);
        $xml = new xml();
        switch($this->method)
        {
        case 'photos':
            isset($args['venue_id']) or runtime_error('Foursquare venue_id parameter not found');
            $root = $xml->element($this->root[0]);
            $xml->append($root);

            if($result = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/' . $args['venue_id'] . '/photos?' . $this->datasource->get())))
            {
                foreach($result->response->photos->groups as $group)
                {
                    if($group->type == 'venue')
                    {
                        foreach($group->items as $item)
                        {
                            $image = $xml->element('image');
                            $image->append($xml->element('url', $item->url));
                            $root->append($image);
                        }
                    }
                }
            }
            break;
        default:
            runtime_error('Unknown Foursquare method: ' . $this->method);
        }
        return $xml;
    }

    private $datasource;
    private $method;
}

?>