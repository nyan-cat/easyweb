<?php

require_once(www_root . 'exception.php');

class fs
{
    static function exists($filename)
    {
        return file_exists($filename);
    }

    static function read($filename)
    {
        return fs::exists($filename) ? file_get_contents($filename) : null;
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
        if($append)
        {
            file_put_contents($filename, $content, FILE_APPEND);
        }
        else
        {
            file_put_contents($filename, $content);
        }
    }

    static function delete($filename)
    {
        @unlink($filename);
    }

    static function checked_delete($filename)
    {
        fs::exists($filename) or runtime_error('File not found: ' . $filename);
        self::delete($filename);
    }

    static function modification($filename)
    {
        return filemtime($filename);
    }

    static function crc32($filename)
    {
        fs::exists($filename) or runtime_error('File not found: ' . $filename);
        return hash_file('crc32', $filename);
    }
}

?>