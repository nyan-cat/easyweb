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
        'users' =>
        [
            (string) $owner_id => (object)
            [
                'allow' => $operations,
                'deny' => []
            ]
        ],
        'groups' => []
    ];
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
        $operation = $matches[4];

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