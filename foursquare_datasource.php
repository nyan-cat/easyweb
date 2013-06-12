<?php

class foursquare_datasource
{
    function __construct($id, $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    function get()
    {
        return 'client_id=' . $this->id . '&client_secret=' . $this->secret;
    }

    private $id;
    private $secret;
}

?>