<?php

namespace fs;

class enumerator implements \Iterator
{
    function __construct($path)
    {
        $this->path = $path;
        $this->handle = opendir($path);
        self::read();
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
        self::read();
    }

    function rewind()
    {
        $this->key = 0;
        rewinddir($this->handle);
        self::read();
    }

    function valid()
    {
        return !is_null($this->value);
    }

    private function read()
    {
        if(($filename = readdir($this->handle)) !== false)
        {
            if($filename === '.' or $filename === '..')
            {
                self::read();
            }
            else
            {
                $fullpath = $this->path . '/' . $filename;

                $this->value = (object)
                [
                    'name'      => $filename,
                    'size'      => filesize($fullpath),
                    'directory' => is_dir($fullpath),
                    'file'      => is_file($fullpath),
                    'link'      => is_link($fullpath)
                ];
            }
        }
        else
        {
            $this->value = null;
        }
    }

    private $path;
    private $handle;
    private $key = 0;
    private $value;
}

?>