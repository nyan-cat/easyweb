<?php

class files
{
    static function parse($uploaded)
    {
        $result = [];

        foreach($uploaded as $name => $input)
        {
            if(is_array($input['error']))
            {
                $files = [];

                foreach($input['error'] as $n => $error)
                {
                    if($error == UPLOAD_ERR_OK)
                    {
                        $files[] = (object)
                        [
                            'name' => $input['name'][$n],
                            'type' => $input['type'][$n],
                            'size' => $input['size'][$n],
                            'tmp'  => $input['tmp_name'][$n]
                        ];
                    }
                }

                $result[$name] = $files;
            }
            else
            {
                if($input['error'] == UPLOAD_ERR_OK)
                {
                    $result[$name] = (object)
                    [
                        'name' => $input['name'],
                        'type' => $input['type'],
                        'size' => $input['size'],
                        'tmp'  => $input['tmp_name']
                    ];
                }
            }
        }

        return $result;
    }
}

?>