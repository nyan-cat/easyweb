<?php

require_once(www_root . 'exception.php');

class fs_iterator implements Iterator
{
    function __construct($handle)
    {
        $this->handle = $handle;
    }

    function __destruct()
    {
        closedir($this->handle);
    }

    function current()
    {
        return $this->value;
    }

    function key()
    {
        return $this->key;
    }

    function next()
    {
        ++$this->key;
        $this->value = readdir($this->handle);
        if($this->value === "." or $this->value === "..")
        {
            self::next();
        }
    }

    function rewind()
    {
        $this->key = 0;
        rewinddir($this->handle);
        self::next();
    }

    function valid()
    {
        return $this->value !== false;
    }

    private $key = 0;
    private $value = false;
    private $handle = null;
}

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

    static function each($path)
    {
        return new fs_iterator(opendir($path));
    }
}

?>