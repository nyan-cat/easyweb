<?php

class geoip_procedure extends procedure
{
    function __construct($vars, $name, $params, $empty, $root, $method, $output = array(), $permission = null)
    {
        parent::__construct($vars, $name, $params, $empty, $root, $output, $permission);
        $this->method = $method;
    }

    function query($args, $document)
    {
        $this->validate($args);
        $xml = new xml();
        switch($this->method)
        {
        case 'record':
            isset($args['host']) or runtime_error('GeoIP host parameter not found');
            $root = $xml->element($this->root[0]);
            $xml->append($root);
            if($record = geoip_record_by_name($args['host']))
            {
                $country = $xml->element('country');
                $root->append($country);
                $country->append($xml->element('alpha2', $record['country_code']));
                $country->append($xml->element('alpha3', $record['country_code3']));
                $country->append($xml->element('name', $record['country_name']));
                $root->append($xml->element('region', $record['region']));
                $root->append($xml->element('city', $record['city']));
                $root->append($xml->element('latitude', $record['latitude']));
                $root->append($xml->element('longitude', $record['longitude']));
            }
            break;
        default:
            runtime_error('Unknown GeoIP method: ' . $this->method);
        }
        return $xml;
    }

    private $method;
}

?>