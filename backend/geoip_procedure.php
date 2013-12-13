<?php

class geoip_procedure extends procedure
{
    function __construct($name, $method, $required)
    {
        switch($method)
        {
        case 'record':
            $params = ['host' => 'string'];
            break;

        default:
            backend_error('bad_config', "Unknown GeoIP method: $method");
        }

        parent::__construct($params, self::make_id($name, $params), $required);

        $this->method = $method;
    }

    function query_direct($args)
    {
        switch($this->method)
        {
        case 'record':
            if($record = geoip_record_by_name($args['host']))
            {
                return (object)
                [
                    'country' =>
                    [
                        'alpha2' => $record['country_code'],
                        'alpha3' => $record['country_code3'],
                        'name' => $record['country_name']
                    ],
                    'region' => $record['region'],
                    'city' => $record['city'],
                    'latitude' => $record['latitude'],
                    'longitude' => $record['longitude']
                ];
            }
            else
            {
                !$this->required or backend_error('bad_input', 'Empty response from GeoIP procedure');
            }
            break;
        }
        return null;
    }

    function evaluate_direct($args)
    {
        return $this->query_direct($args);
    }

    private $method;
}

?>