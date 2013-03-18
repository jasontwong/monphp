<?php

/**
 * Class that manages all configurations and settings from database
 * Aside from a few hard coded configurations and constants, everything else
 * will live in the data table and managed by this class.
 * @package data
 */
class MPData
{
    //{{{ properties
    /**
     * Associative array of all data table rows
     */
    public static $data = NULL;
    public static $id = NULL;
    public static $autoload = NULL;
    /**
     * updates
     */
    private static $updates;
    /**
     * new data
     */
    private static $adds;
    /**
     * Flag notifying data has been changed and needs updating on cleanup
     */
    private static $changed = FALSE;
    //}}}
    //{{{ private static function lookup($type, $name = NULL)
    /**
     */
    public static function lookup($type, $name = NULL)
    {
        try
        {
            $query = array('type' => $type);
            if (!is_null($name))
            {
                $query['name'] = $name;
            }
            $cursor = MPDB::selectCollection('mpsystem.data')->find($query);
            return $cursor->hasNext() ? $cursor : NULL;
        }
        catch (Exception $e)
        {
            return NULL;
        }
    }
    //}}}
    //{{{ private static function register($row)
    private static function register($row)
    {
        $type = &$row['type'];
        $name = &$row['name'];
        self::$data[$type][$name] = $row['data'];
        self::$id[$type][$name] = $row['_id']->{'$id'};
        self::$autoload[$type][$name] = $row['autoload'];
    }
    //}}}
    //{{{ public static function exists()
    /**
     * Not type strict
     */
    public static function exists()
    {
        self::init();
        $args = func_get_args();
        if (count($args) < 3)
        {
            return FALSE;
        }
        $value = array_slice($args, -1);
        $result = array_drill(self::$data, $args);
        if (is_null($result))
        {
            try
            {
                $query = array(
                    'type' => $args[0],
                    'name' => $args[1]
                );
                $key = 'data.' . implode('.', array_slice($args, 2, 1));
                $query[$key] = $value;
                $find = MPDB::selectCollection('mpsystem.data')->find($query);
                return !is_null($find);
            }
            catch (Exception $e)
            {
                return FALSE;
            }
        }
        else
        {
            if (is_array($result))
            {
                return in_array($value, $result);
            }
            else
            {
                return $result == $value;
            }
        }
    }
    //}}}
    //{{{ public static function init()
    /**
     * Gets all data entries from database
     *
     * @return void
     */
    public static function init()
    {
        if (is_null(self::$data))
        {
            self::$data = array();
            self::$id = array();
            self::$autoload = array();
            self::$updates = array();
            self::$adds = array();
            try
            {
                $rows = MPDB::selectCollection('mpsystem.data')
                    ->find(array(
                        'autoload' => true
                    ));
                foreach ($rows as $row)
                {
                    self::register($row);
                }
            }
            catch (Exception $e)
            {
            }
        }
    }
    //}}}
    //{{{ public static function names($type)
    /**
     * Looks into self::$data for name columns
     *
     * @return array or null if type doesn't exist
     */
    public static function names($type)
    {
        self::init();
        return eka(self::$data, $type)
            ? array_keys(self::$data[$type])
            : NULL;
    }
    //}}}
    //{{{ public static function query()
    /**
     * Looks into self::$data for information
     * You can drill down into the $data array, with each parameter getting
     * more specific. If no parameters are specified, entire array is 
     * returned. Get too specific where key doesn't exist, it returns NULL.
     *
     * @return mixed or null if doesn't exist
     */
    public static function query()
    {
        self::init();
        $args = func_get_args();
        $result = array_drill(self::$data, $args);
        if (is_null($result))
        {
            $result = call_user_func_array(array('MPData', 'lookup'), $args);
            if (!is_null($result))
            {
                foreach ($result as $row)
                {
                    self::register($row);
                }
            }
            return array_drill(self::$data, $args);
        }
        else
        {
            return $result;
        }
    }
    //}}}
    //{{{ public static function save()
    /**
     * Records updates and additions
     */
    public static function save()
    {
        self::init();
        if (self::$changed)
        {
            $sdc = MPDB::selectCollection('mpsystem.data');
            if (count(self::$updates))
            {
                $ids = array_keys(self::$updates);
                foreach (self::$updates as $id => $fp)
                {
                    $data = array(
                        'data' => self::query($fp['type'], $fp['name']),
                        'autoload' => self::$autoload[$fp['type']][$fp['name']],
                    );
                    $_id = new MongoId($id); 
                    $sdc->update(array('_id' => $_id), array('$set' => $data));
                }
            }
            foreach (self::$adds as $add)
            {
                $add['data'] = self::query($add['type'], $add['name']);
                $add['autoload'] = self::$autoload[$add['type']][$add['name']];
                $sdc->insert($add);
            }
            self::$updates = array();
            self::$adds = array();
            self::$changed = FALSE;
        }
    }
    //}}}
    //{{{ public static function settings_form()
    /**
     */
    public static function settings_form()
    {
        $data = self::query('_Site');
        $rows = array(
            array(
                'field' => MPField::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Site Title'
                        )
                    )
                ),
                'name' => 'title',
                'type' => 'text',
                'value' => array(
                    'data' => $data['title']
                )
            ),
            array(
                'field' => MPField::layout(
                    'text',
                    array(
                        'data' => array(
                            'label' => 'Site Description'
                        )
                    )
                ),
                'name' => 'description',
                'type' => 'text',
                'value' => array(
                    'data' => $data['description']
                )
            ),
            array(
                'field' => MPField::layout(
                    'dropdown_timezones',
                    array(
                        'data' => array(
                            'label' => 'Time Zone'
                        )
                    )
                ),
                'name' => 'time_zone',
                'type' => 'dropdown_timezones',
                'value' => array(
                    'data' => $data['time_zone']
                )
            )
        );

        return $rows;
    }
    //}}}
//{{{ public static function update($type, $name, $data, $autoload = NULL)
/**
* @param string $type module name or system type name
* @param string $name name of setting or variable
* @param mixed $data PHP native data payload
* @param boolean $autoload auto loading flag
* @return bool true if all checks passed 
*/
public static function update($type, $name, $data, $autoload = FALSE)
{
    self::init();
    self::$changed = TRUE;
    $signature = array('type' => $type, 'name' => $name);
    if (eka(self::$data, $type, $name))
    {
        self::$updates[self::$id[$type][$name]] = $signature;
    }
    else
    {
        self::$adds[] = $signature;
    }
    self::$autoload[$type][$name] = (bool)$autoload;
    self::$data[$type][$name] = $data;
}
//}}}
}
