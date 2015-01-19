<?php

class www_exception extends Exception
{
    function __construct($type, $description)
    {
        $this->type = $type;
        $this->description = $description;
    }

    function __get($name)
    {
        switch($name)
        {
        case 'type':
            return $this->type;

        case 'description':
            return $this->description;

        case 'message':
            return self::$codes[self::$types[$this->type]['code']];

        default:
            return self::$types[$this->type][$name];
        }
    }

    function __isset($name)
    {
        return $name == 'type' or $name == 'description' or isset(self::$types[$this->type]->$name);
    }

    static function extend($types)
    {
        self::$types = array_merge(self::$types, $types);
    }

    private $type;

    private static $codes =
    [
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];

    private static $types =
    [
        'bad_backend_response' => ['code' => 500],
        'bad_credentials'      => ['code' => 403],
        'bad_config'           => ['code' => 500],
        'bad_parameter'        => ['code' => 400],
        'bad_parameter_type'   => ['code' => 500],
        'bad_query_result'     => ['code' => 500],
        'bad_validator_type'   => ['code' => 500],
        'database_error'       => ['code' => 500],
        'empty_query_result'   => ['code' => 400],
        'filesystem_error'     => ['code' => 500],
        'initialization_error' => ['code' => 500],
        'missing_parameter'    => ['code' => 500],
        'not_found'            => ['code' => 404],
        'not_implemented'      => ['code' => 500],
        'object_not_found'     => ['code' => 500],
        'xml_error'            => ['code' => 500]
    ];
}

function error($type, $description)
{
    throw new www_exception($type, $description);
}

?>