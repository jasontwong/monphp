<?php

class FetchField
{
    //{{{ public static function field_layout_products()
    public static function field_layout_products()
    {
        $fetch = new Fetch();
        $products_xml = $fetch->get_products();
        $options = array('' => 'None');
        foreach ($products_xml->children() as $item)
        {
            $options[(string)$item->sku] = (string)$item->name;
        }
        return array(
            'data' => array(
                'element' => Field::ELEMENT_SELECT,
                'options' => $options
            )
        );
    }
    //}}}
    //{{{ public static function field_public_products()
    public static function field_public_products()
    {
        return array(
            'description' => 'List of products from the fetch account',
            'meta' => FALSE,
            'name' => 'Fetch Products',
        );
    }
    //}}}
}

?>
