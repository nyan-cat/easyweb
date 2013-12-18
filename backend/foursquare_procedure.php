<?php

class foursquare_procedure extends procedure
{
    function __construct($name, $method, $required, $result, $foursquare)
    {
        in_array($method, ['photos', 'venues']) or backend_error('bad_config', 'Unknown Foursquare procedure method: ' . $method);
        $result == 'array' or backend_error('bad_config', 'Foursquare procedure result must be array');

        parent::__construct([], self::make_id($name, []), $required, $result);

        $this->method = $method;
        $this->foursquare = $foursquare;
    }

    function query_direct($args)
    {
        switch($this->method)
        {
        case 'photos':
            isset($args['_venue_id']) or backend_error('bad_query', 'Foursquare _venue_id argument missing');

            $photos = [];

            if($result = json::decode(file_get_contents('https://api.foursquare.com/v2/venues/' . $args['venue_id'] . '/photos?' . $this->foursquare->get())))
            {
                foreach($result->response->photos->groups as $group)
                {
                    if($group->type == 'venue')
                    {
                        foreach($group->items as $item)
                        {
                            $photo =
                            [
                                'url' => $item->url,
                                'created' => $item->createdAt,
                                'user' =>
                                [
                                    'id' => $item->user->id,
                                    'firstName' => $item->user->firstName,
                                    'lastName' => @$item->user->lastName,
                                    'gender' => $item->user->gender,
                                    'photo' => $item->user->photo
                                ]
                            ];

                            $resampled = [];

                            foreach($item->sizes->items as $size)
                            {
                                $resampled[] =
                                [
                                    'url' => $size->url,
                                    'width' => $size->width,
                                    'height' => $size->height
                                ];
                            }

                            $photo['resampled'] = $resampled;

                            $photos[] = $photo;
                        }
                    }
                }
            }

            !(empty($photos) and $this->required) or backend_error('bad_input', 'Empty response from Froursquare procedure');

            return (object) $photos;

        case 'venues':
            isset($args['_latitude']) or backend_error('bad_query', 'Foursquare _latitude argument missing');
            isset($args['_longitude']) or backend_error('bad_query', 'Foursquare _longitude argument missing');

            $venues = [];

            if($result = json::decode(file_get_contents('https://api.foursquare.com/v2/venues/search?ll=' . $args['_latitude'] . ',' . $args['_longitude'] . '&' . $this->foursquare->get())))
            {
                foreach($result->response->groups as $group)
                {
                    if($group->type == 'nearby')
                    {
                        foreach($group->items as $item)
                        {
                            $venue[] =
                            [
                                'id' => $item->id,
                                'name' => $item->name,
                                'url' => $item->canonicalUrl
                            ];

                            $categories = [];

                            foreach($item->categories as $category)
                            {
                                $categories[] =
                                [
                                    'id' => $category->id,
                                    'name' => $category->name,
                                    'pluralName' => $category->pluralName,
                                    'shortName' => $category->shortName,
                                    'icon' => $category->icon
                                ];
                            }

                            $venue['categories'] = $categories;

                            $venues[] = $venue;
                        }
                    }
                }
            }

            !(empty($venues) and $this->required) or backend_error('bad_input', 'Empty response from Froursquare procedure');
            
            return (object) $venues;
        }
    }

    private $method;
    private $foursquare;
}

?>