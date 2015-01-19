<?php

namespace acl;

function intersect($assoc, $array)
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

function match($user, $operation, $acl)
{
    return !lookup($user, $operation, $acl, 'deny') and lookup($user, $operation, $acl, 'allow');
}

function lookup($user, $operation, $acl, $type)
{
    $id = (string) $user->id;

    if(isset($acl->users->$id) and ($acl->users->$id->$type === '*' or in_array($operation, $acl->users->$id->$type)))
    {
        return true;
    }
    elseif($groups = intersect($acl->groups, $user->groups))
    {
        foreach($groups as $group)
        {
            if(isset($group->$type) and ($group->$type === '*' or in_array($operation, $group->$type)))
            {
                return true;
            }
        }
    }

    if(isset($acl->parent))
    {
        return lookup($user, $operation, $acl->parent, $type);
    }
    else
    {
        return false;
    }
}

function create($owner_id, $operations = '*')
{
    return (object)
    [
        'users' => (object)
        [
            (string) $owner_id => (object)
            [
                'allow' => $operations,
                'deny' => []
            ]
        ],
        'groups' => (object) []
    ];
}

function apply($acl, $expression, $operations)
{
    preg_match('/(users|groups)\.(\w+)\|(allow|deny)/', $expression, $matches);

    $subject = $matches[1];
    $id = $matches[2];
    $type = $matches[3];

    if(!isset($acl->$subject->$id))
    {
        $acl->$subject->$id = (object)
        [
            'allow' => [],
            'deny' => []
        ];
    }

    if(!is_array($operations))
    {
        $operations = [$operations];
    }

    foreach($operations as $operation)
    {
        if(!in_array($operation, $acl->$subject->$id->$type))
        {
            array_push($acl->$subject->$id->$type, $operation);
        }
    }

    return $acl;
}

function revoke($acl, $expression, $operations)
{
    preg_match('/(users|groups)\.(\w+)\|(allow|deny)/', $expression, $matches);

    $subject = $matches[1];
    $id = $matches[2];
    $type = $matches[3];

    if($operations === '*')
    {
        $acl->$subject->$id->$type = [];
    }
    else
    {
        if(!is_array($operations))
        {
            $operations = [$operations];
        }

        foreach($operations as $operation)
        {
            if(($key = array_search($operation, $acl->$subject->$id->$type)) !== false)
            {
                unset($acl->$subject->$id->$type[$key]);
            }
        }
    }

    if(empty($acl->$subject->$id->allow) and empty($acl->$subject->$id->deny))
    {
        unset($acl->$subject->$id);
    }

    return $acl;
}

function parse($query)
{
    if(preg_match('/\A([^\.]+)\.([^\[]+)\[([^\]]+)\]\|(.+)\Z/', $query, $matches))
    {
        $operation = $matches[4];

        return (object)
        [
            'collection' => $matches[1],
            'id' => $matches[2],
            'acl' => $matches[3],
            'operation' => rtrim($operation, '!'),
            'required' => $operation[strlen($operation) - 1] == '!'
        ];
    }
    elseif(preg_match('/\A([^\.]+)\.([^\|]+)\|(.+)\Z/', $query, $matches))
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

?>