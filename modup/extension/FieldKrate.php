<?php

class ExtensionFieldKrate
{
    //{{{ properties
    public $type = 'field';

    //}}}
    //{{{ public static function field_layout_color()
    public static function field_layout_color()
    {
        return array(
            'data' => array(
                'attr' => array(
                    'class' => 'text color',
                    'type' => 'text'
                ),
                'element' => Field::ELEMENT_INPUT,
            )
        );
    }

    //}}}
    //{{{ public static function field_public_color()
    public static function field_public_color()
    {
        return array(
            'description' => 'Color chosen by a popup color picker',
            'meta' => FALSE,
            'name' => 'Color',
        );
    }

    //}}}
}

?>
