<?php

class MPExtension
{
    //{{{ properties
    private static $files = NULL;
    private static $extensions = NULL;
    private static $names = NULL;

    //}}}
    //{{{ public static function get_type($type)
    /**
     * Get all extension object references based on type
     * @param string $type type name
     * @return array of object references
     */
    public static function get_type($type)
    {
        self::load();
        return deka(array(), self::$extensions, strtolower($type));
    }

    //}}}
    //{{{ private static function load()
    /**
     * Loads extensions
     */
    private static function load()
    {
        self::find_extensions();
        if (is_null(self::$extensions))
        {
            foreach (self::$files as $file)
            {
                include $file;
                $filename = basename($file);
                $class = 'MPExtension'.substr($filename,0,strpos($filename,'.'));
                // This function is not case sensitive
                if (class_exists($class))
                {
                    $ext = new $class;
                    $type = $ext->type;
                    $name = strtolower($class);
                    self::$extensions[$type][$name] = $ext;
                    self::$names[$name] = &self::$extensions[$type][$name];
                }
            }
        }
    }

    //}}}
    //{{{ private static function find_extensions()
    private static function find_extensions()
    {
        if (is_null(self::$files))
        {
            self::$files = array();
            $files = scandir(DIR_EXT);
            foreach ($files as $file)
            {
                $ext = DIR_EXT.'/'.$file;
                if (is_file($ext) && strpos($file, '.') !== 0)
                {
                    self::$files[] = $ext;
                }
            }
        }
        return self::$files;
    }

    //}}}
}
