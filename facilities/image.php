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
            $image = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);

            $png = imagecreatefrompng($filename);
            imagealphablending($png, true);
            imagesavealpha($png, true);

            imagecopy($image, $png, 0, 0, 0, 0, $width, $height);
            imagedestroy($png);
            break;
        }

        return $image ? new image($image, $width, $height) : null;
    }

    function jpeg($filename, $quality = 80)
    {
        imagejpeg($this->resource, $filename, $quality);
    }

    function crop_copy($x, $y, $width, $height)
    {
        $image = imagecreatetruecolor($width, $height);
        if(imagecopy($image, $this->resource, 0, 0, $x, $y, $width, $height))
        {
            return new image($image, $width, $height);
        }
        else
        {
            return null;
        }
    }

    function fit_to_width_copy($width)
    {
        $new_width = $width;
        $new_height = (int)(($width * $this->height) / $this->width);
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function fit_to_height_copy($height)
    {
        $new_width = (int)(($height * $this->width) / $this->height);
        $new_height = $height;
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function contain_copy($width, $height)
    {
        if($this->width / $this->height > $width / $height)
        {
            return $this->fit_to_width_copy($width);
        }
        else
        {
            return $this->fit_to_height_copy($height);
        }
    }

    function cover_copy($width, $height)
    {
        if($this->width / $this->height < $width / $height)
        {
            $image = $this->fit_to_width_copy($width);
            return $image->crop_copy(0, (int)(($image->height() - $height) / 2), $width, $height);
        }
        else
        {
            $image = $this->fit_to_height_copy($height);
            return $image->crop_copy((int)(($image->width() - $width) / 2), 0, $width, $height);
        }
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