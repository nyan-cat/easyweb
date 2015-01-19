<?php

namespace security;

require_once('user.php');

class acl
{
    function __construct($owner = null, $parent = null)
    {
        $this->parent = $parent;
        $this->users = (object) [];
        $this->groups = (object) [];

        if($owner !== null)
        {
            $this->grant("allow user $owner *");
        }
    }

    static function load($object)
    {
        $acl = new acl();
        $acl->users = $object->users;
        $acl->groups = $object->groups;
        return $acl;
    }

    function query(user $user, $operation)
    {
        return !$this->lookup($user, $operation, 'deny') and $this->lookup($user, $operation, 'allow');
    }

    function grant($permission)
    {
        $permission = self::permission($permission);
        $type = $permission->type;
        $subject = $permission->subject;
        $id = $permission->id;

        if(!isset($this->$subject->$id))
        {
            $this->$subject->$id = (object)
            [
                'allow' => [],
                'deny' => []
            ];
        }

        if($permission->operations === '*')
        {
            $this->$subject->$id->$type = $permission->operations;
        }
        else
        {
            foreach($permission->operations as $operation)
            {
                if(!in_array($operation, $this->$subject->$id->$type))
                {
                    array_push($this->$subject->$id->$type, $operation);
                }
            }
        }
    }

    function revoke($permission)
    {
        $permission = self::permission($permission);
        $type = $permission->type;
        $subject = $permission->subject;
        $id = $permission->id;

        if($permission->operations === '*')
        {
            $this->$subject->$id->$type = [];
        }
        else
        {
            foreach($permission->operations as $operation)
            {
                if(($key = array_search($operation, $this->$subject->$id->$type)) !== false)
                {
                    unset($this->$subject->$id->$type[$key]);
                }
            }
        }

        if(empty($this->$subject->$id->allow) and empty($acl->$subject->$id->deny))
        {
            unset($acl->$subject->$id);
        }
    }

    function get()
    {
        return (object) ['users' => $this->users, 'groups' => $this->groups];
    }

    static function parse($query)
    {
        if(preg_match('/\A([^\.]+)\[([^\|]+)\]\.(.+)\Z/', $query, $matches))
        {
            $operation = $matches[3];

            return (object)
            [
                'collection' => $matches[1],
                'id' => $matches[2],
                'operation' => rtrim($operation, '!'),
                'required' => $operation[strlen($operation) - 1] == '!'
            ];
        }
        else
        {
            \error('bad_parameter', 'Bad ACL query string: ' . $query);
        }
    }

    private static function permission($permission)
    {
        preg_match('/(allow|deny) (user|group) (\w+) (\*|[\w, ]+)/', $permission, $matches);

        $operations = [];

        foreach(explode(',', $matches[4]) as $operation)
        {
            $operations[] = trim($operation);
        }

        return (object)
        [
            'type'       => $matches[1],
            'subject'    => $matches[2] . 's',
            'id'         => $matches[3],
            'operations' => ($operations[0] == '*' ? $operations[0] : $operations)
        ];
    }

    private function lookup(user $user, $operation, $type)
    {
        $user_id = $user->id;

        if(isset($this->users->$user_id) and ($this->users->$user_id->$type === '*' or in_array($operation, $this->users->$user_id->$type)))
        {
            return true;
        }
        elseif($groups = self::intersect($this->groups, $user->groups))
        {
            foreach($groups as $group)
            {
                if(isset($group->$type) and ($group->$type === '*' or in_array($operation, $group->$type)))
                {
                    return true;
                }
            }
        }

        if($this->parent !== null)
        {
            return $parent->lookup($user, $operation, $type);
        }
        else
        {
            return false;
        }
    }

    private static function intersect($assoc, $array)
    {
        $result = [];

        foreach($array as $value)
        {
            if(isset($assoc->$value))
            {
                $result[] = $assoc->$value;
            }
        }

        return $result;
    }

    private $parent;
    private $users;
    private $groups;
}

?>