<?php

class Profile 
{
    //{{{ properties
    private $return_layout = FALSE;

    //}}}
    //{{{ constants 
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Profile Module';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = 'User';

    const LISTED = 0;
    const UNLISTED = 1;

    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
    }

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
    }

    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
    }
    
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (User::perm('edit profile fields'))
        {
            $links['Tools'] = array(
                '<a href="/admin/module/Profile/manage/">Manage Profile Fields</a>',
            );
        }

        return $links;
    }

    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        
        if (strpos(URI_PATH, '/admin/module/Profile/') !== FALSE)
        {
            $css = array(
                'screen' => array(
                    '/admin/static/Profile/profile.css/'
                )
            );
        }

        return $css;
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'edit profile fields' => 'Manage Profile Fields',
        );
    }

    //}}}
    //{{{ public static function get_profile_form($group_name = 'profile', $user = NULL, $post = NULL, $return_layout = FALSE)
    public static function get_profile_form($group_name = 'profile', $user = NULL, $post = NULL, $return_layout = FALSE)
    {
        $groups = Doctrine_Query::create()
            ->from('ProfileGroup g')
            ->leftJoin('g.Fields f')
            ->orderBy('g.weight ASC, f.weight ASC, g.name ASC, f.name ASC')
            ->fetchArray();

        $layout = new Field();
        $form = new FormBuilderRows();
        foreach ($groups as $group)
        {
            $rows = array();
            foreach ($group['Fields'] as $field)
            {
                $data = array();
                if (!is_null($user))
                {
                    if (!is_numeric($user))
                    {
                        $uat = Doctrine::getTable('UserAccount');
                        $ua = $uat->findOneByName($user);
                        if ($ua !== FALSE)
                        {
                            $user = $ua->id;
                            $ua->free();
                        }
                    }
                    if (is_numeric($user))
                    {
                        $record = Doctrine_Query::create()
                            ->from('ProfileData d')
                            ->addWhere('d.user_id = ?', $user)
                            ->addWhere('d.profile_field_id = ?', $field['id'])
                            ->fetchOne();
                        if ($record !== FALSE)
                        {
                            $data = $record->toArray();
                            $record->free();
                        }
                    }
                }
                        
                $layout->add_layout(
                    array(
                        'field' => Field::layout(
                            $field['type'],
                            array(
                                'data' => $field['meta']
                            )
                        ),
                        'name' => $field['name'],
                        'type' => $field['type'],
                        'value' => $data
                    )
                );
                if (!is_null($post))
                {
                    $layout->merge($post);
                }
                if ($return_layout)
                {
                    continue;
                }
                $rows[] = array(
                    'label' => array(
                        'text' => $field['name'],
                    ),
                    'fields' => $layout->get_layout($field['name']),
                );
            }
            if ($return_layout)
            {
                return $layout;
            }
            $form->add_group(
                array(
                    'label' => array(
                        'text' => $group['name']
                    ),
                    'rows' => $rows
                ),
                $group_name
            );
                            
        }
        return $form;
    }

    //}}}
    //{{{ public static function save_profile_form($user, $post)
    public static function save_profile_form($user, $post)
    {
        if (!is_numeric($user))
        {
            $uat = Doctrine::getTable('UserAccount');
            $ua = $uat->findOneByName($user);
            if ($ua !== FALSE)
            {
                $user = $ua->id;
                $ua->free();
            }
        }
        $layout = self::get_profile_form('profile', NULL, NULL, TRUE);
        $ppost = $layout->acts('post', $post);
        $fields = Doctrine_Query::create()
            ->from('ProfileField')
            ->whereIn('name', array_keys($ppost))
            ->fetchArray();
        $data = Doctrine_Query::create()
            ->from('ProfileData')
            ->where('user_id', $user)
            ->execute();
        $keys = array();
        foreach ($fields as $field)
        {
            if (ake($field['name'], $ppost))
            {
                $updated = FALSE;
                foreach ($data as $v)
                {
                    if ($v['profile_field_id'] === $field['id'])
                    {
                        $v['data'] = is_array($ppost[$field['name']])
                            ? serialize($ppost[$field['name']])
                            : $ppost[$field['name']];
                        if ($v->isValid())
                        {
                            $v->save();
                            $keys[] = $field['name'];
                        }
                        $updated = TRUE;
                        break;
                    }
                }
                if (!$updated)
                {
                    $data_record = new ProfileData();
                    $data_record->user_id = $user;
                    $data_record->profile_field_id = $field['id'];
                    $data_record->data = is_array($ppost[$field['name']])
                        ? serialize($ppost[$field['name']])
                        : $ppost[$field['name']];
                    if ($data_record->isValid())
                    {
                        $data_record->save();
                        $keys[] = $field['name'];
                    }
                    $data_record->free();
                }
            }
        }
        return array_diff(array_keys($ppost), $keys);
    }

    //}}}
    //{{{ public static function get_profile_data($user, $fields = array(), $post = FALSE)
    public static function get_profile_data($user, $fields = array(), $post = FALSE)
    {
        if (!is_numeric($user))
        {
            $uat = Doctrine::getTable('UserAccount');
            $ua = $uat->findOneByName($user);
            if ($ua !== FALSE)
            {
                $user = $ua->id;
                $ua->free();
            }
        }
        $data = Doctrine_Query::create()
            ->from('ProfileData d')
            ->leftJoin('d.Field f')
            ->where('d.user_id = ?', $user)
            ->whereIn('f.name', $fields)
            ->fetchArray();

        $results = array();
        foreach ($data as $v)
        {
            $results[$v['Field']['name']] = $post
                ? array('data' => $v['data'])
                : $v['data'];
        }

        return $results;
    }

    //}}}
}

?>
