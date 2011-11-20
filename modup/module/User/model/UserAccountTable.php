<?php

class UserAccountTable extends Doctrine_Table
{
    //{{{ public function info($id)
    /**
     * Get info of a user the way it would be returned by the user module
     * @param int $id user id
     */
    public function info($id)
    {
        $user = Doctrine_Query::create()
                ->select('a.name, a.salt, a.email, a.joined, a.permission, g.name, g.permission')
                ->from('UserAccount a')
                ->leftJoin('a.Groups g')
                ->where('a.id = ?')
                ->fetchOne(array($id), Doctrine::HYDRATE_ARRAY);
        $user['group'] = $user['Groups'];
        $user['total_permission'] = $user['permission'];
        foreach ($user['group'] as $group)
        {
            $user['total_permission'] = array_merge($user['total_permission'], $group['permission']);
        }
        $user['total_permission'] = array_unique($user['total_permission']);
        unset($user['Groups']);
        return $user;
    }

    //}}}
    //{{{ public function filterByGroup($id)
    /**
     * Get users based of the group id or name
     * @param mixed $id group id or name
     */
    public function filterByGroup($id)
    {
        $user = Doctrine_Query::create()
                ->select('a.nice_name, a.email, a.name')
                ->from('UserAccount a')
                ->leftJoin('a.Groups g');
        if (is_numeric($id))
        {
            $user->where('g.id = ?');
        }
        else
        {
            $user->where('g.name = ?');
        }
        $users = $user->fetchArray($id);

        return $users;
    }

    //}}}
}

?>
