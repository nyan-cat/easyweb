<?php

class image
{
    static function load($filename)
    {
        $info = getimagesize($filename);
        list($width, $height) = $info;

        if(!$width || !$height)
        {
            return null;
        }

        $image = null;
    
        switch($info['mime'])
        {
        case 'image/gif':
            $image = imagecreatefromgif($filename);
            break;
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filename);
            break;
        case 'image/png':
            $image = imagecreatefrompng($filename);
            break;
        }

        if($image)
        {
            return new image($image, $width, $height);
        }
        else
        {
            return null;
        }
    }

    function save($filename, $quality = 80)
    {
        imagejpeg($this->resource, $filename, $quality);
    }

    function fit_to_width_copy($width)
    {
        $new_width = $width;
        $new_height = ($width * $this->height) / $this->width;
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function fit_to_height_copy($height)
    {
        $new_width = ($height * $this->width) / $this->height;
        $new_height = $height;
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function width()
    {
        return $this->width;
    }

    function height()
    {
        return $this->height;
    }

    private function __construct($resource, $width, $height)
    {
        $this->resource = $resource;
        $this->width = $width;
        $this->height = $height;
    }

    private $resource = null;
    private $width = 0;
    private $height = 0;
}

?>