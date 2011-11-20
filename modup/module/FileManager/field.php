<?php

class FileManagerField
{
    //{{{ public static function field_fieldtype_filemanager_image($data)
    public static function field_fieldtype_filemanager_image($data)
    {
        return array(
            array(
                'name' => 'data',
                'meta' => array(
                    'single' => deka(FALSE, $data, 'single')
                ),
                'default_data' => '',
            ),
            array(
                'name' => 'caption',
                'meta' => array(
                    'use' => deka(FALSE, $data, 'caption'),
                ),
                'default_data' => '',
            ),
            array(
                'name' => 'uri',
                'meta' => array(
                    'use' => deka(FALSE, $data, 'uri'),
                ),
                'default_data' => '',
            ),
        );
    }
    //}}}
    //{{{ public static function field_layout_filemanager_image($meta = array())
    public static function field_layout_filemanager_image($meta = array())
    {
        $class = 'file FileManagerBrowser TypeImage';
        if (deka(FALSE, $meta, 'data', 'meta', 'single'))
        {
            $class .= ' SingleFile';
        }
        return array(
            'data' => array(
                'attr' => array(
                    'class' => $class,
                    'type' => 'hidden'
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'caption' => array(
                'attr' => array(
                    'placeholder' => 'Caption',
                    'class' => 'caption',
                    'type' => deka(FALSE, $meta, 'caption', 'meta', 'use') ? 'text' : 'hidden'
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE,
            ),
            'uri' => array(
                'attr' => array(
                    'placeholder' => 'URL',
                    'class' => 'uri',
                    'type' => deka(FALSE, $meta, 'uri', 'meta', 'use') ? 'text' : 'hidden'
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE,
            ),
        );
    }

    //}}}
    //{{{ public static function field_layout_filemanager_image_size()
    public static function field_layout_filemanager_image_size()
    {
        return array(
            'width' => array(
                'attr' => array(
                    'class' => 'size_width',
                    'type' => 'text',
                    'size' => 4,
                ),
                'text' => ' w ',
                'element' => Field::ELEMENT_INPUT,
            ),
            'height' => array(
                'attr' => array(
                    'class' => 'size_height',
                    'type' => 'text',
                    'size' => 4,
                ),
                'text' => ' h',
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE,
            ),
        );
    }

    //}}}
    //{{{ public static function field_meta_filemanager_image()
    public static function field_meta_filemanager_image()
    {
        return array(
            'caption' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Allow Caption?</label>",
                'type' => 'checkbox_boolean'
            ),
            'uri' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Allow Link?</label>",
                'type' => 'checkbox_boolean'
            ),
            'single' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Single Image?</label>",
                'type' => 'checkbox_boolean'
            ),
        );
    }

    //}}}
    //{{{ public static function field_post_filemanager_image_size($key, $data)
    public static function field_post_filemanager_image_size($key, $data)
    {
        return $data;
    }

    //}}}
    //{{{ public static function field_public_filemanager_image()
    public static function field_public_filemanager_image()
    {
        return array(
            'description' => 'FileManager image picker',
            'meta' => TRUE,
            'name' => 'Image',
        );
    }

    //}}}
}

?>
