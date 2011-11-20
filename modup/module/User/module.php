<?php

//{{{ class User
class User
{
    //{{{ properties
    /**
     * Current user info
     */
    static $user;
    /**
     * Flag to note if user info has changed
     */
    static $changed;
    /**
     * Array of all permissions from all modules
     */
    static $perm;
    /**
     * Array of all groups in the system
     * This is not loaded at module load time, but loaded when it is called for
     * the first time.
     */
    static $groups = array();
    /**
     * Array of methods of UserInfo class to reference in __call()
     */
    static $methods = array();

    //}}}
    //{{{ constants
    const USER_ADMIN = 'Admin';
    const USER_ANONYMOUS = 'Anonymous';

    const ID_ADMIN = 'admin';
    const ID_ANONYMOUS = 'anonymous';

    const GROUP_ADMIN = 'admin';
    const GROUP_ANONYMOUS = 'anonymous';

    const MODULE_AUTHOR = 'Glenn';
    const MODULE_DESCRIPTION = 'User, group, and permission management';
    const MODULE_WEBSITE = 'http://www.glennyonemitsu.com/';

    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
        self::$changed = FALSE;
    }

    //}}}
    //{{{ public function hook_active()
    public function hook_active()
    {
        $uac = MonDB::selectCollection('user_account');
        $uag = MonDB::selectCollection('user_group');
        $uac->ensureIndex(array('name' => 1), array(
            'unique' => 1, 
            'safe' => 1, 
            'dropDups' => 1,
        ));
        $uag->ensureIndex(array('name' => 1), array(
            'unique' => 1, 
            'safe' => 1, 
            'dropDups' => 1,
        ));
        self::find_info();
        Module::h('user_perm');
    }

    //}}}
    //{{{ public function hook_admin_dashboard()
    public function hook_admin_dashboard()
    {
        if (!User::perm('admin'))
        {
            return array();
        }
        return array(
            array(
                'title' => 'User Account Overview',
                'content' => $this->_admin_dashboard_overview()
            )
        );
    }

    //}}}
    //{{{ private function _admin_dashboard_overview()
    private function _admin_dashboard_overview()
    {
        $users = MonDB::selectCollection('user_account')->count();
        $groups = MonDB::selectCollection('user_group')->count();
        $o = '
            <ul>
                <li>Total User Accounts: ' . $users . '</li>
                <li>Total User Groups: ' . $groups . '</li>
            </ul>';
        return $o;
    }

    //}}}
    //{{{ public function hook_end()
    /**
     * Checks for user account updates and saves to DB if it is
     */
    public function hook_end()
    {
        if (self::$changed)
        {
            $uac = MonDB::selectCollection('user_account');
            $user = $uac->findOne(array('_id' => $info['_id']));
            $info = self::$info;
            $info['group'] = array();
            foreach (self::$info['group'] as $group)
            {
                $info['group'][] = $group;
                $info['group_ids'][] = $group['_id'];
            }
            $user = array_merge($user, $info);
            $uac->save($user);
        }
    }

    //}}}
    //{{{ public function hook_install_form_build()
    public function hook_install_form_build()
    {
        $fields['pw'] = array(
            'field' => Field::layout('password_confirm'),
            'name' => 'password',
            'type' => 'password_confirm'
        );
        $fields['email'] = array(
            'field' => Field::layout('text'),
            'name' => 'email',
            'type' => 'text'
        );
        return array(
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'password for admin account'
                    ),
                    'fields' => $fields['pw']
                ),
                array(
                    'label' => array(
                        'text' => 'e-mail for admin account'
                    ),
                    'fields' => $fields['email']
                )
            )
        );
    }

    //}}}
    //{{{ public function hook_install_form_process($data, $extra)
    /**
     * Sets up the first admin account
     */
    public function hook_install_form_process($data, $extra)
    {
        $hash = deka(FALSE, $extra, 'hashed')
            ? $data['password'] 
            : sha1($data['password']);
        $uad = new UserAccount();
        $uad->id = User::ID_ADMIN;
        $uad->name = User::USER_ADMIN;
        $uad->pass = $hash;
        $uad->email = $data['email'];
        $uad->permission = array('admin');
        $uad->save();

        $gad = new UserGroup();
        $gad->name = User::GROUP_ADMIN;
        $gad->permission = array('admin');
        $gad->save();

        $guad = new UserGrouping();
        $guad->user_id = $uad->id;
        $guad->group_id = $gad->id;
        $guad->save();

        $gm = new UserGroup();
        $gm->name = 'member';
        $gm->permission = array('edit self');
        $gm->save();

        $uan = new UserAccount();
        $uan->id = User::ID_ANONYMOUS;
        $uan->name = User::USER_ANONYMOUS;
        $uan->pass = sha1(random_string(40));
        $uan->email = '';
        $uan->permission = array();
        $uan->save();

        $gan = new UserGroup();
        $gan->name = User::GROUP_ANONYMOUS;
        $gan->permission = array();
        $gan->save();

        $guan = new UserGrouping();
        $guan->user_id = $uan->id;
        $guan->group_id = $gan->id;
        $guan->save();

    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'User' => array(
                'admin' => 'Full admin access',
                'edit self' => 'Edit their own account',
                'create users' => 'Create user accounts',
                'view users' => 'View user accounts',
                'edit users' => 'Edit user accounts',
            ),
            'Group' => array(
                'create groups' => 'Create groups',
                'view groups' => 'View groups',
                'edit groups' => 'Edit groups',
                'edit permissions' => 'Change user and group permissions'
            )
        );
    }

    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        if (URI_PATH === '/admin/module/User/users/')
        {
            $css['screen'] = array('/admin/static/User/user.css/');
        }
        return $css;
    }

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/User/edit_user/') === 0)
        {
            $js[] = '/admin/static/User/account.js/';
        }
        if (URI_PATH === '/admin/login/')
        {
            $js[] = '/admin/static/User/login.js/';
        }
        return $js;
    }

    //}}}
    //{{{ public function hook_admin_js_header()
    public function hook_admin_js_header()
    {
        $js = array();
        if (strpos(URI_PATH, '/admin/module/User/edit_user/') === 0 || 
            URI_PATH === '/admin/login/')
            {
                $js[] = '/file/module/User/sha1.js';
            }
        return $js;
    }

    //}}}
    //{{{ public function hook_admin_login_build()
    /**
     * Custom hook by this module
     * Return value should be a form group array for all other modules
     */
    public function hook_admin_login_build()
    {
        $layouts = array(
            'name' => array(
                'field' => Field::layout('text'),
                'name' => 'name',
                'type' => 'text'
            ),
            'pass' => array(
                'field' => Field::layout('password_sha1'),
                'name' => 'pass',
                'type' => 'password_sha1'
            )
        );
        $group = array( 
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'username'
                    ),
                    'fields' => $layouts['name']
                ),
                array(
                    'label' => array(
                        'text' => 'password'
                    ),
                    'fields' => $layouts['pass']
                )
            )
        );
        return array('layout' => $layouts, 'form' => $group);
    }

    //}}}
    //{{{ public function hook_admin_login_submit($data, $extra)
    public function hook_admin_login_submit($data, $extra)
    {
        $account = $this->is_account($data['name'], $data['pass']);
        if ($account)
        {
            $_SESSION['user']['name'] = $account['name'];
            $_SESSION['user']['pass'] = $account['pass'];
            $uac = MonDB::selectCollection('user_account');
            $user = $uac->findOne(array('name' => $account['name']));
            $user['logged_in'] = time();
            $uac->save($user);
            $results = array(
                'success' => TRUE,
            );
        }
        else
        {
            $results = array(
                'success' => FALSE,
                'messages' => array(
                    'notices' => array(
                        'Username and password are not valid.'
                    )
                )
            );
        }
        return $results;
    }
    //}}}
    //{{{ public function hook_admin_module_page($page)
    public function hook_admin_module_page($page)
    {
        switch ($page)
        {
            case 'edit':
            break;
        }
    }
    
    //}}}
    //{{{ public function hook_admin_nav()
    public function hook_admin_nav()
    {
        $links = array();
        if (User::perm('edit self'))
        {
            $links = array_merge($links, array(
                '<a href="/admin/module/User/edit_user/'.self::$user->user['id'].'/">Edit Your Account</a>',
            ));
        }
        if (User::perm('view users'))
        {
            $links = array_merge($links, array(
                '<a href="/admin/module/User/users/">Users</a>',
            ));
        }
        if (User::perm('view groups'))
        {
            $links = array_merge($links, array(
                '<a href="/admin/module/User/groups/">Groups</a>',
            ));
        }
        if (User::perm('create users'))
        {
            $links = array_merge($links, array(
                '<a href="/admin/module/User/create_user/">Create User</a>',
            ));
        }
        if (User::perm('create groups'))
        {
            $links = array_merge($links, array(
                '<a href="/admin/module/User/create_group/">Create Group</a>',
            ));
        }
        return array('User' => $links);
    }

    //}}}
    //{{{ public function hook_profile_fields($id, $data)
    public function hook_profile_fields($id, $data)
    {
        $items = array(
            array(
                'label' => 'First Name',
                'type' => 'text',
                'name' => 'first_name',
                'value' => deka('', $data, 'first_name')
            ),
            array(
                'label' => 'Last Name',
                'type' => 'text',
                'name' => 'last_name',
                'value' => deka('', $data, 'last_name')
            ),
            array(
                'label' => 'City',
                'type' => 'text',
                'name' => 'city',
                'value' => deka('', $data, 'city')
            ),
            array(
                'label' => 'State',
                'type' => 'text',
                'name' => 'state',
                'value' => deka('', $data, 'state')
            ),
            array(
                'label' => 'AIM Scree Name',
                'type' => 'text',
                'name' => 'aim_sn',
                'value' => deka('', $data, 'aim_sn')
            ),
            array(
                'label' => 'Hide profile from other users?',
                'type' => 'checkbox_boolean',
                'name' => 'privacy',
                'value' => deka('', $data, 'privacy')
            ),
        );

        return $items;
    }

    //}}}
    //{{{ public function hook_profile_keys()
    public function hook_profile_keys()
    {
        return array(
            'first_name',
            'last_name',
            'city',
            'state',
            'aim_sn',
            'privacy'
        );
    }

    //}}}
    //{{{ public function hook_profile_validate($id, $data)
    public function hook_profile_validate($id, $data)
    {
        $success = TRUE;
        foreach ($data as $k => $v)
        {
            switch ($k)
            {
                case 'state':
                    if ($v !== '' && strlen($v) !== 2)
                    {
                        $success = FALSE;
                    }
                break;
                default:
                    $success = TRUE;
            }
        }
        return array(
            'success' => $success,
            'module_entry_id' => $id,
            'data' => $data
        );
    }

    //}}}
    //{{{ public function hook_search_results($terms)
    public function hook_search_results($terms)
    {
        foreach ($terms as $term)
        {
            $results[$term] = array(
                array(
                    'snippet' => "This is the user $term",
                    'link' => "/admin/user/$term"
                ),
                array(
                    'snippet' => "This is another user $term",
                    'link' => "/admin/user/another/$term"
                ),
            );
        }

        return $results;
    }

    //}}}
    //{{{ public function hook_workflow_responses()
    public function hook_workflow_responses()
    {
        $uac = MonDB::selectCollection('user_account');
        $query = array(
            'name' => array(
                '$ne' => User::USER_ANONYMOUS,
            ),
        );
        $users = iterator_to_array($uac->find($query));
        foreach ($users as &$user)
        {
            $user_dropdown[$user['name']] = $user['nice_name'];
        }
        return array(
            'email user' => array(
                'label' => 'Email User',
                'params' => array(
                    'user' => array(
                        'label' => 'User',
                        'type' => 'dropdown',
                        'options' => $user_dropdown,
                        'description' => ''
                    ),
                    'message' => array(
                        'label' => 'Message',
                        'type' => 'textarea',
                        'options' => $user_dropdown,
                        'description' => '<p>This is the description message</p>',
                        'value' => 'default value test'
                    )
                ),
            )
        );
    }
    //}}}
    //{{{ public function hook_workflow_triggers()
    public function hook_workflow_triggers()
    {
        $param_roles = array(
            'role' => array(
                'label' => 'User Role',
                'type' => 'dropdown',
                'options' => array()
            )
        );
        $ugc = MonDB::selectCollection('user_group');
        $query = array(
            'name' => array(
                '$ne' => User::GROUP_ANONYMOUS,
            ),
        );
        $groups = iterator_to_array($ugc->find($query));
        foreach ($groups as &$group)
        {
            $param_roles['role']['options'][$group['name']] = $group['nice_name'];
        }
        return array(
            'user create' => array(
                'label' => 'User account created',
                'subtriggers' => array(
                    '' => NULL,
                    'with role' => array(
                        'label' => 'with role',
                        'params' => $param_roles
                    )
                )
            ),
            'user delete' => array(
                'label' => 'User account deleted',
                'subtriggers' => array(
                    '' => NULL,
                    'with role' => array(
                        'label' => 'with role',
                        'params' => $param_roles
                    )
                )
            )
        );
    }
    //}}}
    //{{{ public function cb_user_perm($perms)
    public function cb_user_perm($perms)
    {
        self::$perm = $perms;
    }
    //}}}
    //{{{ protected static function find_info()
    protected static function find_info()
    {
        if (is_null(self::$user))
        {
            if (isset($_SESSION) && (eka($_SESSION, 'user', 'name') && eka($_SESSION, 'user', 'pass')))
            {
                self::$user = new UserInfo($_SESSION['user']['name']);
                if (!self::$user->verify_password($_SESSION['user']['pass']))
                {
                    self::$user = new UserInfo(User::USER_ANONYMOUS);
                }
            }
            else
            {
                self::$user = new UserInfo(User::USER_ANONYMOUS);
            }
            self::$methods = get_class_methods(self::$user);
        }
    }
    //}}}
    //{{{ protected function is_account($name, $pass)
    /**
     * Checks if credentials match
     * @param string $name username
     * @param string $pass sha1 hashed password
     */
    protected function is_account($name, $pass)
    {
        $user = MonDB::selectCollection('user_account')->findOne(array('name' => $name));
        if (!is_null($user) && $user['pass'] === sha1($user['salt'].$pass))
        {
            return array(
                'name' => $user->name,
                'pass' => $user->pass
            );
        }
        return FALSE;
    }

    //}}}
    //{{{ public static function find_groups($group = NULL)
    /**
     * Looks up all groups in the system, returns one if specified or all
     *
     * @param mixed $group optional name as string
     * @return array
     */
    public static function find_groups($group = NULL)
    {
        $ugc = MonDB::selectCollection('user_group');
        if (is_null($group))
        {
            if (empty(self::$groups))
            {
                $groups = iterator_to_array($ugc->find());
                foreach ($groups as &$g)
                {
                    self::$groups[$g['name']] = $g;
                }
            }
            return self::$groups;
        }
        else
        {
            $g = deka($ugc->findOne(array('name' => $group)), self::$groups, $group);
            if (is_null($g))
            {
                $g = array();
            }
            return $g;
        }
    }

    //}}}
    //{{{ public static function info($key)
    /**
     * Returns the user info
     *
     * @param string $key self::$info array key
     * @return string|NULL
     */
    public static function info($key)
    {
        self::find_info();
        return self::$user->info($key);
    }

    //}}}
    //{{{ public static function i($key)
    /**
     * Alias for self::info($key)
     */
    public static function i($key)
    {
        return self::info($key);
    }

    //}}}
    //{{{ public static function update($key, $value)
    /**
     * Update the user array and flag for updating
     * The params are unlimited. If there are more than two parameters, the
     * parameters except last is used to drill down into $user, and the
     * last is the value.
     *
     * @param string $key
     * @param mixed $value string or array
     */
    public static function update()
    {
        self::find_info();
        $caller = array(self::$user, 'update');
        $args = func_get_args();
        return call_user_func_array($caller, $args);
    }

    //}}}
    //{{{ public static function check_perm($permission)
    /**
     * Checks if the current user has the permission token set
     *
     * @param string $permission permission token
     * @return boolean
     */
    public static function check_perm($permission)
    {
        self::find_info();
        return self::$user->check_perm($permission);
    }

    //}}}
    //{{{ public static function check_group($group)
    /**
     * Checks if current user has specific group token
     *
     * @param mixed $group group name or group id as integer
     * @return boolean
     */
    public static function check_group($group)
    {
        self::find_info();
        return self::$user->check_group($group);
    }
    
    //}}}
    //{{{ public static function add_groups($groups)
    public static function add_groups($groups)
    {
        self::find_info();
        return self::$user->add_groups($groups);
    }
    
    //}}}
    //{{{ public static function has_perm()
    /**
     * Checks if a user has a certain permission token
     * This does a quick check to see if there is permission for authorization,
     * meaning this looks at the aggregate permission list from all of the
     * user's groups as well. To see if a specific permission is explicitly set
     * for purposes such as user permission forms, use the check_perm method.
     *
     * @param string $permission permission token
     * @return boolean
     */
    public static function has_perm()
    {
        self::find_info();
        return self::$user->has_perm(func_get_args());
    }

    //}}}
    //{{{ public static function perm($permission)
    /** 
     * Alias of has_perm()
     */
    public static function perm($permission)
    {
        self::find_info();
        return self::$user->perm($permission);
    }

    //}}}
    //{{{ public static function permissions()
    public static function permissions()
    {
        return self::$perm;
    }

    //}}}
    //{{{ public static function search_perms($needle)
    /**
     * Searches for matching permission token strings
     *
     * @param string $needle search term
     * @return boolean
     */
    public static function search_perms($needle)
    {
        self::find_info();
        return self::$user->search_perms($needle);
    }

    //}}}
    //{{{ public static function setting()
    /**
     * Wrapper for info() method on $setting property with drill down
     * This gets the same data as info() but only for the $setting property. It
     * then falls back to the data class to see if there is a possible default.
     * @return mixed
     */
    public static function setting()
    {
        self::find_info();
        $caller = array(self::$user, 'setting');
        $args = func_get_args();
        return call_user_func_array($caller, $args);
    }

    //}}}
    //{{{ public static function field_content_group($type, $data)
    public static function field_content_group($type, $data)
    {
        $ugc = MonDB::selectCollection('user_group');
        $group = $ugc->findOne(array('name' => self::GROUP_ANONYMOUS));
        if (is_null($group))
        {
            $group = self::anonymous_info();
        }
        else
        {
            unset($group['permission']);
        }
        return $group;
    }

    //}}}
    //{{{ public static function field_content_group_multiple($type, $data)
    public static function field_content_group_multiple($type, $data)
    {
        return self::field_content_group($type, $data);
    }

    //}}}
    //{{{ public static function field_content_user($type, $data)
    public static function field_content_user($type, $data)
    {
        $uac = MonDB::selectCollection('user_account');
        $user = $uac->findOne(array('name' => $data));
        if (is_null($user))
        {
            $user = self::anonymous_user();
        }
        else
        {
            unset($user['pass'], $user['salt'], $user['permission']);
        }
        return $user;
    }

    //}}}
    //{{{ public static function field_content_user_current($type, $data)
    public static function field_content_user_current($type, $data)
    {
        $uac = MonDB::selectCollection('user_account');
        $user = $uac->findOne(array('name' => $data));
        if (is_null($user))
        {
            $user = self::anonymous_user();
        }
        else
        {
            unset($user['pass'], $user['salt'], $user['permission']);
        }
        return $user;
    }

    //}}}
    //{{{ public static function field_content_user_multiple($type, $data)
    //TODO uhh this! once multiple user accounts are made
    public static function field_content_user_multiple($type, $data)
    {
        return self::field_content_user($type, $data);
    }

    //}}}
    //{{{ public static function field_form_group($name, $value, $extra)
    public static function field_form_group($name, $value, $extra)
    {
        $ugc = MonDB::selectCollection('user_group');
        $groups = iterator_to_array($ugc->find());
        $extra['options'][''] = 'none';
        foreach ($groups as &$group)
        {
            $extra['options'][$group['name']] = $group['nice_name'];
        }
        return Field::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_group_multiple($name, $value, $extra)
    public static function field_form_group_multiple($name, $value, $extra)
    {
        $ugc = MonDB::selectCollection('user_group');
        $groups = iterator_to_array($ugc->find());
        foreach ($groups as $group)
        {
            $extra['options'][$group['name']] = $group['nice_name'];
        }
        unset($extra['options']['']);
        $extra['attr']['multiple'] = 'multiple';
        $extra['attr']['size'] = 3;
        return Field::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user($name, $value, $extra)
    public static function field_form_user($name, $value, $extra)
    {
        $uac = MonDB::selectCollection('user_account');
        $groups = iterator_to_array($uac->find());
        $extra['options'][''] = 'none';
        foreach ($users as &$user)
        {
            $extra['options'][$user['name']] = $user['nice_name'];
        }
        return Field::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user_current($name, $value, $extra)
    /** 
     * In the content module, when going back into a revision this will cause
     * problems. If editing an old revision with no data set for this field
     * then saving will save the newest revision with that noted. And the 
     * previously newest revision might have had this value set, but it is now
     * over written. The best way to use this field is to assign it to the
     * content type as early as possible.
     */
    public static function field_form_user_current($name, $value, $extra)
    {
        $action = deka('add', $extra, 'form_action');
        $value = is_array($value) ? $value[0] : $value;
        if ($action === 'edit')
        {
            switch ($value)
            {
                case '':
                case self::ID_NONE:
                    $field_name = 'None';
                    $field_value = self::ID_NONE;
                break;
                case self::ID_ANONYMOUS:
                    $field_name = 'Anonymous';
                    $field_value = self::ID_ANONYMOUS;
                break;
                default:
                    $uac = MonDB::selectCollection('user_account');
                    $user = $uac->findOne(array('name' => $value));
                    $field_name = $user ? $user['nice_name'] : 'user: '.$value.' (account no longer exists)';
                    $field_value = $value;
                break;
            }
        }
        else
        {
            $field_name = self::info('nice_name');
            $field_value = self::info('name');
        }
        $extra['html'] = '<p>'.$field_name.'</p>';
        return Field::act('form', 'hidden', $name, $field_value, $extra);
    }

    //}}}
    //{{{ public static function field_form_user_multiple($name, $value, $extra)
    public static function field_form_user_multiple($name, $value, $extra)
    {
        $uac = MonDB::selectCollection('user_account');
        $groups = iterator_to_array($uac->find());
        foreach ($users as &$user)
        {
            $extra['options'][$user['name']] = $user['nice_name'];
        }
        unset($extra['options']['']);
        $extra['attr']['multiple'] = 'multiple';
        $extra['attr']['size'] = 3;
        return Field::act('form', 'dropdown', $name, $value, $extra);
    }

    //}}}
    //{{{ public static function field_public_group()
    public static function field_public_group()
    {
        return array(
            'description' => 'A user group',
            'meta' => FALSE,
            'name' => 'User Group',
        );
    }

    //}}}
    //{{{ public static function field_public_group_multiple()
    public static function field_public_group_multiple()
    {
        return array(
            'description' => 'Multiple user groups',
            'meta' => FALSE,
            'name' => 'User Group (multiple select menu)',
        );
    }

    //}}}
    //{{{ public static function field_public_user()
    public static function field_public_user()
    {
        return array(
            'description' => 'A user account',
            'meta' => FALSE,
            'name' => 'User Account',
        );
    }

    //}}}
    //{{{ public static function field_public_user_current()
    public static function field_public_user_current()
    {
        return array(
            'description' => 'Records the current user',
            'meta' => FALSE,
            'name' => 'User Account (the current one)',
        );
    }

    //}}}
    //{{{ public static function field_public_user_multiple()
    public static function field_public_user_multiple()
    {
        return array(
            'description' => 'Multiple user accounts',
            'meta' => FALSE,
            'name' => 'User Account (multiple select menu)',
        );
    }

    //}}}
}
//}}}
//{{{ class UserInfo
/**
 * Holds information about a specific user or anonymous account
 */
class UserInfo
{
    //{{{ properties
    public $user;
    //}}}
    //{{{ public function __construct($name = User::USER_ANONYMOUS)
    public function __construct($name = User::USER_ANONYMOUS)
    {
        $this->find_user($name);
    }
    //}}}
    //{{{ public function info($key)
    /**
     * Returns the user info
     *
     * @param string $key self::$info array key
     * @return string|NULL
     */
    public function info($key)
    {
        return deka(NULL, $this->user, $key);
    }

    //}}}
    //{{{ public function i($key)
    /**
     * Alias for self::info($key)
     */
    public function i($key)
    {
        return $this->info($key);
    }

    //}}}
    //{{{ public function update($key, $value)
    /**
     * Update the user array and flag for updating
     * The params are unlimited. If there are more than two parameters, the
     * parameters except last is used to drill down into $user, and the
     * last is the value.
     *
     * @param string $key
     * @param mixed $value string or array
     */
    public function update()
    {
        $c = func_num_args();
        if ($c > 1)
        {
            $this->changed = TRUE;
            $user = &$this->user;
            for ($i = 0; $i < $c; ++$i)
            {
                $arg = func_get_arg($i);
                if ($i === ($c - 1))
                {
                    $user = $arg;
                    break;
                }
                elseif (eka($user, $arg))
                {
                    $user =& $user[$arg];
                }
                else
                {
                    $user[$arg] = array();
                    $user =& $user[$arg];
                    break;
                }
            }
            $this->save_user();
        }
    }

    //}}}
    //{{{ public function check_perm($permission)
    /**
     * Checks if the current user has the permission token set
     *
     * @param string $permission permission token
     * @return boolean
     */
    public function check_perm($permission)
    {
        return in_array($permission, $this->user['permission']);
    }

    //}}}
    //{{{ public function check_group($group)
    /**
     * Checks if current user has specific group token
     *
     * @param mixed $group group name or group id as integer
     * @return boolean
     */
    public function check_group($group)
    {
        foreach ($this->user['group'] as $g)
        {
            if ((is_int($group) && (int)$g['id'] === $group) || ($g['name'] === $group))
            {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    //}}}
    //{{{ public function search_perms($needle)
    public function search_perms($needle)
    {
        $perms = array();
        foreach ($this->user['total_permission'] as $perm)
        {
            if (strpos($perm, $needle) !== FALSE)
            {
                $perms[] = $perm;
            }
        }
        return $perms;
    }
    //}}}
    //{{{ public function has_perm()
    /**
     * Checks if a user has a certain permission token
     * This does a quick check to see if there is permission for authorization,
     * meaning this looks at the aggregate permission list from all of the
     * user's groups as well. To see if a specific permission is explicitly set
     * for purposes such as user permission forms, use the check_perm method.
     *
     * Can pass multiple permission tokens to check if one exists.
     *
     * @param string $permission permission token
     * @return boolean
     */
    public function has_perm()
    {
        if (eka($this->user, 'total_permission'))
        {
            $perms = &$this->user['total_permission'];
            if (in_array('admin', $perms))
            {
                return TRUE;
            }
            else
            {
                if (func_num_args())
                {
                    $tokens = func_get_args();
                    foreach ($tokens as $token)
                    {
                        if (is_array($token))
                        {
                            foreach ($token as $t)
                            {
                                if (in_array($t, $perms))
                                {
                                    return TRUE;
                                }
                            }
                        }
                        else
                        {
                            if (in_array($token, $perms))
                            {
                                return TRUE;
                            }
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    //}}}
    //{{{ public function perm($permission)
    /** 
     * Alias of has_perm()
     */
    public function perm($permission)
    {
        return $this->has_perm($permission);
    }

    //}}}
    //{{{ public function setting()
    /**
     * Wrapper for info() method on $setting property with drill down
     * This gets the same data as info() but only for the $setting property. It
     * then falls back to the data class to see if there is a possible default.
     * @return mixed
     */
    public function setting()
    {
        $setting = $this->user['setting'];
        $c = func_num_args();
        $success = TRUE;
        for ($i = 0; $i < $c; ++$i)
        {
            $arg = func_get_arg($i);
            if (eka($setting, $arg))
            {
                $setting = $setting[$arg];
            }
            else
            {
                $success = FALSE;
                break;
            }
        }
        if ($success)
        {
            return $setting;
        }
        else
        {
            $caller = array('Data', 'query');
            $args = func_get_args();
            array_unshift($args, 'user');
            return call_user_func_array($caller, $args);
        }
    }

    //}}}
    //{{{ public function verify_password($password)
    public function verify_password($password)
    {
        return $password === $this->user['pass'];
    }
    //}}}
    //{{{ private function find_user($name)
    private function find_user($name)
    {
        $uac = MonDB::selectCollection('user_account');
        $user = $uac->findOne(array('name' => $name));
        if (!is_null($user))
        {
            $user['total_permission'] = $user['permission'];
            $ugc = MonDB::selectCollection('user_group');
            $query = array(
                '_id' => array(
                    '$in' => $user['group_ids'],
                ),
            );
            $groups = iterator_to_array($ugc->find($query));
            foreach ($groups as &$group)
            {
                $user['group'][] = $group;
                $user['total_permission'] = array_merge($user['total_permission'], $group['permission']);
            }
            $user['total_permission'] = array_unique($user['total_permission']);
            $this->user = $user;
        }
        elseif ($name !== User::USER_ANONYMOUS)
        {
            $this->find_user(User::USER_ANONYMOUS);
        }
        else
        {
            $this->fake_user();
        }
    }

    //}}}
    //{{{ private function fake_user()
    /**
     * Returns a fallback anonymous account in case it's not in the database
     * @return array
     */
    private function fake_user()
    {
        $this->user = array(
            'id' => User::ID_ANONYMOUS,
            'pass' => sha1(random_string(40)),
            'name' => User::USER_ANONYMOUS,
            'salt' => random_string(5),
            'email' => '',
            'joined' => '',
            'permission' => array(),
            'setting' => array()
        );
    }
    //}}}
    //{{{ private function save_user()
    /**
     * Checks for user account updates and saves to DB if it is
     */
    private function save_user()
    {
        if ($this->changed)
        {
            $info = $this->user;
            $ugc = MonDB::selectCollection('user_account');
            $user = $ugc->findOne(array('_id' => $info['_id']));
            $user = array_merge($user, $info);
            $ugc->save($user);
        }
    }

    //}}}
}
//}}}

?>
