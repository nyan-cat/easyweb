<?php

require_once('procedure.php');

class geoip_procedure extends procedure
{
    function __construct($name, $method, $required, $result)
    {
        in_array($method, ['record']) or backend_error('bad_config', 'Unknown GeoIP procedure method: ' . $method);
        $result == 'object' or backend_error('bad_config', 'GeoIP procedure result must be object');

        parent::__construct([], self::make_id($name, []), $required, $result);

        $this->method = $method;
    }

    function query_direct($args)
    {
        switch($this->method)
        {
        case 'record':
            isset($args['_host']) or backend_error('bad_query', 'GeoIP _host argument missing');

            if($record = geoip_record_by_name($args['_host']))
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

    private $method;
}

?>