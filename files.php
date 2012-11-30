<?php

class files
{
    static function get($pattern = '.*')
    {
        $pattern = '\A' . $pattern . '\Z';
        $list = array();

        foreach ($_FILES as $input)
        {
            if(is_array($input['error']))
            {
                foreach($input['error'] as $n => $error)
                {
                    if($error == UPLOAD_ERR_OK && preg_match($pattern, $input['tmp_name'][$n]) == 1)
                    {
                        $list[] = array
                        (
                            'name' => $input['tmp_name'][$n],
                            'size' => $input['size']
                        );
                    }
                }
            }
            else
            {
                if($input['error'] == UPLOAD_ERR_OK && preg_match($pattern, $input['tmp_name']) == 1)
                {
                    $list[] = array
                    (
                        'name' => $input['tmp_name'],
                        'size' => $input['size']
                    );
                }
            }
        }

        return $list;
    }
}

?>