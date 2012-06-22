<?php

/**
 * module manager class
 * The module class handles every aspect of module management including 
 * checking, installing, and searching. The search functions are mainly for
 * hooks which are sent to the hook manager class for activation. The hooks
 * are registered with the module manager but are called by the hook manager.
 *
 * @package MPModule
 */

class MPModule
{
    //{{{ properties
    /**
     * References of all modules available to the system
     * This is only populated if the find_available() method is called,
     * usually done in a module config page.
     */
    static protected $available = array();
    /**
     * MPModule meta information (author, description, etc.)
     */
    static protected $meta = array();
    /**
     * Searched for all available modules
     */
    static protected $searched = FALSE;
    /**
     * References to activated modules
     */
    static protected $active = array();
    /**
     * References to activated modules' field class
     */
    static protected $fields = array();
    /**
     * Collection of hooks for active modules
     */
    static protected $hook;

    //}}}
    //{{{ constants
    /**
     * MPModules are loaded to check if they are valid
     */
    const STATE_VALID = 0;
    /**
     * MPModules are loaded during the install phase
     */
    const STATE_INSTALL = 1;
    /**
     * MPModules are loaded after system has been installed (or most of it)
     */
    const STATE_ACTIVE = 2;
    /**
     * No filter for modules
     */
    const TARGET_ALL = 0;
    //}}}
    //{{{ protected function callbacks($module)
    /**
     * Get cb_hook callback methods for a module
     * A module can register a hook by having a callback method for the class.
     * So if a module has the method cb_mpuser_perm, the hook 'user_perm' is
     * now registered by that module. If a hook is to be registered but does
     * not need to do anything, the cb_ method is still needed.
     *
     * @param string|module $module
     * @return array of callbacks sans cb_
     */
    protected function callbacks($module)
    {
        $methods = get_class_methods($module);
        $callbacks = array();
        foreach ($methods as $method)
        {
            if (substr($method, 0, 3) === 'cb_')
            {
                $callbacks[] = substr($method, 3);
            }
        }
        return $callbacks;
    }
    //}}}
    //{{{ protected function load($mod_file)
    /**
     * Goes through the module directory to find all available modules
     * @return void 
     */
    protected function load($mod, $mod_file)
    {
        if (is_readable($mod_file))
        {
            include_once $mod_file;
            $module = new $mod;
            self::$active[$mod] = $module;
            self::$meta[$mod] = self::load_meta($mod);
            $hooks = self::callbacks($module);
            foreach ($hooks as $h)
            {
                self::$hook[$h] = &self::$active[$mod];
            }
        }
    }
    //}}}
    //{{{ protected function load_available()
    /**
     * Goes through the module directory to find all available modules
     * @return array
     */
    protected function load_available()
    {
        if (!self::$searched)
        {
            if ($modules = scandir(DIR_MODULE))
            {
                foreach ($modules as $module)
                {
                    $file = DIR_MODULE.'/'.$module.'/module.php';
                    if (is_readable($file))
                    {
                        include_once $file;
                        self::$available[$module] = new $module;
                        self::$meta[$module] = self::load_meta($module);
                    }
                }
            }
            self::$searched = TRUE;
        }
        return self::$available;
    }
    //}}}
    //{{{ protected static function hook_owner($hook)
    /**
     * Get reference to module that registered the hook
     *
     * @param string $hook hook name
     * @return mixed reference to module object or FALSE
     */
    protected static function hook_owner($hook)
    {
        return isset(self::$hook[$hook]) ? self::$hook[$hook] : FALSE;
    }

    //}}}
    //{{{ public static function active($target = MPModule::TARGET_ALL)
    /**
     * Get active module references
     *
     * @param mixed $target module name or MPModule::TARGET_ALL to get all
     * @return array of module references
     */
    public static function active($target = MPModule::TARGET_ALL)
    {
        if ($target === MPModule::TARGET_ALL)
        {
            return self::$active;
        }
        else
        {
            return ake($target, self::$active) 
                ? array($target => self::$active[$target])
                : array();
        }
    }

    //}}}
    //{{{ public static function active_names()
    /**
     * Get active module names
     *
     * @return array of module names
     */
    public static function active_names()
    {
        return array_keys(self::$active);
    }

    //}}}
    //{{{ public static function available($target = MPModule::TARGET_ALL)
    /**
     * Get all available modules
     *
     * @param mixed $target module name or MPModule::TARGET_ALL for all
     * @return array
     */
    public static function available($target = MPModule::TARGET_ALL)
    {
        self::load_available();
        if ($target === MPModule::TARGET_ALL)
        {
            return self::$available;
        }
        else
        {
            return isset(self::$available[$target]) 
                ? array($target => self::$available[$target])
                : array();
        }
    }

    //}}}
    //{{{ public static function check_dependency($modules)
    /**
     * Checks to make sure the modules include all dependent modules
     * The modules are passed because this is still used in the install
     * procedure where they are not technically installed and active yet.
     *
     * @param array $modules array of module names
     * @return boolean
     */
    public static function check_dependency($modules)
    {
        foreach ($modules as $mod)
        {
            $m = self::available($mod);
            $constant_name = $mod.'::MODULE_DEPENDENCY';
            if (defined($constant_name))
            {
                $deps = array_clean(explode(',', constant($constant_name)));
                if (array_intersect($deps, $modules) !== $deps)
                {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    //}}}
    //{{{ public static function h($hook, $target = MPModule::TARGET_ALL)
    /**
     * The meat of the module system, in this method.
     * This has three major steps. The first is the preparation step which is
     * passed to 'prep_'.$hook method of a module (the first to be detected
     * with this method. This method will be called repeatedly with the same
     * data parameters along with the module name the data will be passed to,
     * giving the module a chance to section out or alter the data for the 
     * module. So the admin_login hook can have just the user module's POST
     * data prepared and sent, even though all modules participating in the
     * hook will contribute to the overall data.
     *
     * The second step is the hook itself for the participating module. The
     * participating module can return a value for further use by the module
     * with the 'cb_'.$hook method. It is up to this module to define the
     * format of the return value.
     *
     * The third step is the callback 'cb_'.$hook method. This is optional and
     * is fine if no module has this method.
     * 
     * The idea behind name collision management is that the
     * hooks will start with the module name. So the admin module will have
     * prep_mpadmin_dashboard and cb_mpadmin_dashboard.
     *
     * @param string $hook hook name
     * @param int|string target constant of module name to call the hook on
     * @return mixed
     */
    public static function h($hook, $target = MPModule::TARGET_ALL)
    {
        self::load_active();
        $method = 'hook_'.$hook;
        $callback = 'cb_'.$hook;
        $prep = 'prep_'.$hook;
        $args = array_slice(func_get_args(), 2);

        $mod = self::hook_owner($hook);
        $can_prep = method_exists($mod, $prep);
        $can_cb = method_exists($mod, $callback);
        $param = array();
    
        $modules = self::hook_user($hook, $target);
        MPCache::remove_by_hook($hook);
        foreach ($modules as $name => $module)
        {
            if ($can_prep)
            {
                $caller = array($mod, $prep);
                $params = array_merge(array($name), $args);
                $results = call_user_func_array($caller, $params);
                $use_method = $results['use_method'];
                $data = $results['data'];
            }
            else
            {
                $use_method = TRUE;
                $data = $args;
            }
            $param[$name] = $use_method 
                ? call_user_func_array(array($module, $method), $data)
                : $data;
        }
        $param = is_int($target) || !ake($target, $param) 
            ? $param 
            : array($target => $param[$target]);
        return $can_cb 
            ? call_user_func_array(array($mod, $callback), array($param))
            : $param;
    }

    //}}}
    //{{{ public static function hook_user($hook, $target = MPModule::TARGET_ALL)
    /**
     * Get references of modules using a hook in an array
     *
     * @param string $hook hook name
     * @param mixed $target module name or MPModule::TARGET_ALL to get all
     * @return array
     */
    public static function hook_user($hook, $target = MPModule::TARGET_ALL)
    {
        $mods = self::active($target);
        $result = array();
        foreach ($mods as $name => &$mod)
        {
            if (method_exists($mod, 'hook_'.$hook))
            {
                $result[$name] = &$mod;
            }
        }
        return $result;
    }

    //}}}
    //{{{ public static function install($target = MPModule::TARGET_ALL)
    public static function install($target = MPModule::TARGET_ALL)
    {
        self::load_active();
        if ($target === MPModule::TARGET_ALL)
        {
            foreach (self::$active as $name => $mod)
            {
                self::h('install', $name);
            }
        }
        elseif (isset(self::$active[$target]))
        {
            self::h('install', $target);
        }
    }

    //}}}
    //{{{ public static function is_active($module)
    /**
     * Check a module is currently active
     *
     * @param string $module module name
     * @return boolean
     */
    public static function is_active($module)
    {
        return in_array($module, self::active_names());
    }

    //}}}
    //{{{ public static function load_meta($module)
    /**
     * Get the module's meta information as an assoc array
     * @param string $module module name
     * @return array
     */
    public static function load_meta($module)
    {
        if (!class_exists($module))
        {
            $file = DIR_MODULE.'/'.$module.'/module.php';
            if (is_readable($file))
            {
                include_once $file;
            }
        }
        $meta = array(
            'author' => '', 
            'dependency' => array(), 
            'description' => '', 
            'website' => ''
        );
        if (class_exists($module))
        {
            $ca = $module.'::MODULE_AUTHOR';
            $cdp = $module.'::MODULE_DEPENDENCY';
            $cde = $module.'::MODULE_DESCRIPTION';
            $cw = $module.'::MODULE_WEBSITE';
            if (defined($ca))
            {
                $meta['author'] = constant($ca);
            }
            if (defined($cdp))
            {
                $meta['dependency'] = explode(',', constant($cdp));
                foreach ($meta['dependency'] as $k => $v)
                {
                    if (empty($k))
                    {
                        unset($meta['dependency'][$k]);
                    }
                }
            }
            if (defined($cde))
            {
                $meta['description'] = constant($cde);
            }
            if (defined($cw))
            {
                $meta['website'] = constant($cw);
            }
        }
        return $meta;
    }

    //}}}
    //{{{ public static function load_active()
    /**
     * Builds $active array with references to activated modules
     * Also records module directories and registers hooks in this step.
     * Looks through all method names and registers field types based on
     * method name format.
     *
     * @return void
     */
    public static function load_active($force = FALSE)
    {
        if ($force || self::$active === array())
        {
            // load system module
            self::load('MPSystem', DIR_SYS . '/classes/MPSystem.php');
            if ($mods = MPData::query('_System', 'modules'))
            {
                foreach ($mods as $mod)
                {
                    self::load($mod, DIR_MODULE . '/' . $mod . '/module.php');
                }
            }
        }
    }

    //}}}
    //{{{ public static function meta($module, $key = NULL)
    /**
     * Get the module's stored meta information as an assoc array
     * @param string $module module name
     * @param string $key optional key (author, description, dependency, website)
     * @return array|string
     */
    public static function meta($module, $key = NULL)
    {
        if (!eka(self::$meta, $module))
        {
            self::$meta[$module] = self::load_meta($module);
        }
        return !is_null($key) && eka(self::$meta, $module, $key) 
            ? self::$meta[$module][$key]
            : self::$meta[$module];
    }

    //}}}
    //{{{ public static function uninstall($target)
    public static function uninstall($target)
    {
        self::h('uninstall', $target);
    }

    //}}}
}
