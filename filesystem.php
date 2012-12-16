<?php

require_once('exception.php');

class fs
{
    static function normalize($filename)
    {
        return website_root . $filename;
    }

    static function exists($filename)
    {
        return file_exists(fs::normalize($filename));
    }

    static function read($filename)
    {
        $normalized = fs::normalize($filename);
        return file_exists($normalized) ? file_get_contents($normalized) : null;
    }

    static function checked_read($filename)
    {
        if($content = fs::read($filename))
        {
            return $content;
        }
        else
        {
            runtime_error('File not found: ' . $filename);
        }
    }

    static function write($filename, $content)
    {
        $normalized = fs::normalize($filename);
        file_put_contents($normalized, $content);
    }

    static function crc32($filename)
    {
        return hash_file('crc32', fs::normalize($filename));
    }
}

?>