<?php

namespace fs;

require_once('enumerator.php');

function read($filename)
{
    return file_get_contents($filename);
}

function write($filename, $data, $append = false)
{
    file_put_contents($filename, $data, $append ? FILE_APPEND : 0);
}

function delete($filename)
{
    @unlink($filename);
}

function size($filename)
{
    return filesize($filename);
}

function path($filename)
{
    return pathinfo($filename, PATHINFO_DIRNAME);
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