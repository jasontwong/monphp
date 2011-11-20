<?php

class InventoryField
{
    //{{{ public static function field_layout_inventory($meta = array())
    public static function field_layout_inventory($meta = array())
    {
        $product_group_id = deka(NULL, $meta, 'data', 'meta', 0);
        $attr = array(
            'type' => 'hidden',
            'class' => 'hidden inventory-grid'
        );
        if (!is_null($product_group_id))
        {
            $product_group = Inventory::get_product_group($product_group_id);
            $attr['data-options-x'] = $product_group['ogx_id'];
            $attr['data-options-y'] = $product_group['ogy_id'];
            $attr['data-group-id'] = $product_group['id'];
            $attr['data-product'] = NULL;
        }
        return array(
            'data' => array(
                'element' => Field::ELEMENT_INPUT,
                'attr' => $attr
            )
        );
    }
    //}}}
    //{{{ public static function field_fieldtype_inventory($data)
    public static function field_fieldtype_inventory($data)
    {
        var_dump(func_get_args());
        /*
        if (is_array($data))
        {
            return array(
                array(
                    'name' => 'data',
                    'meta' => array(
                        'options' => array_combine($data['data'], $data['data'])
                    ),
                    'default_data' => deka('', $data, 'default_data')
                ),
            );
        }
        */
    }
    //}}}
    //{{{ public static function field_public_inventory()
    public static function field_public_inventory()
    {
        return array(
            'description' => 'Tabular data with customizable axis options',
            'meta' => TRUE,
            'name' => 'Inventory',
        );
    }
    //}}}
    //{{{ public static function field_meta_inventory()
    public static function field_meta_inventory()
    {
        $groups = Inventory::get_product_groups();
        $field = '<select>';
        foreach ($groups as $group)
        {
            $field .= "<option value='".$group['id']."'>".htmlspecialchars($group['name'])."</option>";
        }
        $field .= '</select>';
        return array(
            'data' => array(
                'description' => 'Which product group? This determines the set of product options you can set quantities on.',
                'extra' => array(),
                'field' => $field,
                'type' => 'dropdown'
            )
        );
    }
    //}}}
    //{{{ public static function field_post_inventory($key, $data = array())
    public static function field_post_inventory($key, $data = array())
    {
        $data = $data['data'];
        return array(
            'options_x' => json_decode($data['options_x']),
            'options_y' => json_decode($data['options_y']),
            'inventory' => json_decode($data['inventory']),
            'group_id' => $data['group_id'],
            'product_id' => $data['product_id']
        );
    }
    //}}}
    //{{{ public static function field_save_inventory($key, $data = array(), $meta = array())
    public static function field_save_inventory($key, $data = array(), $meta = array())
    {
        $data = self::field_post_inventory($key, $data);
        $p = func_get_args();
        $data['product_name'] = $meta['title'];
        $data['x'] = &$data['options_x'];
        $data['y'] = &$data['options_y'];
        if (eka($data, 'product_id') && strlen($data['product_id']) && Inventory::product_exists($data['product_id']))
        {
            $id = $data['product_id'];
            Inventory::update_product_and_inventory($id, $data);
        }
        else
        {
            $id = Inventory::save_product_and_inventory($data);
        }
        $result = array(
            'data' => array(
                array(
                    'cdata' => $id,
                    'akey' => 0,
                    'meta' => array()
                )
            )
        );
        return $result;
    }
    //}}}
}

?>
