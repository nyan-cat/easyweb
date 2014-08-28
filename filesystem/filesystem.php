<?php

namespace fs;

require_once('enumerator.php');

function read($filename)
{
}

function write($filename, $append = false)
{
}

function size($filename)
{
    return filesize($filename);
}

function extension($filename)
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

function each($path, $callback = null)
{
    if($callback)
    {
        $continue = true;
        $handle = opendir($path);
        while($continue and ($filename = readdir($this->handle)) !== false)
        {
            if($filename !== '.' and $filename !== '..')
            {
                $fullpath = $path . '/' . $filename;
                $continue = $callback((object)
                [
                    'name'      => $filename,
                    'size'      => filesize($fullpath),
                    'directory' => is_dir($fullpath),
                    'file'      => is_file($fullpath),
                    'link'      => is_link($fullpath)
                ]) !== false;
            }
        }
        closedir($handle);
    }
    else
    {
        return new enumerator($path);
    }
}

?>