<?php

class form
{
    static function checkbox($value)
    {
        return strtolower($value) == 'on';
    }

    static function adjust_uint($value)
    {
        $value = preg_replace('/[^\d]/', '', $value);
        return is_numeric($value) ? $value : 0;
    }
}

?>