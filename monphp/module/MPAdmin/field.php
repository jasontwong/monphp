<?php

class MPAdminField
{
    //{{{ public static function field_layout_tinyMCE()
    public static function field_layout_tinyMCE()
    {
        return array(
            'theme_advanced_styles' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'label' => 'Styles (tinyMCE)',
                'element' => MPField::ELEMENT_INPUT,
            ),
            'theme_advanced_blockformats' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'label' => 'MPFormats (tinyMCE)',
                'element' => MPField::ELEMENT_INPUT,
            ),
            'theme_advanced_text_colors' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text'
                ),
                'label' => 'Text Colors (tinyMCE)',
                'element' => MPField::ELEMENT_INPUT,
            ),
            'theme_advanced_more_colors' => array(
                'attr' => array(
                    'class' => 'checkbox',
                    'type' => 'checkbox'
                ),
                'label' => 'More Colors (tinyMCE)',
                'element' => MPField::ELEMENT_INPUT,
            ),
        );
    }

    //}}}
    //{{{ public static function field_post_tinyMCE($key, $data)
    public static function field_post_tinyMCE($key, $data)
    {
        $data['theme_advanced_more_colors'] = ake('theme_advanced_more_colors', $data);
        return is_array($data) ? $data : array();
    }

    //}}}
}
