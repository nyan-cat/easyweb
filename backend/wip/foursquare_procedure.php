<?php

require_once(www_root . 'backend/wip/procedure.php');

class foursquare_procedure extends procedure
{
    function __construct($name, $method, $required, $result, $foursquare)
    {
        in_array($method, ['photos', 'venues']) or error('initialization_error', 'Unknown Foursquare procedure method: ' . $method);
        $result == 'array' or error('initialization_error', 'Foursquare procedure result must be array');

        parent::__construct($name, [], $required, $result);

        $this->method = $method;
        $this->foursquare = $foursquare;
    }

    function query_direct($args)
    {
        switch($this->method)
        {
        case 'photos':
            isset($args['_venue_id']) or error('missing_parameter', 'Foursquare _venue_id argument missing');

            $photos = [];

            if($result = json\decode(file_get_contents('https://api.foursquare.com/v2/venues/' . $args['_venue_id'] . '/photos?' . $this->foursquare->get())))
            {
                foreach($result->response->photos->groups as $group)
                {
                    if($group->type == 'venue')
                    {
                        foreach($group->items as $item)
                        {
                            $photo = (object)
                            [
                                'url'     => $item->url,
                                'created' => $item->createdAt,
                                'user'    => (object)
                                [
                                    'id'         => $item->user->id,
                                    'first_name' => $item->user->firstName,
                                    'gender'     => $item->user->gender,
                                    'photo'      => $item->user->photo
                                ]
                            ];

                            if($item->user->lastName)
                            {
                                $photo->user->last_name = $item->user->lastName;
                            }

                            $resampled = [];

                            foreach($item->sizes->items as $size)
                            {
                                $resampled[] = (object)
                                [
                                    'url'    => $size->url,
                                    'width'  => $size->width,
                                    'height' => $size->height
                                ];
                            }

                            $photo->resampled = $resampled;

                            $photos[] = $photo;
                        }
                    }
                }
            }

            !(empty($photos) and $this->required) or error('empty_query_result', 'Empty response from Froursquare procedure');

            return (object) $photos;

        case 'venues':
            isset($args['_latitude']) or error('missing_parameter', 'Foursquare _latitude argument missing');
            isset($args['_longitude']) or error('missing_parameter', 'Foursquare _longitude argument missing');

            $venues = [];

            if($result = json\decode(file_get_contents('https://api.foursquare.com/v2/venues/search?ll=' . $args['_latitude'] . ',' . $args['_longitude'] . '&' . $this->foursquare->get())))
            {
                foreach($result->response->groups as $group)
                {
                    if($group->type == 'nearby')
                    {
                        foreach($group->items as $item)
                        {
                            $venue[] = (object)
                            [
                                'id'   => $item->id,
                                'name' => $item->name,
                                'url'  => $item->canonicalUrl
                            ];

                            $categories = [];

                            foreach($item->categories as $category)
                            {
                                $categories[] = (object)
                                [
                                    'id'          => $category->id,
                                    'name'        => $category->name,
                                    'plural_name' => $category->pluralName,
                                    'short_name'  => $category->shortName,
                                    'icon'        => $category->icon
                                ];
                            }

                            $venue->categories = $categories;

                            $venues[] = $venue;
                        }
                    }
                }
            }

            !(empty($venues) and $this->required) or error('empty_query_result', 'Empty response from Froursquare procedure');
            
            return (object) $venues;
        }
    }

    private $method;
    private $foursquare;
}

?>