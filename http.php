<?php

class http
{
    static function get($url, $params = null, $username = null, $password = null)
    {
        !is_array($params) or empty($params) or $url .= '?' . http_build_query($params);

        if($username and $password)
        {
            $ctx = stream_context_create(['http' => ['header' => 'Authorization: Basic ' . base64_encode("$username:$password")]]);

            return file_get_contents($url, false, $ctx);
        }
        else
        {
            return file_get_contents($url, false);
        }
    }

    static function post($url, $post = null, $get = null, $username = null, $password = null)
    {
        $http =
        [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded'
                       . ($username and $password ? ("\r\nAuthorization: Basic " . base64_encode("$username:$password")) : '')
        ];

        !is_array($post) or empty($post) or $http['content'] = http_build_query($post);
        !is_array($get) or empty($get) or $url .= '?' . http_build_query($get);

        $ctx = stream_context_create(['http' => $http]);

        return file_get_contents($url, false, $ctx);
    }
}

?>