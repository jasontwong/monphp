<?php

class MPAdmin
{
    //{{{ constants
    const MODULE_AUTHOR = '';
    const MODULE_DESCRIPTION = 'Automated admin interface';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = 'MPUser';

    const TYPE_NOTICE = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_ERROR = 3;
    const TYPE_IMPORTANT = 4;

    //}}}
    //{{{ properties
    protected static $v = array();
    protected static $theme = 'default';

    //}}}
    //{{{ constructor
    /**
     * @param int $state current state of module manager
     */
    public function __construct()
    {
    }

    //}}}

    //{{{ private function _rpc_dashboard($data)
    private function _rpc_dashboard($data)
    {
        $data = (array)json_decode($data['json']);
        foreach ($data as $side => &$elements)
        {
            $elements = (array)$elements;
            foreach ($elements as &$element)
            {
                $element = (array)$element;
            }
        }
        MPUser::update('setting', 'admin', 'dashboard', $data);
    }

    //}}}
    //{{{ private function _rpc_quicklinks($data)
    private function _rpc_quicklinks($data)
    {
        $data = (array)json_decode($data['json']);
        MPUser::update('setting', 'admin', 'quicklinks', $data);
        MPUser::update('setting', 'admin', 'quicklinks', $data);
    }

    //}}}
    //{{{ private function _rpc_nav($data)
    private function _rpc_nav($data)
    {
        $data = json_decode($data['json'], TRUE);
        MPUser::update('setting', 'admin', 'nav', $data);
        MPUser::update('setting', 'admin', 'nav', $data);
    }

    //}}}

    //{{{ public function cb_mpadmin_css($css)
    public function cb_mpadmin_css($css)
    {
        $o = '';
        foreach ($css as $module => $styles)
        {
            foreach ($styles as $media => $hrefs)
            {
                foreach ($hrefs as $href)
                {
                    $o .= "<link rel='stylesheet' media='{$media}' href='{$href}'>\n";
                }
            }
        }
        return $o;
    }

    //}}} 
    //{{{ public function cb_mpadmin_dashboard($modules)
    /**
     * Build out dashboard widgets based on what the modules specify
     * Each module that wants widgets or dashboard elements must return an
     * array. Each element in that array is another array with this layout:
     *      'admin' => array(
     *          array(
     *              'title' => 'Last Login',
     *              'content' => 'your html here'
     *          ),
     *          array(
     *              'title' => 'Bad Login Attempts',
     *              'content' => 'other html here'
     *          )
     *      )
     * If the user does not have any specific ordering in place, this callback
     * sorts by title alphabatically then arranges them side by side in order.
     * If only a few are ordered, then those are placed and the rest are placed
     * like the default behavior at the end.
     *
     * @param array $modules module hook data
     * @return string
     */
    public function cb_mpadmin_dashboard($modules)
    {
        $elements = $titles = array();
        $left = $right = $trash = 0;
        $sides = array('left', 'right', 'trash');
        $placement = MPUser::setting('admin', 'dashboard');
        foreach ($modules as $module => $boards)
        {
            foreach ($boards as $board)
            {
                $key = preg_replace('/\W+/', '_', $module.'__'.$board['title']);
                $elements[$key] = $board;
            }
        }
        //var_dump($elements, $placement);
        $boards = array(
            'left' => array(), 
            'right' => array(),
            'trash' => array()
        );
        // user placed boards
        foreach ($sides as $side)
        {
            if (eka($placement, $side))
            {
                foreach ($placement[$side] as $key => $details)
                {
                    if (eka($elements, $key))
                    {
                        ++$$side;
                        $elements[$key]['key'] = $key;
                        $elements[$key]['fold'] = $details['fold'];
                        $boards[$side][] = $elements[$key];
                        unset($elements[$key]);
                    }
                }
            }
        }
        // what's left?
        if (count($elements))
        {
            foreach ($elements as $key => &$board)
            {
                $board['key'] = $key;
                $board['fold'] = 'opened';
                $titles[] = strtolower($board['title']);
            }
            array_multisort($titles, SORT_REGULAR, $elements);
            // even out sides
            if ($left != $right)
            {
                $less = $left < $right ? 'left' : 'right';
                $more = $left > $right ? 'left' : 'right';
                while ($$less < $$more)
                {
                    $temp = array_shift($elements);
                    if (is_null($temp))
                    {
                        break;
                    }
                    $boards[$less][] = $temp;
                    ++$$less;
                }
            }
            // spread the rest evenly
            $side = 'right';
            foreach ($elements as $key => $element)
            {
                $side = $side === 'left' ? 'right' : 'left';
                $boards[$side][] = $element;
            }
        }
        return $boards;
    }

    //}}}
    //{{{ public function cb_mpadmin_js($js)
    public function cb_mpadmin_js($js)
    {
        $done = array();
        $o = '';
        foreach ($js as $mod => $scripts)
        {
            foreach ($scripts as $script)
            {
                if (array_search($script, $done) === FALSE)
                {
                    $o .= "<script type='text/javascript' src='{$script}'></script>\n";
                    $done[] = $script;
                }
            }
        }
        return $o;
    }

    //}}} 
    //{{{ public function cb_mpadmin_js_header($js)
    public function cb_mpadmin_js_header($js)
    {
        return $this->cb_mpadmin_js($js);
    }

    //}}} 
    //{{{ public function cb_mpadmin_login_build($mods)
    public function cb_mpadmin_login_build($mods)
    {
        $layout = new MPField();
        $form = new MPFormRows;
        $form->attr = array(
            'method' => 'post',
            'action' => '/admin/login/'
        );
        foreach ($mods as $mod => $login)
        {
            if (eka($login, 'layout'))
            {
                foreach ($login['layout'] as $lay)
                {
                    $layout->add_layout($lay);
                }
            }
            $form->add_group($login['form'], 'login['.$mod.']');
        }

        $layout->add_layout(array(
            'field' => MPField::layout(
                'submit_reset',
                array(
                    'submit' => array(
                        'text' => 'Login'
                    ),
                    'reset' => array(
                        'text' => 'Cancel'
                    )
                )
            ),
            'name' => 'go',
            'type' => 'submit_reset'
        ));
        $form->add_group(
            array(
                'rows' => array(
                    array(
                        'fields' => $layout->get_layout('go')
                    ),
                )
            )
        );
        return array($layout, $form);
    }

    //}}}
    //{{{ public function cb_mpadmin_login_submit($results)
    /**
     * Processes all module contribution to the admin_login_submit custom hook
     * Each module is to return an array with the 'success' key a boolean
     * allowing the user to log in or not with the form submission.
     */
    public function cb_mpadmin_login_submit($results)
    {
        $success = TRUE;
        $messages = array();
        foreach ($results as $row)
        {
            if ($row['success'] === FALSE)
            {
                $success = FALSE;
            }
            if (isset($row['messages']['errors']))
            {
                $messages['errors'][] = $row['messages']['errors'];
            }
            if (isset($row['messages']['notices']))
            {
                $messages['notices'][] = $row['messages']['notices'];
            }
            if (isset($row['messages']['successes']))
            {
                $messages['successes'][] = $row['messages']['successes'];
            }
        }
        $_SESSION['admin']['logged_in'] = $success;
        if ($success)
        {
            MPUser::update('setting', 'admin', 'last_login', time());
        }
        return array(
            'login' => $success,
            'messages' => $messages
        );
    }

    //}}}
    //{{{ public function cb_mpadmin_logout()
    public function cb_mpadmin_logout()
    {
    }

    //}}}
    //{{{ public function cb_mpadmin_module_page($page)
    /**
     * Build module page
     * This should just quickly return the $page, which is a complete chunk of
     * HTML which the module has built.
     *
     * @param string $page HTML of module page
     * @return string
     */
    public function cb_mpadmin_module_page($page)
    {
        return array_pop($page);
    }

    //}}}
    //{{{ public function cb_mpadmin_nav($menu)
    /**
     *  Expects array with keys as "zones"
     *
     *  e.g. 
     *  array('Add' => array('Label' => 'uri'))
     *
     *  @param array $menu
     *  @return array $nav
     */
    public function cb_mpadmin_nav($menu)
    {
        // Creates nav ordering
        $nav = array();

        foreach ($menu as $mod => $items)
        {
            foreach ($items as $title => $links)
            {
                if (!ake($title, $nav))
                {
                    $nav[$title] = array();
                }
                $nav[$title] = array_merge($nav[$title], $links);
            }
        }

        // settings always last nav item and overwrites previous settings
        if (ake('Settings', $nav))
        {
            unset($nav['Settings']);
        }
        if (MPUser::has_perm('admin settings'))
        {
            $nav['Settings'] = array(
                '<a href="/admin/settings/site/">Site</a>'
            );
        }
    
        $settings = array_keys(MPModule::hook_user('data_info'));
        foreach ($settings as $mod)
        {
            if (!MPUser::perm($mod.' settings') && !MPUser::has_perm('admin settings'))
            {
                continue;
            }
            $nav['Settings'][] = '<a href="/admin/settings/'.$mod.'/">'.$mod.'</a>';
        }

        return $nav;
    }

    //}}} 
    //{{{ public function cb_mpadmin_tinymce($modules)
    public function cb_mpadmin_tinymce($modules)
    {
        $options = $modules['MPAdmin'];
        foreach ($modules as $module => $module_options)
        {
            $options = array_merge($options, $module_options);
        }
        return json_encode($options);
    }

    //}}}

    //{{{ public function hook_mpadmin_css()
    public function hook_mpadmin_css()
    {
        return array(
            'screen' => array(
                '/file/module/MPAdmin/js/jquery/themes/base/jquery.ui.core.css',
                '/file/module/MPAdmin/js/jquery/themes/base/jquery.ui.slider.css',
                '/admin/static/MPAdmin/screen.css/',
                '/admin/static/MPAdmin/field.css/'
            )
        );
    }

    //}}}
    //{{{ public function hook_mpadmin_js()
    public function hook_mpadmin_js()
    {
        $js = array();
        if (URI_PATH === '/admin/')
        {
            $js[] = '/file/module/MPAdmin/js/jquery/ui/jquery.ui.sortable.js';
            $js[] = '/admin/static/MPAdmin/dashboard.js/';
        }
        if (URI_PATH === '/admin/settings/')
        {
        }
        return $js;
    }

    //}}}
    //{{{ public function hook_mpadmin_js_header()
    public function hook_mpadmin_js_header()
    {
        return array(
            '/file/module/MPAdmin/js/jquery/jquery.js',
            '/file/module/MPAdmin/js/tiny_mce/tiny_mce.js',
            '/file/module/MPAdmin/js/tiny_mce/jquery.tinymce.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.core.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.widget.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.mouse.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.slider.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.tabs.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.sortable.js',
            '/file/module/MPAdmin/js/jquery/ui/jquery.ui.datepicker.js',
            //'/file/module/MPAdmin/js/timepicker.js',
            '/admin/static/MPAdmin/admin.js/',
            '/admin/static/MPAdmin/field.js/'
        );
    }

    //}}}
    //{{{ public function hook_mpadmin_module_page($page)
    public function hook_mpadmin_module_page($page)
    {
    }

    //}}}
    //{{{ public function hook_mpadmin_tinymce()
    public function hook_mpadmin_tinymce()
    {
        $options = array(
            'plugins' => 'inlinepopups,spellchecker',
            'theme' => 'advanced',
            'skin' => 'krate',
            'theme_advanced_blockformats' => 'p,div,h1,h2,h3,h4,h5,h6',
            'theme_advanced_buttons1' => "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,styleselect,formatselect,separator,sup,sub",
            'theme_advanced_buttons2' => "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,separator,anchor,image,separator,forecolor,charmap,removeformat,spellchecker,separator,hr,code",
            'theme_advanced_buttons3' => "",
            'theme_advanced_more_colors' => TRUE,
            'theme_advanced_toolbar_location' => 'top',
            'theme_advanced_statusbar_location' => 'bottom',
            'theme_advanced_resizing' => TRUE,
            'theme_advanced_resize_horizontal' => FALSE,
            'relative_urls' => FALSE,
            'width' => '508'
        );
        if (is_array(MPData::query('MPAdmin', 'tinyMCE')))
        {
            $options = array_merge($options, MPData::query('MPAdmin', 'tinyMCE'));
        }
        return $options;
    }

    //}}}
    //{{{ public function hook_mpadmin_settings_fields()
    public function hook_mpadmin_settings_fields()
    {
        $hidden = is_file(MPData::query('MPAdmin', 'logo', 'name'))
            ? array('delete')
            : array();
        /* switch to filemanager field and fallback should be inside the MPAdmin module
         * Maybe even merge the filemanager with the admin system?
        $logo = array(
            'field' => MPField::layout(
                'file',
                array(
                    'data' => array(
                        'label' => 'MPAdmin Logo'
                    )
                )
            ),
            'name' => 'logo',
            'type' => 'file',
            'hidden' => $hidden,
            'value' => array(
                'group_key' => 'MPAdmin',
            ),
            'html_before' => array(
                'data' => '<img src="/file/upload/'.MPData::query('MPAdmin', 'logo', 'name').'" /><br />'
            )
        );
        */
        $bgcolor = array(
            'field' => MPField::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Background Color'
                    )
                )
            ),
            'name' => 'bgcolor',
            'type' => 'text',
            'value' => array(
                'data' => MPData::query('MPAdmin', 'bgcolor')
            )
        );
        $tiny_data = MPData::query('MPAdmin', 'tinyMCE');
        if ($tiny_data['theme_advanced_more_colors'] === 'false')
        {
            unset($tiny_data['theme_advanced_more_colors']);
        }
        $tinyMCE = array(
            'field' => MPField::layout('tinyMCE'),
            'name' => 'tinyMCE',
            'type' => 'tinyMCE',
            'value' => $tiny_data
        );

        return array($logo, $bgcolor, $tinyMCE);
    }

    //}}}
    //{{{ public function hook_mpadmin_settings_validate($name, $data)
    public function hook_mpadmin_settings_validate($name, $data)
    {
        $success = FALSE;
        switch ($name)
        {
            case 'logo':
                /*
                if (!ake('tmp_name', $data))
                {
                    $data = array();
                    break;
                }
                if (!is_dir(DIR_FILE.'/upload'))
                {
                    $can_move = mkdir(DIR_FILE.'/upload', 0777, TRUE);
                }
                if (is_file(DIR_FILE.'/upload/admin_logo.jpg'))
                {
                    unlink(DIR_FILE.'/upload/admin_logo.jpg');
                }
                if (move_uploaded_file($data['tmp_name'], DIR_FILE.'/upload/admin_logo.jpg'))
                {
                    $data['name'] = 'admin_logo.jpg';
                    $data['tmp_name'] = DIR_FILE.'/upload/'.$data['name'];
                    $success = TRUE;
                }
                */
            break;
            case 'bgcolor':
                if (substr($data, 0, 1) === '#' && (strlen($data) === 4 || strlen($data) === 7))
                {
                    $success = TRUE;
                }
                else
                {
                    $data = '';
                }
            break;
            case 'tinyMCE':
                foreach ($data as $k => &$setting)
                {
                    $success = TRUE;
                    switch ($k)
                    {
                        case 'theme_advanced_more_colors':
                            $setting = $setting
                                ? 'true'
                                : 'false';
                        break;
                        default:
                            if (strlen($setting))
                            {
                                continue;
                            }
                            unset($data[$k]);
                    }
                }
            break;
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }

    //}}}
    //{{{ public function hook_mpuser_perm()
    public function hook_mpuser_perm()
    {
        $perms = array(
            'admin access' => 'Can access admin back end',
            'admin settings' => 'Can change system and module settings',
        );
        $settings = MPModule::hook_user('mpadmin_settings_fields');
        foreach ($settings as $mod)
        {
            $perms[$mod.' settings'] = 'Can change '.$mod.' settings';
        }
        return array(
            'MPAdmin' => $perms
        );
    }

    //}}}
    //{{{ public function hook_mpsystem_active()
    public function hook_mpsystem_active()
    {
        $logging = MPData::query('MPAdmin', 'logging');
        if (is_null($logging))
        {
            MPData::update('MPAdmin', 'logging', TRUE);
        }
        $alc = MPDB::selectCollection('mpadmin_log');
        $alc->ensureIndex(array('username' => 1, 'type' => 1));

        $static_routes = array(
            '#^/admin/static/([^/]+)/(.+)/$#' => DIR_MODULE.'/${1}/admin/static/${2}',
            '#^/admin/static/([^/]+)/(.+)/$#' => DIR_MODULE.'/${1}/admin/static/${2}.php'
        );
        $static_types = array(
            'css' => 'text/css',
            'js' => 'text/javascript',
        );
        foreach ($static_routes as $pattern => $controller)
        {
            MPRouter::add($pattern, $controller, MPRouter::ROUTE_PCRE, MPRouter::PRIORITY_NORMAL, 'admin');
            if (MPRouter::pattern(TRUE) === $pattern)
            {
                // we need correct headers
                $c = MPRouter::controller(TRUE);
                $s = strrpos($c, '.');
                if ($s !== FALSE)
                {
                    $ext = substr($c, $s + 1);
                    if (array_key_exists($ext, $static_types))
                    {
                        header('MPContent-type: '.$static_types[$ext]);
                    }
                }
            }
        }
    }

    //}}}
    //{{{ public function hook_mpsystem_routes()
    public function hook_mpsystem_routes()
    {
        $ctrl = dirname(__FILE__).'/admin/controller';
        $routes = array(
            array('/admin/', $ctrl.'/index.php'),
            array('/admin/login/', $ctrl.'/login.php'),
            array('/admin/logout/', $ctrl.'/logout.php'),
            array('#^/admin/settings/[^/]+/$#', $ctrl.'/settings.php', MPRouter::ROUTE_PCRE),
            array('#^/admin/rpc/([^/]+/)+$#', $ctrl.'/rpc.php', MPRouter::ROUTE_PCRE),
            array('#^/admin/module/.+/$#', $ctrl.'/module.php', MPRouter::ROUTE_PCRE),
            array('#^/admin/mod/.+/$#', $ctrl.'/mod.php', MPRouter::ROUTE_PCRE),
        );
        return $routes;
    }

    //}}}
    //{{{ public function hook_mpsystem_start()
    public function hook_mpsystem_start()
    {
        if (MPRouter::source() === 'MPAdmin')
        {
            $redirect = (URI_PARTS > 0 && URI_PART_0 === 'admin')
                        ? (URI_PARTS === 1 || URI_PART_1 !== 'login')
                        : FALSE;

            $logged_in = MPUser::perm('admin access') && deka(FALSE, $_SESSION, 'admin', 'logged_in');

            if (!$logged_in && $redirect)
            {
                header('Location: /admin/login/');
                exit;
            }
        }
    }

    //}}}
    //{{{ public function hook_rpc($action, $params = NULL)
    /**
     * Implementation of hook_rpc
     *
     * This looks at the action and checks for the method _rpc_<action> and
     * passes the parameters to that. There is no limit on parameters.
     *
     * @param string $action action name
     * @return string
     */
    public function hook_rpc($action)
    {
        $method = '_rpc_'.$action;
        $caller = array($this, $method);
        $args = array_slice(func_get_args(), 1);
        return method_exists($this, $method) 
            ? call_user_func_array($caller, $args)
            : '';
    }

    //}}}

    //{{{ public function prep_mpadmin_login_submit($mod, $data)
    public function prep_mpadmin_login_submit($mod, $data)
    {
        if (eka($data, $mod))
        {
            return array(
                'use_method' => TRUE,
                'data' => array(
                    'data' => $data[$mod],
                    'extra' => array()
                )
            );
        }
    }

    //}}}
    //{{{ public function prep_mpadmin_module_page($mod)
    /**
     * Prepare data for the module's admin page
     * Looks into the module's /admin/ directory for the matching php template
     * file. If it is available, the hook uses the output of this file instead
     * of going into the module's hook method.
     */
    public function prep_mpadmin_module_page($mod)
    {
        $data['callback'] = URI_PARTS === 3 ? 'index' : URI_PART_3;
        $dir = dirname(dirname(__FILE__)).'/'.$mod.'/admin/';
        $ctrl = $dir.'/controller/'.$data['callback'].'.php';
        $view = $dir.'/view/'.$data['callback'].'.php';
        if (is_readable($ctrl))
        {
            ob_start();
            include $ctrl;
            if (is_readable($view))
            {
                include $view;
            }
            $data = ob_get_clean();
            $use_method = FALSE;
        }
        else
        {
            for ($i = 4; $i < URI_PARTS; ++$i)
            {
                $data['parameters'][] = constant('URI_PART_'.$i);
            }
            $use_method = TRUE;
        }
        return array(
            'use_method' => $use_method,
            'data' => $data
        );
    }

    //}}}

    //{{{ public function row_module($mod)
    /**
     * Returns the HTML to use in the module form in admin backend
     *
     * @param string $name module name
     * @param module $mod
     * @return string
     */
    public function row_module($name, $mod)
    {
        $name = strlen($name)
            ? ucwords($name)
            : '';
        $meta = MPModule::meta($name);
        $description = strlen($meta['description'])
            ? ' - '.$meta['description'].'. '
            : '. ';
        if (strlen($meta['author']))
        {
            $byline = strlen($meta['website'])
                ? '<span class="byline">By <a href="'.$meta['website'].'">'.$meta['author'].'</a></span>. '
                : '<span class="byline">By '.$meta['author'].'</span>. ';
        }
        else
        {
            $byline = strlen($meta['website'])
                ? '<span class="byline"><a href="'.$meta['website'].'">'.$meta['website'].'</a></span>. '
                : '';
        }
        $dependency = count($meta['dependency'])
            ? '<span class="dependency">Dependencies: <em>'.implode(', ', $meta['dependency']).'</em></span>.'
            : '';
        return $name.$description.$byline.$dependency;
    }

    //}}}

    //{{{ public static function append($name, $value)
    /**
     * Appends an array for use in the admin templates
     *
     * @param string $name variable name
     * @param mixed $value value to store
     * @return void
     */
    public static function append($name, $value)
    {
        self::$v[$name][] = $value;
    }

    //}}}
    //{{{ public static function bounce($permission)
    /**
     * Checks current user if they have permission. Sets denied message if not.
     *
     * @param string $permission
     * @return boolean
     */
    public static function bounce($permission)
    {
    }
    //}}}
    //{{{ public static function get($name, $default = NULL)
    /**
     * Gets a variable for use in the admin templates
     *
     * @param string $name variable name
     * @param mixed $default default value if doesn't exist
     * @return mixed, null if name doesn't exist
     */
    public static function get($name, $default = NULL)
    {
        return isset(self::$v[$name]) ? self::$v[$name] : $default;
    }

    //}}}
    //{{{ public static function log($type, $messages)
    public static function log($type, $messages)
    {
        $logging = MPData::query('MPAdmin', 'logging');
        if (!is_null($logging) && $logging)
        {
            if (is_string($messages))
            {
                $messages = array($messages);
            }
            $log = array(
                'user' => MPUser::i('name'),
                'type' => $type,
                'messages' => $messages,
            );
            $alc = MPDB::selectCollection('mpadmin_log');
            $alc->insert($log);
        }
    }
    
    //}}}
    //{{{ public static function notify($type, $messages)
    public static function notify($type, $messages)
    {
        if (is_string($messages))
        {
            $messages = array($messages);
        }
        switch ($type)
        {
            case self::TYPE_NOTICE:
                $_SESSION['admin']['messages']['notice'] = $messages;
            break;
            case self::TYPE_SUCCESS:
                $_SESSION['admin']['messages']['success'] = $messages;
            break;
            case self::TYPE_ERROR:
                $_SESSION['admin']['messages']['error'] = $messages;
            break;
            case self::TYPE_IMPORTANT:
                $_SESSION['admin']['messages']['important'] = $messages;
            break;
        }
    }
    
    //}}}
    //{{{ public static function quick_form($form)
    /**
     * Used for consistent admin backend forms and ease of use
     *
     * @param array $form array of data to be looped through and thrown into a form
     */
    public static function quick_form($form)
    {
        // $form['action'];
        // $form['method'];
        // $form['enctype'];
        foreach ($form['groups'] as $glabel => $group)
        {
            if (!is_numeric($glabel))
            {
                // group gets label
            }
            if (ake('type', $group))
            {
                switch ($group['type'])
                {
                    case 'hidden':
                        // set class hidden
                    break;
                    case 'tabbed':
                        // set class tabbed
                    break;
                }
            }
            foreach ($group['fields'] as $flabel => $field)
            {
                if (!is_numeric($flabel))
                {
                    // field gets label
                }
                if (ake('description', $field))
                {
                    // set description
                }
                if (ake('multiple', $field))
                {
                    // set multiple attribute
                }
                if (ake('options', $field))
                {
                    // set options
                }
                if (ake('value', $field))
                {
                    // set value 
                }
                // $field['name'];
                // $field['type'];
            }
        }
        // add or edit form?
        if ($form['type'] === 'edit')
        {
            // make submit_reset_delete buttons
        }
        else
        {
            // make submit_reset buttons
        }
        // return form builder html?
    }

    //}}}
    //{{{ public static function set($name, $value)
    /**
     * Sets a variable for use in the admin templates
     *
     * @param string $name variable name
     * @param mixed $value value to store
     * @return void
     */
    public static function set($name, $value)
    {
        self::$v[$name] = $value;
    }

    //}}}
}
