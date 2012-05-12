<?php

class MPFileManager
{
    //{{{ properties
    protected static $file_path = '';
    protected static $web_path = '';
    protected static $sizes = array();
    //}}}
    //{{{ constants 
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'MPFileManager MPModule';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';

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
        self::$sizes = self::get_image_sizes();
        self::$sizes['browse'] = array(
            'width' => '90',
            'height' => '90',
        );
        self::$file_path = !is_null(MPData::query('MPFileManager', 'file_path'))
            ? MPData::query('MPFileManager', 'file_path')
            : DIR_FILE.'/upload';
        self::$web_path = !is_null(MPData::query('MPFileManager', 'web_path'))
            ? MPData::query('MPFileManager', 'web_path')
            : '/file/upload';
        if (!is_dir(self::$file_path))
        {
            mkdir(self::$file_path, 0777, TRUE);
        }
    }

    //}}}
    //{{{ public function hook_mpadmin_css()
    public function hook_mpadmin_css()
    {
        $screen = array();
        if (strpos(URI_PATH, '/admin/mod/MPFileManager/') === 0)
        {
            $screen[] = '/admin/static/MPAdmin/screen.css/';
            $screen[] = '/admin/static/MPFileManager/browse.css/';
        }
        else
        {
            $screen[] = '/admin/static/MPFileManager/field.css/';
        }

        $css['screen'] = $screen;

        return $css;
    }

    //}}}
    //{{{ public function hook_mpadmin_js()
    public function hook_mpadmin_js()
    {
        $js[] = '/admin/static/MPFileManager/jquery.windowmsg-1.0.js/';
        if (strpos(URI_PATH, '/mod/MPFileManager/browse/') !== FALSE)
        {
            $js[] = '/admin/static/MPFileManager/filemanager.js/';
        }
        else
        {
            $js[] = '/admin/static/MPFileManager/admin_nav.js/';
            $js[] = '/admin/static/MPFileManager/field.js/';
            $js[] = '/admin/static/MPFileManager/tinymce.js/';
        }
        return $js;
    }

    //}}}
    //{{{ public function hook_mpadmin_js_header()
    public function hook_mpadmin_js_header()
    {
        $js = array();
        if (strpos(URI_PATH, '/mod/MPFileManager/') !== FALSE)
        {
            $js[] = '/admin/static/MPFileManager/jquery.js/';
            $js[] = '/admin/static/MPAdmin/admin.js/';
        }
        if (strpos(URI_PATH, '/admin/mod/MPFileManager/browse/tinymce/') !== FALSE)
        {
            $js[] = '/file/module/MPAdmin/js/tiny_mce/tiny_mce.js';
            $js[] = '/file/module/MPAdmin/js/tiny_mce/jquery.tinymce.js';
            $js[] = '/file/module/MPAdmin/js/tiny_mce/tiny_mce_popup.js';
            $js[] = '/admin/static/MPFileManager/tinymce_browse.js/';
        }
        return $js;
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
        return array(
            'file_browser_callback' => 'MPFileManager_browser'
        );
    }
    
    //}}}
    //{{{ public function hook_mpdata_info()
    public function hook_mpdata_info()
    {
        $fields = array();
        $fields[] = array(
            'field' => MPField::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'File path'
                    )
                )
            ),
            'name' => 'file_path',
            'type' => 'text',
            'value' => array(
                'data' => self::$file_path
            )
        );
        $fields[] = array(
            'field' => MPField::layout(
                'text',
                array(
                    'data' => array(
                        'label' => 'Web path'
                    )
                )
            ),
            'name' => 'web_path',
            'type' => 'text',
            'value' => array(
                'data' => self::$web_path
            )
        );
        $fields[] = array(
            'field' => MPField::layout(
                'filemanager_image_size',
                array(
                    'width' => array(
                        'label' => 'Large Image Size'
                    )
                )
            ),
            'name' => 'size_large',
            'type' => 'filemanager_image_size',
            'value' => array(
                'height' => self::$sizes['large']['height'],
                'width' => self::$sizes['large']['width']
            )
        );
        $fields[] = array(
            'field' => MPField::layout(
                'filemanager_image_size',
                array(
                    'width' => array(
                        'label' => 'Medium Image Size'
                    )
                )
            ),
            'name' => 'size_medium',
            'type' => 'filemanager_image_size',
            'value' => array(
                'height' => self::$sizes['medium']['height'],
                'width' => self::$sizes['medium']['width']
            )
        );
        $fields[] = array(
            'field' => MPField::layout(
                'filemanager_image_size',
                array(
                    'width' => array(
                        'label' => 'Thumbnail Image Size'
                    )
                )
            ),
            'name' => 'size_thumb',
            'type' => 'filemanager_image_size',
            'value' => array(
                'height' => self::$sizes['thumb']['height'],
                'width' => self::$sizes['thumb']['width']
            )
        );
        return $fields;
    }

    //}}}
    //{{{ public function hook_mpdata_validate($name, $data)
    public function hook_mpdata_validate($name, $data)
    {
        $success = TRUE;
        switch ($name)
        {
            case 'size_large':
            case 'size_medium':
            case 'size_thumb':
                if (!is_numeric($data['width']) || !is_numeric($data['height']))
                {
                    $data['width'] = '';
                    $data['height'] = '';
                }
        }
        return array(
            'success' => $success,
            'data' => $data
        );
    }

    //}}}
    //{{{ public function hook_mproutes()
    public function hook_mproutes()
    {
        $ctrl = dirname(__FILE__).'/admin/controller';
        $routes = array(
            array('#^/admin/mod/MPFileManager/rpc/([^/]+/)+$#', $ctrl.'/rpc.php', MPRouter::ROUTE_PCRE),
        );
        return $routes;
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
    //{{{ public function hook_mpuser_perm()
    public function hook_mpuser_perm()
    {
        return array(
            'view files' => 'View Files',
            'create folder' => 'Create Folder',
            'edit folder' => 'Edit Folder',
            'upload file' => 'Upload File',
            'edit file' => 'Edit File'
        );
    }
    //}}}
    // {{{ public function save_file($path, $name, $tmp_file)
    public function save_file($path, $name, $tmp_file)
    {
        $original_file = $path.'/'.$name;
        $success = move_uploaded_file($tmp_file, $original_file);

        if ($success)
        {
            $resized_path = $path.'/_resized';
            mkdir($resized_path);
            if (is_dir($resized_path) && list($width, $height, $mime_type) = getimagesize($original_file))
            {
                $quality = 90;
                $basename = file_extension($name);
                foreach (self::$sizes as $label => $size)
                {
                    if ($size['width'] > 0 && $size['height'] > 0 && ($width > $size['width'] || $height > $size['height']))
                    {
                        $ratio_orig = $width/$height;

                        if ($size['width']/$size['height'] > $ratio_orig) 
                        {
                           $size['width'] = $size['height']*$ratio_orig;
                        } 
                        else 
                        {
                           $size['height'] = $size['width']/$ratio_orig;
                        }
                        
                        $image = imagecreatetruecolor($size['width'], $size['height']);
                        $resized_file = $resized_path.'/'.$basename[0].'-'.$label.$basename[1];
                        switch ($mime_type)
                        {
                            case IMAGETYPE_GIF:
                                $orig_image = imagecreatefromgif($original_file);
                                imagealphablending($image, false);
                                imagesavealpha($image, true);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagegif($image, $resized_file);
                            break;
                            case IMAGETYPE_JPEG:
                                $orig_image = imagecreatefromjpeg($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagejpeg($image, $resized_file, $quality);
                            break;
                            case IMAGETYPE_PNG:
                                $orig_image = imagecreatefrompng($original_file);
                                imagealphablending($image, false);
                                imagesavealpha($image, true);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                if ( function_exists('imageistruecolor') && imageistruecolor( $orig_image ) )
                                {
                                    imagetruecolortopalette( $image, false, imagecolorstotal( $orig_image ) );
                                }
                                imagepng($image, $resized_file);
                            break;
                            case IMAGETYPE_WBMP:
                                $orig_image = imagecreatefromwbmp($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagewbmp($image, $resized_file);
                            break;
                            case image_type_to_mime_type(IMAGETYPE_XBM):
                                $orig_image = imagecreatefromxbm($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagexbm($image, $resized_file);
                            break;
                        }
                        if (isset($orig_image))
                        {
                            imagedestroy($orig_image);
                        }
                        imagedestroy($image);
                    }
                }
            }
        }

        return $success;
    }
    //}}}
    //{{{ protected function _rpc_browser($data)
    protected function _rpc_browser($data)
    {
        $success = FALSE;
        $action = $data['action'];
        $view = $data['view'];
        $dir = $data['dir'];
        $files = json_decode($data['files'], TRUE);
        $web = self::$web_path.str_replace(self::$file_path, '', $dir);
        $info = array();
        switch ($action)
        {
            // {{{ case 'add'
            case 'add':
                if (count($files) === 1)
                {
                    $new_dir = $dir.'/'.$files[0];
                    $success = mkdir($new_dir);
                    if ($success)
                    {
                        mkdir($new_dir.'/_resized');
                        chmod($new_dir);
                        $action = 'refresh';
                    }
                }
            break;
            // }}}
            // {{{ case 'copy'
            case 'copy':
                $old_dir = array_pop($files);
                if (is_dir($old_dir) && is_dir($dir))
                {
                    foreach ($files as $v)
                    {
                        $old_file = $old_dir.'/'.$v;
                        $new_file = $dir.'/'.$v;
                        if (is_file($old_file))
                        {
                            $success = copy($old_file, $new_file);
                            if (!$success)
                            {
                                break;
                            }
                            mkdir($dir.'/_resized');
                            list($name, $ext) = file_extension($v);
                            foreach (array_keys(self::$sizes) as $label)
                            {
                                $fname = '/_resized/'.$name.'-'.$label.$ext;
                                if (is_file($old_dir.$fname))
                                {
                                    copy($old_dir.$fname, $dir.$fname);
                                }
                            }
                        }
                        elseif (is_dir($old_file))
                        {
                            $success = dir_copy($old_file, $new_file);
                            if (!$success)
                            {
                                break;
                            }
                        }
                    }
                    $action = 'refresh';
                }
            break;
            // }}}
            // {{{ case 'delete'
            case 'delete':
                foreach ($files as $v)
                {
                    $file = $dir.'/'.$v;
                    if (is_file($file))
                    {
                        $success = unlink($file);
                        if ($success && is_dir($dir.'/_resized'))
                        {
                            list($name, $ext) = file_extension($v);
                            foreach (array_keys(self::$sizes) as $label)
                            {
                                $fname = '/_resized/'.$name.'-'.$label.$ext;
                                if (is_file($dir.$fname))
                                {
                                    unlink($dir.$fname);
                                }
                            }
                        }
                    }
                    elseif (is_dir($file))
                    {
                        $success = rm_resource_dir($file);
                    }
                }
                $action = 'refresh';
            break;
            // }}}
            // {{{ case 'list'
            case 'list':
                $dir_files = scandir($dir);
                $tmp_sort_dirs = $tmp_sort_files = $tmp_files = $tmp_dirs = array();
                $tmp_size = 0;
                foreach ($dir_files as $v)
                {
                    if (strpos($v,'.') === 0 || $v === '_resized')
                    {
                        continue;
                    }
                    $mime = explode('/', file_mime_type($dir.'/'.$v));
                    $stat = stat($dir.'/'.$v);
                    $stat['nice_mtime'] = date('Y-m-d H:i:s', $stat['mtime']);
                    $stat['nice_size'] = size_readable($stat['size']);
                    if (is_dir($dir.'/'.$v))
                    {
                        $tmp_dirs[] = array(
                            'name' => $v,
                            'stat' => $stat,
                            'mime' => array('folder'),
                            'ext' => '',
                            'resized_path' => '',
                        );
                        $tmp_sort_dirs[] = $v;
                    }
                    else
                    {
                        $filename = file_extension($v);
                        if ($mime[0] === 'image')
                        {
                            $tmp_files[] = array(
                                'name' => $v,
                                'stat' => $stat,
                                'mime' => $mime,
                                'ext' => $filename[1],
                                'resized_path' => self::get_resized_image($web.'/'.$v, 'browse'),
                            );
                            $tmp_sort_files[] = $v;
                        }
                        elseif ($view !== 'image')
                        {
                            $tmp_files[] = array(
                                'name' => $v,
                                'stat' => $stat,
                                'mime' => $mime,
                                'ext' => $filename[1],
                                'resized_path' => '',
                            );
                            $tmp_sort_files[] = $v;
                        }
                        else
                        {
                            continue;
                        }
                    }
                    $tmp_size += $stat['size'];
                }
                $files = array_merge($tmp_dirs, $tmp_files);
                $success = TRUE;
                $info['total_size'] = size_readable($tmp_size);
                $info['total_dirs'] = count($tmp_dirs);
                $info['total_files'] = count($tmp_files);
            break;
            // }}}
            // {{{ case 'move'
            case 'move':
                $old_dir = array_pop($files);
                if (is_dir($old_dir) && is_dir($dir))
                {
                    foreach ($files as $v)
                    {
                        $old_file = $old_dir.'/'.$v;
                        $new_file = $dir.'/'.$v;
                        if (file_exists($old_file))
                        {
                            $success = rename($old_file, $new_file);
                            if (!$success)
                            {
                                break;
                            }
                        }
                    }
                    $action = 'refresh';
                }
            break;
            // }}}
            // {{{ case 'rename'
            case 'rename':
                if (count($files) == 2)
                {
                    $old_file = $dir.'/'.$files[0];
                    list($name, $ext) = file_extension($files[0]);
                    $new_file = $dir.'/'.$files[1].$ext;
                    if (file_exists($old_file) && !file_exists($new_file))
                    {
                        $success = rename($old_file, $new_file);
                        if ($success && is_dir($dir.'/_resized'))
                        {
                            foreach (array_keys(self::$sizes) as $label)
                            {
                                $fname = '/_resized/'.$name.'-'.$label.$ext;
                                $new_fname = '/_resized/'.$files[1].'-'.$label.$ext;
                                if (is_file($dir.$fname))
                                {
                                    rename($dir.$fname, $dir.$new_fname);
                                }
                            }
                        }
                    }
                    $action = 'refresh';
                }
            break;
            // }}}
        }
        echo json_encode(
            array(
                'success' => $success,
                'action' => $action,
                'view' => $view,
                'dir' => $dir,
                'files' => $files,
                'web' => $web,
                'info' => $info,
            )
        );
    }

    //}}}
    //{{{ public static function web_path()
    public static function web_path()
    {
        return self::get_web_path();
    }
    //}}}
    //{{{ public static function get_web_path()
    public static function get_web_path()
    {
        return self::$web_path;
    }
    //}}}
    //{{{ public static function set_web_path($path)
    public static function set_web_path($path)
    {
        MPData::update('MPFileManager', 'web_path', $path);
        self::$web_path = $path;
        return self::$web_path;
    }
    //}}}
    //{{{ public static function file_path()
    public static function file_path()
    {
        return self::get_file_path();
    }
    //}}}
    //{{{ public static function get_file_path()
    public static function get_file_path()
    {
        return self::$file_path;
    }
    //}}}
    //{{{ public static function set_file_path($path)
    public static function set_file_path($path)
    {
        MPData::update('MPFileManager', 'file_path', $path);
        self::$file_path = $path;
        return self::$file_path;
    }
    //}}}
    //{{{ public static function scan($dir)
    public static function scan($dir)
    {
        if (is_dir($dir))
        {
            $contents = array('files' => array(), 'dirs' => array());
            $link_base = str_replace(MPFileManager::file_path(), '', $dir);
            $files = $dirs = array();
            $scans = scandir($dir);
            foreach ($scans as $k => $v)
            {
                $file = $dir.'/'.$v;
                if (is_file($file))
                {
                    $contents['files'][] = array(
                        'link' => $link_base.'/'.$v,
                        'name' => $v,
                        'size' => filesize($file)
                    );
                }
                elseif (is_dir($file))
                {
                    $dir_data = array(
                        'name' => $v
                    );
                    switch ($v)
                    {
                        case '.':
                            $dir_data['link'] = empty($link_base) ? '/' : $link_base;
                        break;
                        case '..':
                            $dir_data['link'] = empty($link_base) ? '/' : dirname($link_base);
                        break;
                        default:
                            $dir_data['link'] = $link_base.'/'.$v;
                        break;
                    }
                    $contents['dirs'][] = $dir_data;
                }
            }
            return $contents;
        }
        elseif (is_file($dir))
        {
            throw new MPFileManagerIsFileException($dir.' is a file');
        }
        else
        {
            throw new MPFileManagerNotExistException($dir.' does not exist');
        }
    }
    //}}}
    //{{{ public static function dir_scan($dir)
    /**
     * Like scan, but just for directories
     * @param string $dir full path to scan
     * @return array
     */
    public static function dir_scan($dir)
    {
        $dirs = array();
        if (file_exists($dir) && is_dir($dir))
        {
            /**
             * using dirname() because we want to see at least the base folder
             * name in the heirarchy
             */
            $the_dir = str_replace(MPFileManager::file_path(), '', $dir);
            if (empty($the_dir))
            {
                $the_dir = '/';
            }
            $dirs = array($the_dir);
            $files = scandir($dir);
            foreach ($files as $file)
            {
                $filepath = $dir.'/'.$file;
                if ($file !== '.' && $file !== '..' && is_dir($filepath))
                {
                    $dirs = array_merge($dirs, self::dir_scan($filepath));
                }
            }
        }
        return $dirs;
    }
    //}}}
    // {{{ public static function get_image_sizes()
    public static function get_image_sizes()
    {
        $default_size = array(
            'width' => '',
            'height' => '',
        );
        $sizes['thumb'] = !is_null(MPData::query('MPFileManager', 'size_thumb'))
            ? MPData::query('MPFileManager', 'size_thumb')
            : $default_size;
        $sizes['medium'] = !is_null(MPData::query('MPFileManager', 'size_medium'))
            ? MPData::query('MPFileManager', 'size_medium')
            : $default_size;
        $sizes['large'] = !is_null(MPData::query('MPFileManager', 'size_large'))
            ? MPData::query('MPFileManager', 'size_large')
            : $default_size;
        return $sizes;
    }
    //}}}
    // {{{ public static function get_resized_image($file, $size)
    public static function get_resized_image($web_file, $size)
    {
        list($name, $ext) = file_extension($web_file);
        $web_path = dirname($web_file);
        $web_resized = $web_path.'/_resized';
        if (is_string($size) && ake($size, self::$sizes))
        {
            $new_web_file = $web_resized.'/'.$name.'-'.$size.$ext;
            if (is_file(self::$file_path.str_replace('/file/upload', '', $new_web_file)))
            {
                return $new_web_file;
            }
        }
        elseif (is_array($size))
        {
        }

        return $web_file;
    }
    //}}}
}

class MPFileManagerNotExistException extends Exception {}
class MPFileManagerIsFileException extends Exception {}
