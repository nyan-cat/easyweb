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

    static function write($filename, $content, $append = false)
    {
        $normalized = fs::normalize($filename);
        if($append)
        {
            file_put_contents($normalized, $content, FILE_APPEND);
        }
        else
        {
            file_put_contents($normalized, $content);
        }
    }

    static function modification($filename)
    {
        return filemtime(fs::normalize($filename));
    }

    static function crc32($filename)
    {
        fs::exists($filename) or runtime_error('File not found: ' . $filename);
        return hash_file('crc32', fs::normalize($filename));
    }
}

?>