<?php

/**
 * Manager of the field layout
 *
 * @package MPField
 */
class MPField
{
    //{{{ constants
    const ELEMENT_BUTTON = 0;
    const ELEMENT_INPUT = 1;
    const ELEMENT_SELECT = 2;
    const ELEMENT_TEXTAREA = 3;

    //}}}
    //{{{ properties
    /**
     * Associative array of publicly available field types
     */
    public static $public_types = NULL;
    public static $public_types_options = NULL;
    private $layout;

    /**
     * Stores result set of actions performed by acts()
     */
    private $actions = array();
    private $errors = array();
    /**
     * Flag to note if field_ methods were scanned
     */
    protected static $scanned = FALSE;
    protected static $mapper = array();

    //}}}
    //{{{ public function __construct($layout = array())
    public function __construct($layout = array())
    {
        $this->layout = $layout;
    }
    //}}}
    //{{{ public function __call($name, $args = array())
    public function __call($name, $args = array())
    {
        if (!empty($args))
        {
            $action = $name;
            $data = $args;
            return call_user_func_array(array($this, 'acts'), array($action, $data));
        }
        else
        {
            throw new Exception('Call to undefined method');
        }
    }
    //}}}
    //{{{ public static function __callStatic($name, $args)
    // This is a php 5.3.0 > function
    public static function __callStatic($name, $args)
    {
        if (!empty($args))
        {
            $action = $name;
            $key = $args[0];
            $data = deka(array(), $args, 1);
            return call_user_func_array(array('MPField', 'action'), array($action, $key, $data));
        }
        else
        {
            throw new Exception('Call to undefined method');
        }
    }
    //}}}
    //{{{ public function act($action, $key, $data)
    public function act($action, $key, $data)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'action'), $args);
    }

    //}}}
    //{{{ public function acts($action, $data)
    /**
     * Performs an action of a field type in an array loop
     *
     * @param string $action method to call: field_{$action}_
     * @param mixed $data data
     * @return array
     */
    public function acts($action, $data)
    {
        $key = $action.'-'.sha1(serialize($data));
        if (eka($this->actions, $key))
        {
            return $this->actions[$key];
        }
        else
        {
            $result = array();
            $args = func_get_args();
            $args = array_slice($args, 2);
            $caller = array($this, 'act');
            foreach ($data as $k => $row)
            {
                try
                {
                    $aargs = $args 
                        ? array($action, $k, $row, $args)
                        : array($action, $k, $row);
                    $result[$k] = call_user_func_array($caller, $aargs);
                }
                catch (MPFieldErrorException $e)
                {
                    $this->errors[$key][$k] = $e->get_errors();
                }
            }
            $this->actions[$key] = $result;
            return $result;
        }
    }

    //}}}
    //{{{ public function add($layout, $key = NULL)
    public function add($layout, $key = NULL)
    {
        $this->add_layout($layout, $key);
    }
    //}}}
    //{{{ public function add_layout($layout, $key = NULL)
    public function add_layout($layout, $key = NULL)
    {
        if (!is_null($key))
        {
            $this->layout[$key] = $layout;
        }
        elseif (eka($layout, 'name'))
        {
            $this->layout[$layout['name']] = $layout;
        }
    }
    //}}}
    //{{{ public function get_layout($key)
    public function get_layout($key)
    {
        return deka(NULL, $this->layout, $key);
    }
    //}}}
    //{{{ public function has_layout($key)
    public function has_layout($key)
    {
        return eka($this->layout, $key);
    }
    //}}}
    //{{{ public function merge($data, $key = 'value')
    /**
     * Incorporates the data into the layout's value array.
     * It does not replace the entire array but only the keys of $data.
     * @param array $data
     * @param string $key
     * @return void
     */
    public function merge($data, $key = 'value')
    {
        foreach ($data as $type => $v)
        {
            $this->layout[$type][$key] = $v;
        }
    }
    //}}}
    //{{{ public static function action_array($action, $type)
    public static function action_array($action, $type)
    {
        $class = deka(NULL, self::$mapper, $action, $type);
        $method = 'field_'.$action.'_'.$type;
        return $class && method_exists($class, $method)
            ? array($class, $method)
            : NULL;
    }
    //}}}
    //{{{ public static function quick_act($action, $type, $data)
    public static function quick_act($action, $type, $data = array())
    {
        $field = new MPField();
        $field->add_layout(
            array(
                'field' => MPField::layout($type),
                'name' => 'quickact',
                'type' => $type
            )
        );
        return $field->act($action, 'quickact', $data);
    }
    //}}}
    //{{{ public static function layout($type, $data = array())
    public static function layout($type, $data = array())
    {
        self::scan();
        $class = deka(FALSE, self::$mapper, 'layout', $type);
        $method = 'field_layout_'.$type;
        if ($class && method_exists($class, $method))
        {
            $layout = call_user_func_array(array($class, $method), array($data));
            if (is_array($data) && $data)
            {
                foreach ($data as $type => $arrays)
                {
                    foreach ($arrays as $k => $v)
                    {
                        $layout[$type][$k] = $v;
                    }
                }
            }
            return $layout;
        }
        else
        {
            throw new Exception('Layout for type "'.$type.'" does not exist.');
        }
    }
    //}}}
    //{{{ public static function scan()
    /**
     * Scans modules for field_ methods and records them
     * @return void
     */
    public static function scan()
    {
        if (!self::$scanned)
        {
            $extensions = MPExtension::get_type('field');
            $modules = array();
            foreach (MPModule::active_names() as $mod)
            {
                $cffile = DIR_MODULE.'/'.$mod.'/field.php';
                if (is_file($cffile))
                {
                    include $cffile;
                    $cfclass = $mod.'MPField';
                    if (class_exists($cfclass))
                    {
                        $modules[] = new $cfclass;
                    }
                }
            }
            $classes = array_merge($extensions, $modules);
            foreach ($classes as $class)
            {
                $methods = get_class_methods($class);
                foreach ($methods as $method)
                {
                    $parts = explode('_', $method, 3);
                    if (count($parts) === 3 && $parts[0] === 'field')
                    {
                        $action = $parts[1];
                        $type = $parts[2];
                        self::$mapper[$action][$type] = get_class($class);
                    }
                }
            }
            self::$scanned = TRUE;
        }
    }

    //}}}
    //{{{ public static function types()
    /**
     * Gets the public types by looking at the field_public_ methods
     * used to indicate that this field is to be publicly used
     *
     * @return array of public types
     */
    public static function types()
    {
        self::scan();
        if (is_null(self::$public_types))
        {
            self::$public_types = array();
            if (eka(self::$mapper, 'public'))
            {
                foreach (self::$mapper['public'] as $type => $class)
                {
                    self::$public_types[$type] = call_user_func(array($class, 'field_public_'.$type));
                }
            }
        }
        return self::$public_types;
    }

    //}}}
    //{{{ public static function type_options()
    /**
     * Gets public types to use in a field requiring an 'options' array
     * @return array of public types
     */
    public static function type_options()
    {
        self::scan();
        if (is_null(self::$public_types_options))
        {
            $options = self::types();
            foreach ($options as &$option)
            {
                $option = $option['name'];
            }
            asort($options);
            self::$public_types_options = $options;
        }
        return self::$public_types_options;
    }

    //}}}
    //{{{ protected static function data_type($data)
    /**
     * Gets the type based on $data for act and acts methods
     * @param string|array $data
     * @return string|FALSE
     */
    protected static function data_type($data)
    {
        if (is_string($data))
        {
            return $data;
        }
        elseif (is_array($data) && isset($data['type']))
        {
            return $data['type'];
        }
        else
        {
            return FALSE;
        }
    }

    //}}}
    //{{{ protected static function name_value(&$extra, $params)
    /**
     * Shortcut method to add the name and value attributes into the array
     * Just pass func_get_args() for $params and it will assume the first
     * and second value are the name and value strings respectively.
     *
     * @param array &$extra the extra info array to alter
     * @param array $params field_type_ method parameters
     * @return array
     */
    protected static function name_value(&$extra, $params)
    {
        $extra['attr']['name'] = $params[0];
        $extra['attr']['value'] = $params[1];
        return $extra;
    }

    //}}}
    //{{{ protected function action($action, $key, $data)
    /**
     * Performs an action of a field type
     * Parameters after the first will be taken as is and passed to the class
     * and method based on self::$fields. But to get the type data it looks at
     * the second parameter. If it is a string it uses that. If it's an array,
     * it looks in the 'type' key. If the second parameter is a string, it
     * assumes the rest of the parameters are the ones to pass, since it is
     * silly to pass the field type when it is specified in the method. BUT it
     * will be passed if it goes to the fallback method.
     *
     * @param string $action method to call: field_{$action}_
     * @param mixed $data additional data
     * @return mixed
     */
    protected function action($action, $key, $data)
    {
        self::scan();
        if (!($type = $this->key_type($key)))
        {
            return NULL;
        }

        $args = array_slice(func_get_args(), 1);

        if ($prep_caller = $this->action_array('prepare', $action))
        {
            $args = call_user_func_array($prep_caller, $args);
        }

        if ($caller = $this->action_array($action, $type))
        {
            $results = call_user_func_array($caller, $args);
        }
        elseif ($caller = $this->action_array('fallback', $action))
        {
            $results = call_user_func_array($caller, $args);
        }
        else
        {
            $results = $args;
        }

        $caller = $this->action_array('conclude', $action);
        return $caller ? call_user_func($caller, $results, $args) : $results;
    }

    //}}}
    //{{{ protected function key_type($key)
    /**
     * Gets the type based on $layout key
     * @param string $key
     * @return string
     */
    protected function key_type($key)
    {
        return deka(NULL, $this->layout, $key, 'type');
    }

    //}}}
}

class MPFieldErrorException extends Exception
{
    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct($errors);
    }

    public function get_errors()
    {
        return $this->errors;
    }
}
