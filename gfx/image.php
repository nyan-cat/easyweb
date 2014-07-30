<?php

namespace gfx;

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
            imagealphablending($image, true);
            break;

        case 'image/jpeg':
            $image = imagecreatefromjpeg($filename);
            imagealphablending($image, true);
            break;

        case 'image/png':
            $image = imagecreatefrompng($filename);
            imagealphablending($image, true);
            break;
        }

        return $image ? new image($image, $width, $height) : null;
    }

    function jpeg($filename, $quality = 80)
    {
        imagejpeg($this->resource, $filename, $quality);
    }

    function crop($x, $y, $width, $height)
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

    function fit_to_width($width)
    {
        $new_width = $width;
        $new_height = (int)(($width * $this->height) / $this->width);
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function fit_to_height($height)
    {
        $new_width = (int)(($height * $this->width) / $this->height);
        $new_height = $height;
        $image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image, $this->resource, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return new image($image, $new_width, $new_height);
    }

    function contain($width, $height)
    {
        if($this->width / $this->height > $width / $height)
        {
            return $this->fit_to_width($width);
        }
        else
        {
            return $this->fit_to_height($height);
        }
    }

    function cover($width, $height)
    {
        if($this->width / $this->height < $width / $height)
        {
            $image = $this->fit_to_width($width);
            return $image->crop(0, (int)(($image->height() - $height) / 2), $width, $height);
        }
        else
        {
            $image = $this->fit_to_height($height);
            return $image->crop((int)(($image->width() - $width) / 2), 0, $width, $height);
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

    function blit($image, $x, $y)
    {
        imagecopy($this->resource, $image->native(), $x, $y, 0, 0, $image->width(), $image->height());
    }

    /*function watermark($width, $height, $string)
    {
        $color = imagecolorallocatealpha($image, 255, 255, 255, 64);
        $font = './calibrib.ttf';
        $fontsize = 24;
        $size = imageftbbox($fontsize, 0, $font, $string);
        $fwidth = $size[2] - $size[0];
        $fhwight = $size[7] - $size[1];
        $horizontal = 16;
        $vertical = 16;
        imagefttext($image, $fontsize, 0, $width - $fwidth - $horizontal, $height - $fheight - $vertical, $color, $font, $string);
    }*/

    function native()
    {
        return $this->resource;
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