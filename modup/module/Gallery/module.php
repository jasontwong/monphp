<?php

class Gallery
{
    //{{{ properties
    protected static $has_ext = array();
    protected static $image_sizes = array();
    protected static $item_types = array();
    protected static $misc_fields = array();
    protected static $upload_dir = '';

    //}}}
    //{{{ constants 
    const MODULE_AUTHOR = 'Jason T. Wong';
    const MODULE_DESCRIPTION = 'Gallery Module';
    const MODULE_WEBSITE = '';
    const MODULE_DEPENDENCY = '';

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
        if (extension_loaded('gd') && function_exists('gd_info'))
        {
            self::$has_ext['gd'] = TRUE;
        }
        else
        {
            self::$has_ext['gd'] = FALSE;
        }
        self::$upload_dir = DIR_FILE.'/module/gallery/uploads';
        if (!is_dir(self::$upload_dir))
        {
            mkdir(self::$upload_dir, 0777, TRUE);
        }
        self::$image_sizes = array(
            'large' => array(
                'width' => '800',
                'height' => '600'
            ),
            'medium' => array(
                'width' => '400',
                'height' => '300'
            ),
            'thumbnail' => array(
                'width' => '72',
                'height' => '72'
            )
        );
        self::$item_types = array(
            'image' => 'Image',
            'video' => 'Video',
            'pdf' => 'PDF'
        );
        self::$misc_fields = array(
            array(
                'label' => 'Location',
                'name' => 'location',
                'type' => 'text',
                'value' => '',
            ),
        );
    }

    //}}}
    //{{{ public function hook_admin_css()
    public function hook_admin_css()
    {
        $css = array();
        if (URI_PARTS > 3 && URI_PART_2 === 'gallery')
        {
            switch (URI_PART_3)
            {
                case 'edit_item':
                case 'edit_album':
                    $css['screen'] = array(
                        '/file/module/gallery/thickbox/thickbox.css'
                    );
                break;
            }
        }

        return $css;
    }

    //}}}
    //{{{ public function hook_admin_js()
    public function hook_admin_js()
    {
        $js = array();
        if (URI_PARTS > 3)
        {
            if (URI_PART_2 === 'gallery')
            {
                switch (URI_PART_3)
                {
                    case 'edit_item':
                    case 'manage_items':
                    case 'edit_album':
                        $js[] = '/file/module/gallery/thickbox/thickbox-compressed.js';
                    break;
                }
            }
        }

        return $js;
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
        if (user::perm('add gallery'))
        {
            $links[] = array(
                'label' => 'Add Album',
                'uri' => '/admin/module/gallery/add_album/',
            );
        }
        if (user::perm('edit gallery'))
        {
            $links[] = array(
                'label' => 'Manage Albums',
                'uri' => '/admin/module/gallery/manage/',
            );
        }

        return empty($links)
            ? array()
            : array(
                'label' => 'Gallery',
                'links' => $links
            );
    }

    //}}}
    //{{{ public function hook_uninstall()
    public function hook_uninstall()
    {
        rm_resource_dir(self::$upload_dir, FALSE);
    }

    //}}}
    //{{{ public function hook_user_perm()
    public function hook_user_perm()
    {
        return array(
            'add gallery' => 'Add Gallery',
            'edit own gallery' => 'Edit Own Gallery',
            'edit galleries' => 'Edit Galleries',
        );
    }

    //}}}
    // {{{ protected static function img_resize($tmp_name, $width, $height, $save_dir, $save_name)
    /**
     * Make thumbs from JPEG, PNG, GIF source file
     *
     * @param string $tmp_name = $_FILES['source']['tmp_name'];   
     * @param int $width max width size
     * @param int $height max height size
     * @param string $save_dir destination folder
     * @param string $save_name new name
     * @return boolean
     *
     */
    protected static function img_resize($tmp_name, $width, $height, $save_dir, $save_name)
    {
        $save_dir .= ( substr($save_dir,-1) != "/") ? "/" : "";
        // {{{ if using gd
        if (deka(FALSE, self::$has_ext, 'gd'))
        {
            $gis = getimagesize($tmp_name);
            $type = $gis[2];
            switch ($type)
            {
                case "1": 
                    $imorig = imagecreatefromgif($tmp_name); 
                break;
                case "2": 
                    $imorig = imagecreatefromjpeg($tmp_name);
                break;
                case "3": 
                    $imorig = imagecreatefrompng($tmp_name); 
                break;
                default:  
                    $imorig = imagecreatefromjpeg($tmp_name);
            }
        
            // get the right proportion, flip height and width if necessary
            if ($height > $width)
            {
                $y = imageSX($imorig);
                $x = imageSY($imorig);
                $size = $height;
            }
            else
            {
                $x = imageSX($imorig);
                $y = imageSY($imorig);
                $size = $width;
            }
            // get the new sizes
            if ($gis[0] <= $size)
            {
                $av = $x;
                $ah = $y;
            }
            else
            {
                $yc = $y * 1.3333333;
                $d = $x > $yc ? $x : $yc;
                $c = $d > $size ? $size/$d : $size;
                $av = $x * $c;
                $ah = $y * $c;
            }
            // if flipped, flip back
            if ($height > $width)
            {
                $temp = $av;
                $av = $ah;
                $ah = $temp;
                $temp = $x;
                $x = $y;
                $y = $temp;
            }
            $im = imagecreate($av, $ah);
            $im = imagecreatetruecolor($av,$ah);
            if (imagecopyresampled($im,$imorig , 0,0,0,0,$av,$ah,$x,$y))
            {
                if (imagejpeg($im, $save_dir.$save_name))
                {
                    imagedestroy($im);
                    return TRUE;
                }
            }
            imagedestroy($im);
    
        }
        // }}}
        return FALSE;
    }
    
    // }}}
    // {{{ protected static function store_file($src, $dest, $name, $rm_src = TRUE)
    protected static function store_file($src, $dest_dir, $name, $force = FALSE, $rm_src = FALSE)
    {
        $dest_dir .= ( substr($dest_dir,-1) != "/") ? "/" : "";
        if ($force)
        {
            $filename = $dest_dir.$name;
        }
        else
        {
            $filename = available_filename($dest_dir.$name);
        }
        if ($filename !== FALSE)
        {
            if (copy($src, $filename))
            {
                chmod($filename, 0755);
            }
            else
            {
                $filename = FALSE;
            }
        }
        if ($rm_src)
        {
            unlink($src);
        }
        return $filename;
    }
    
    // }}}
    //{{{ public static function can_resize()
    public function can_resize()
    {
        foreach (self::$has_ext as $v)
        {
            if ($v)
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    //}}}
    //{{{ public static function get_album_dir($id)
    public function get_album_dir($id)
    {
        return self::$upload_dir.'/album_'.$id;
    }

    //}}}
    //{{{ public static function get_item_types()
    public function get_item_types()
    {
        return self::$item_types;
    }

    //}}}
    // {{{ public static function get_misc_fields()
    public function get_misc_fields()
    {
        return self::$misc_fields;
    }

    // }}}
    // {{{ public static function store_album_cover_image($id, $file)
    public function store_album_cover_image($id, $file)
    {
        $album_dir = self::$upload_dir.'/album_'.$id;
        if (!is_dir($album_dir))
        {
            mkdir($album_dir, 0777, TRUE);
        }
        list($name, $ext) = file_extension($file['name']);
        $file['tmp_name'] = self::store_file($file['tmp_name'], $album_dir, 'cover'.$ext, TRUE, TRUE);
        if ($file['tmp_name'] === FALSE)
        {
            return array();
        }
        return $file;
    }

    // }}}
    // {{{ public static function store_album_item($album_id, $item)
    public function store_album_item($album_id, $item)
    {
        $item_dir = self::$upload_dir.'/album_'.$album_id.'/'.$item['type'];
        if (!is_dir($item_dir.'/full'))
        {
            mkdir($item_dir.'/full', 0777, TRUE);
        }
        switch ($item['type'])
        {
            case 'image':
                if (strpos($item['file_tmp_name'], '/tmp/') !== FALSE)
                {
                    $item['file_tmp_name'] = self::store_file($item['file_tmp_name'], $item_dir.'/full', $item['file_name'], FALSE, TRUE);
                    chmod($item['file_tmp_name'], 0755);
                }
                if (self::can_resize())
                {
                    foreach (self::$image_sizes as $dir => $size)
                    {
                        if (!is_dir($item_dir.'/'.$dir))
                        {
                            mkdir($item_dir.'/'.$dir, 0777, TRUE);
                        }
                        $success = self::img_resize($item['file_tmp_name'], $size['width'], $size['height'], $item_dir.'/'.$dir, basename($item['file_tmp_name']));
                        if ($success)
                        {
                            $item['file_meta'][$dir]['file_tmp_name'] = $item_dir.'/'.$dir.'/'.basename($item['file_tmp_name']);
                            chmod($item['file_meta'][$dir]['file_tmp_name'], 0755);
                        }
                    }
                }
                else
                {
                    foreach (self::$image_sizes as $dir => $size)
                    {
                        if (!is_dir($item_dir.'/'.$dir))
                        {
                            mkdir($item_dir.'/'.$dir, 0777, TRUE);
                        }
                        if (eka($item, 'file_meta', $dir))
                        {
                            $item['file_meta'][$dir]['file_tmp_name'] = self::store_file($item['file_meta'][$dir]['file_tmp_name'], $item_dir.'/'.$dir, $item['file_meta'][$dir]['file_name']);
                            chmod($item['file_meta'][$dir]['file_tmp_name'], 0755);
                        }
                    }
                }
            break;
            default:
                $item['file_tmp_name'] = self::store_file($item['file_tmp_name'], $item_dir, $item['file_name'], FALSE, TRUE);
            break;
        }

        return $item;
    }

    // }}}
}

?>
