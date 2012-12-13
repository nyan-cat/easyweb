<?php

class profiler
{
    static function checkpoint($name)
    {
        self::$timeline[] = array(microtime(true), $name);
    }

    static function render()
    {
        self::checkpoint('Finished');

        $first = self::$timeline[0][0];
        $previous = $first;

        echo '<table cellspacing="20">';

        foreach(self::$timeline as $record)
        {
            list($time, $name) = $record;
            $all = $time - $first;
            $last = $time - $previous;

            echo "<tr><td>$name</td><td>$all</td><td>$last</td></tr>";

            $previous = $time;
        }

        echo '</table>';
    }

    static private $timeline = array();
}

?>