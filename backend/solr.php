<?php

class solr
{
    function __construct($server, $port, $url, $username, $password)
    {
        $this->server = $server;
        $this->port = $port;
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    function get($core)
    {
        if(!isset($this->client[$core]))
        {
            $this->client[$core] = new SolrClient
            ([
                'hostname' => $this->server,
                'port'     => $this->port,
                'login'    => $this->username,
                'password' => $this->password,
                'path'     => $this->url . $core
            ]);
        }
        return $this->client[$core];
    }

    private $client = [];
    private $server;
    private $port;
    private $url;
    private $username;
    private $password;
}

?>