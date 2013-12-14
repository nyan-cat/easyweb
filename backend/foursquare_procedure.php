<?php

class foursquare_procedure extends procedure
{
    function __construct($name, $method, $required, $result, $foursquare)
    {
        $result == 'array' or backend_error('bad_config', 'Foursquare procedure result must be array');

        switch($method)
        {
        case 'photos':
            $params = ['venue_id' => 'string'];
            break;

        case 'venues':
            $params = ['latitude' => 'real', 'longitude' => 'real'];
            break;

        default:
            backend_error('bad_config', "Unknown Foursquare method: $method");
        }

        parent::__construct($params, self::make_id($name, $params), $required, $result);

        $this->method = $method;
        $this->foursquare = $foursquare;
    }

    function query_direct($args)
    {
        switch($this->method)
        {
        case 'photos':

            $photos = [];

            if($result = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/' . $args['venue_id'] . '/photos?' . $this->foursquare->get())))
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

            $venues = [];

            if($result = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/search?ll=' . $args['latitude'] . ',' . $args['longitude'] . '&' . $this->foursquare->get())))
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