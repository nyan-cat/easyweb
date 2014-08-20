<?php

namespace dbg;

class log
{
    function __construct($filename)
    {
        $this->filename = self::$folder . $filename;
    }

    static function folder($folder)
    {
        self::$folder = $folder;
    }

    function debug($message)
    {
        $this->write('debug', $message);
    }

    function info($message)
    {
        $this->write('info', $message);
    }

    function notice($message)
    {
        $this->write('notice', $message);
    }

    function warning($message)
    {
        $this->write('warning', $message);
    }

    function error($message)
    {
        $this->write('error', $message);
    }

    function critical($message)
    {
        $this->write('critical', $message);
    }

    function fatal($message)
    {
        $this->write('fatal', $message);
    }

    function write($level, $message)
    {
        $timestamp = microtime(true);
        $integer = floor($timestamp);
        $datetime = @date("Y-m-d H:i:s", $integer) . substr((string)($timestamp - $integer), 1, 4);
        file_put_contents($this->filename, "[$datetime] - [$level] - $message\r\n", FILE_APPEND);
    }

    function separate()
    {
        file_put_contents($this->filename, "\r\n- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r\n\r\n", FILE_APPEND);
    }

    private static $folder = '/var/log/';

    private $filename;
}

?>