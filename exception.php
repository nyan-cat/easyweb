<?php

class runtime_exception extends Exception
{
}

function runtime_error($message)
{
    throw new runtime_exception($message);
}

?>