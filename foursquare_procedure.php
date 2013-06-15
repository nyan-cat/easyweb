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
                            $root->append($image);
                            $image['@url'] = $item->url;

                            foreach($item->sizes->items as $size)
                            {
                                $resampled = $xml->element('resampled');
                                $image->append($resampled);
                                $resampled['@url'] = $size->url;
                                $resampled['@width'] = $size->width;
                                $resampled['@height'] = $size->height;
                                $resampled['@created'] = @date("Y-m-d H:i:s", $item->createdAt);
                                $resampled['@user-id'] = $item->user->id;
                                $resampled['@user-first-name'] = $item->user->firstName;
                                if(isset($item->user->lastName))
                                {
                                    $resampled['@user-last-name'] = $item->user->lastName;
                                }
                                $resampled['@user-gender'] = $item->user->gender;
                                $resampled['@user-photo'] = $item->user->photo;
                            }
                        }
                    }
                }
            }
            break;

        case 'venues':
            isset($args['latitude']) or runtime_error('Foursquare latitude parameter not found');
            isset($args['longitude']) or runtime_error('Foursquare longitude parameter not found');
            $root = $xml->element($this->root[0]);
            $xml->append($root);

            if($result = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/search?ll=' . $args['latitude'] . ',' . $args['longitude'] . '&' . $this->datasource->get())))
            {
                foreach($result->response->groups as $group)
                {
                    if($group->type == 'nearby')
                    {
                        foreach($group->items as $item)
                        {
                            $venue = $xml->element('venue');
                            $root->append($venue);
                            $venue['@id'] = $item->id;
                            $venue['@name'] = $item->name;
                            $venue['@url'] = $item->canonicalUrl;

                            foreach($item->categories as $cat)
                            {
                                $category = $xml->element('category');
                                $venue->append($category);
                                $category['@id'] = $cat->id;
                                $category['@name'] = $cat->name;
                                $category['@plural-name'] = $cat->pluralName;
                                $category['@short-name'] = $cat->shortName;
                                $category['@icon'] = $cat->icon;
                            }
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