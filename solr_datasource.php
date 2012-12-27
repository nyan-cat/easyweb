<?php

class solr_datasource
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
            $this->client[$core] = new SolrClient(array
            (
                'hostname' => $this->server,
                'port'     => $this->port,
                'login'    => $this->username,
                'password' => $this->password,
                'path'     => $this->url . $core
                /*'wt'       => SOLR_PHP_NATIVE_RESPONSE_WRITER,*/
            ));
        }
        return $this->client[$core];
    }

    private $client = array();
    private $server;
    private $port;
    private $url;
    private $username;
    private $password;
}

?>