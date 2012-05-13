<?php

//{{{ function ake($key, $array)
/**
 * Shortcut for array_key_exists()
 */
function ake($key, $array)
{
    return is_array($array) && array_key_exists($key, $array);
}

//}}}
//{{{ function array_clean($array)
/**
 * Goes one level into array and removes empty elements
 */
function array_clean($array)
{
    foreach ($array as $k => $v)
    {
        if (empty($v))
        {
            unset($array[$k]);
        }
    }
    return $array;
}

//}}}
//{{{ function array_drill($array, $keys)
/**
 * Drills down the array into the keys provided
 * @param array $array
 * @param string|array $key,... optional keys to drill down to, or array of keys
 * @return mixed|null null if key doesn't exist
 */
function array_drill($array)
{
    $keys = array_slice(func_get_args(), 1);
    if (is_array($keys[0]))
    {
        $kc = count($keys[0]);
        for ($i = 0; $i < $kc; ++$i)
        {
            $param = $keys[0][$i];
            if (array_key_exists($param, $array))
            {
                $array = $array[$param];
            }
            else
            {
                return NULL;
            }
        }
    }
    else
    {
        foreach ($keys as $key)
        {
            if (array_key_exists($key, $array))
            {
                $array = $array[$key];
            }
            else
            {
                return NULL;
            }
        }
    }
    return $array;
}

//}}}
//{{{ function array_join($master)
/**
 * This is similar to array_merge with the exception that
 * only the keys of the master array will be returned
 */
function array_join($master)
{
    return array_intersect_key(call_user_func_array('array_merge', func_get_args()), $master);
}

//}}}
// {{{ function array_to_xml($root_element_name, $array)
/**
 * Return associative array as XML string
 *
 * @param string $root_element_name     top level xml name
 * @param string $array                 associative array needed to turned into XML
 */
function array_to_xml($root_element_name, $array)
{
    $xml = new SimpleXMLElement("<{$root_element_name}></{$root_element_name}>");
    $f = create_function('$f,$c,$a','
            foreach ($a as $k => $v) 
            {
                if (is_array($v)) 
                {
                    $ch = $c->addChild($k);
                    $f($f,$ch,$v);
                } 
                else 
                {
                    $c->addChild($k,$v);
                }
            }');
    $f($f,$xml,$array);
    return $xml->asXML();
} 
// }}}
//{{{ function available_filename($filename)
/**
 * Get an available filename
 * If the filename is taken, it appends a _n before the extension, with n
 * being the index starting at 0.
 * @param string $filename full path to the file to check
 * @return string filename including path
 */
function available_filename($filename)
{
    list($name, $ext) = file_extension($filename);
    $path = dirname($filename);
    if (is_dir($path))
    {
        if (is_file($filename))
        {
            $i = 0;
            while (TRUE)
            {
                $try = $path.'/'.$name.'-'.$i++.$ext;
                if (!is_file($try))
                {
                    return $try;
                }
            }
        }
        else
        {
            return $filename;
        }
    }
    return FALSE;
}

//}}}
//{{{ function deka($default = NULL)
/**
 * Works like eka() but with default value set as first parameter
 * Use this as a shortcut of a ternary. Example:
 *
 *      $foo = isset($bar['baz']) ? $bar['baz'] : 'default';
 *      can become:
 *      $foo = deka('default', $bar, 'baz');
 */
function deka($default = NULL)
{
    $args = array_slice(func_get_args(), 1);
    $data = $default;
    if (!empty($args) && call_user_func_array('eka', $args))
    {
        $data = array_shift($args);
        if (!empty($args))
        {
            foreach ($args as $arg)
            {
                if (array_key_exists($arg, $data))
                {
                    $data = $data[$arg];
                }
                else
                {
                    return $default;
                }
            }
        }
    }
    return $data;
}

//}}}
//{{{ function dir_copy($src, $dest, $inclusive = TRUE, $chmod = 0777)
/**
 * If the parameter $inclusive = TRUE, the folder specified in $src will be 
 * copied to the directory. So if source is /usr and dest is /home, you will
 * end up with /home/usr.
 */
function dir_copy($src, $dest, $inclusive = TRUE, $chmod = 0777)
{
    if (is_dir($src))
    {
        $dest_folder = $inclusive ? $dest.'/'.basename($src) : $dest;
        if (!is_dir($dest_folder))
        {
            if (mkdir($dest_folder, $chmod, TRUE))
            {
                chmod($dest_folder, $chmod);
            }
            else
            {
                return FALSE;
            }
        }
        $files = scandir($src);
        foreach ($files as $file)
        {
            if ($file !== '.' && $file !== '..')
            {
                $new_src = $src.'/'.$file;
                $new_dest = $dest_folder.'/'.$file;
                if (is_file($new_src))
                {
                    copy($new_src, $new_dest);
                    chmod($new_dest, $chmod);
                }
                elseif (is_dir($new_src))
                {
                    if (!dir_copy($new_src, $new_dest, $inclusive, $chmod))
                    {
                        return FALSE;
                    }
                }
            }
        }
    }
    else
    {
        return FALSE;
    }
    return TRUE;
}

//}}}
//{{{ function eka($array)
/**
 * Works like array_key_exists() but with array name first then multiple keys
 * Letters reversed because the parameters are sort of reversed to ake()
 */
function eka($array)
{
    if (is_array($array))
    {
        $params = array_slice(func_get_args(), 1);
        if (is_array($params[0]))
        {
            $pc = count($params[0]);
            for ($i = 0; $i < $pc; ++$i)
            {
                if (array_key_exists($params[0][$i], $array))
                {
                    $array = $array[$params[0][$i]];
                }
                else
                {
                    return FALSE;
                }
            }
            return TRUE;
        }
        else
        {
            foreach ($params as $param)
            {
                if (array_key_exists($param, $array))
                {
                    $array = $array[$param];
                }
                else
                {
                    return FALSE;
                }
            }
            return TRUE;
        }
    }
    else
    {
        return FALSE;
    }
}

//}}}
//{{{ function extension($string, $ext)
/**
 * Adds or removes extension to string
 * Mainly used for filename handling
 *
 * @param string $string
 * @param string $ext extension to add or remove
 * @return string
 */
function extension($string, $ext)
{
    $c = strlen($string) - strlen($ext);
    return substr($string, $c) === $ext ? substr($string, 0, $c) : $string.$ext;
}

//}}}
//{{{ function file_extension($filename)
/**
 * Get the bare name and extension of a filename
 * @param string $filename
 * @return array
 */
function file_extension($filename)
{
    $filename = basename($filename);
    $pos = strrpos($filename, '.');
    return ($pos === FALSE || $pos === 0)
        ? array($filename, $filename)
        : array(substr($filename, 0, $pos), substr($filename, $pos));
}

//}}}
//{{{ function file_mime_type($filename)
/**
 * Get the mime_type of the file
 * @param string $filename
 * @return string
 */
function file_mime_type($filename)
{
    if (version_compare(PHP_VERSION, '5.3.0', '>='))
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
    }
    else
    {
        $mime = mime_content_type($filename);
    }
    return $mime;
}

//}}}
//{{{ function filter_extensions($c)
/**
 * Callback for array_filter to get all MPExtension classes
 */
function filter_extensions($c)
{
    return substr($c, 0, 9) === 'MPExtension' && strlen($c) > 9;
}
//}}}
// {{{ function hex_to_rgb($color)
/**
 *
 */
function hex_to_rgb($color)
{
    if ($color[0] == '#')
    {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6)
    {
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    }
    elseif (strlen($color) == 3)
    {
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    }
    else
    {
        return false;
    }

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

// }}}
//{{{ function hsc($string, $ent = ENT_QUOTES, $enc = 'UTF-8')
/**
 * shortcut for htmlspecialchars()
 */
function hsc($string, $ent = ENT_QUOTES, $enc = 'UTF-8')
{
    return htmlspecialchars($string, $ent, $enc);
}
//}}}
//{{{ function is_email($email)
// follows ~99.99% of RFC 2822 according to http://www.regular-expressions.info/email.html
function is_email($email)
{
    $regex = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
    $result = preg_match($regex, $email);
    return (bool)$result;
}
//}}}
//{{{ function is_mobile()
// adapted from http://code.google.com/p/php-mobile-detect/
function is_mobile()
{
    static $mobility = NULL;

    if (is_null($mobility))
    {
        $mobility = FALSE;
        $devices = array(
            "android"       => "android",
            "blackberry"    => "blackberry",
            "iphone"        => "(iphone|ipod)",
            "opera"         => "opera mini",
            "palm"          => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
            "windows"       => "windows ce; (iemobile|ppc|smartphone)",
            "generic"       => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)"
        );

        $userAgent = &$_SERVER['HTTP_USER_AGENT'];
        $accept = &$_SERVER['HTTP_ACCEPT'];

        if ((isset($_SERVER['HTTP_X_WAP_PROFILE'])|| isset($_SERVER['HTTP_PROFILE'])) ||
            (strpos($accept,'text/vnd.wap.wml') > 0 || strpos($accept,'application/vnd.wap.xhtml+xml') > 0))
            {
                $mobility = TRUE;
            } 
            else 
            {
                foreach ($devices as $device => $regexp) 
                {
                    if (!$mobility && preg_match("/" . $regexp . "/i", $userAgent))
                    {
                        $mobility = TRUE;
                    }
                }
            }
    }
    return $mobility;
}
//}}}
//{{{ function is_slug($slug)
function is_slug($slug)
{
    return preg_match('/^[a-zA-Z0-9]([-a-zA-Z0-9]*[a-zA-Z0-9])?$/', $slug);
}

//}}}
//{{{ function prepend_name($key, $name)
/**
 * Prepends $name with $key while keeping it in array notation
 *
 * @param string $key string to prepend with
 * @param string $name string to change
 * @param boolean $multiple append [] for html array fields
 */
function prepend_name($key, $name)
{
    if (strpos($name, '[') === FALSE)
    {
        return $key.'['.$name.']';
    }
    else
    {
        $pos = strpos($name, '[');
        $first = substr($name, 0, $pos);
        $second = substr($name, $pos);
        return $key.'['.$first.']'.$second;
    }
}

//}}}
//{{{ function random_string($length = 10, $base = 62)
function random_string($length = 10, $base = 62)
{
    $c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $o = '';
    $max = $base - 1;
    for ($i = 0; $i < $length; ++$i)
    {
        $o .= substr($c, mt_rand(0, $max), 1);
    }
    return $o;
}

//}}}
// {{{ function rgb_to_hex($r, $g=-1, $b=-1)
/**
 *
 */
function rgb_to_hex($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
    {
        list($r, $g, $b) = $r;
    }

    $r = intval($r);
    $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}

// }}}
// {{{ function rgb_to_yuv($r, $g=-1, $b=-1)
/**
 *
 */
function rgb_to_yuv($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
    {
        list($r, $g, $b) = $r;
    }

    $y = 0.299*$r + 0.587*$g + 0.114*$b;
    $u = 0.713*($r-$y);
    $v = ($b-$y)*0.565;

    return array($y, $u, $v);
}

// }}}
//{{{ function rm_resource_dir($path, $rm_path = TRUE)
/**
 * Recursively remove files and directories
 * @param string $path file path
 * @param boolean $rm_path remove path directory if TRUE
 * @return boolean
 */
function rm_resource_dir($path, $rm_path = TRUE)
{
    if (is_dir($path))
    {
        $files = scandir($path);
        if ($files !== FALSE)
        {
            foreach ($files as $file)
            {
                if (!($file === '.' || $file === '..'))
                {
                    if (!rm_resource_dir(rtrim($path,'/').'/'.$file))
                    {
                        unlink($path.'/'.$file);
                    }
                }
            }
        }
        if ($rm_path)
        {
            rmdir($path);
        }
        return TRUE;
    }
    return FALSE;
}

//}}}
// {{{ function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s')
/**
 * Return human readable sizes
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.3.0
 * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
 * @param       int     $size        size in bytes
 * @param       string  $max         maximum unit
 * @param       string  $system      'si' for SI, 'bi' for binary prefixes
 * @param       string  $retstring   return string format
 */
function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s')
{
    // Pick units
    $systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
    $systems['si']['size']   = 1000;
    $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
    $systems['bi']['size']   = 1024;
    $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];

    // Max unit to display
    $depth = count($sys['prefix']) - 1;
    if ($max && false !== $d = array_search($max, $sys['prefix'])) {
        $depth = $d;
    }

    // Loop
    $i = 0;
    while ($size >= $sys['size'] && $i < $depth) {
        $size /= $sys['size'];
        $i++;
    }

    return sprintf($retstring, $size, $sys['prefix'][$i]);
}
// }}}
//{{{ function slugify($name, $replacement = '-')
function slugify($name, $replacement = '-')
{
    // Characters to process. All other characters will be dropped
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKKLMNOPQRSTUVWXYZ-_ ';
    // Characters to replace with hyphens
    $hyphened = '-_ ';
    $o = '';
    $last_char = '';
    $length = strlen($name);
    for ($i = 0; $i < $length; ++$i)
    {
        $char = substr($name, $i, 1);
        if (strpos($chars, $char) !== FALSE)
        {
            if (strpos($hyphened, $char) === FALSE)
            {
                $o .= $char;
                $last_char = $char;
            }
            else
            {
                if ($last_char !== $replacement)
                {
                    $o .= $replacement;
                    $last_char = $replacement;
                }
            }
        }
    }
    return strtolower($o);
}

//}}}
//{{{ function test_db_settings($db)
function test_db_settings($db)
{
    try
    {
        $db['options'] = array_clean($db['options']);
        $mondb = new Mongo($db['server'], $db['options']);
        unset($mondb);
        return TRUE;
    }
    catch (Exception $e)
    {
        return FALSE;
    }
}

//}}}
//{{{ function time_zones()
/**
 * Returns an array of country named time zones
 */
function time_zones()
{
    $zones = DateTimeZone::listIdentifiers();
    $continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
    $locations = array();
    foreach ($zones as $zone)
    {
        $zone = explode('/', $zone);
        if (in_array($zone[0], $continents) && isset($zone[1]) && $zone[1] !== '')
        {
            $locations[$zone[0]][$zone[0].'/'.$zone[1]] = str_replace('_', ' ', $zone[1]);
        }
    }
    foreach ($locations as &$zones)
    {
        asort($zones);
    }
    return $locations;
}

//}}}
// {{{ function word_split($str, $words = 15, $random = FALSE)
/**
 *
 * @param string $str The text string to split
 * @param integer $words The number of words to extract. Defaults to 15
 */
function word_split($str, $words = 15, $random = FALSE)
{
    if ($random)
    {
	    $arr = preg_split("/[\s]+/", $str);
        $max_start = count($arr) - $words;
        $start = mt_rand(0, $max_start);
    }
    else
    {
	    $arr = preg_split("/[\s]+/", $str, $words + 1);
        $start = 0;
    }
	$arr = array_slice($arr, $start, $words);
	return implode(' ',$arr);
}

// }}}
