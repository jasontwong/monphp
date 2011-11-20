<?php

class EcommerceField
{
    //{{{ public static function field_layout_ecommerce_address()
    public static function field_layout_ecommerce_address()
    {
        return array(
            'name' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Name',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'company' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Company',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'address1' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Address 1',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'address2' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Address 2',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'country' => array(
                'attr' => array(
                    'class' => 'select EcommerceAddressCountry',
                ),
                'element' => Field::ELEMENT_SELECT,
                'options' => Ecommerce::get_paypal_countries(),
            ),
            'state' => array(
                'attr' => array(
                    'class' => 'text EcommerceAddressState',
                    'type' => 'text',
                    'placeholder' => 'State',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'city' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'City',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'phone' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Phone Number',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'zipcode' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Zipcode',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
        );
    }

    //}}}
    //{{{ public static function field_layout_ecommerce_product()
    public static function field_layout_ecommerce_product()
    {
        $fields = array(
            'id' => array(
                'attr' => array(
                    'class' => 'array_clear',
                    'type' => 'hidden',
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE,
            ),
            'name' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'Name',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'sku' => array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => 'SKU',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'weight' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductWeight',
                    'type' => 'text',
                    'placeholder' => 'Weight',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'price' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductPrice',
                    'type' => 'text',
                    'placeholder' => 'Price',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'quantity' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductQuantity',
                    'type' => 'text',
                    'placeholder' => 'Quantity',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'discount' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductDiscount',
                    'type' => 'text',
                    'placeholder' => 'Discount',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'tax' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductTax',
                    'type' => 'text',
                    'placeholder' => 'Tax',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'shipping' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductShipping',
                    'type' => 'text',
                    'placeholder' => 'Shipping',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
            'total' => array(
                'attr' => array(
                    'class' => 'text EcommerceProductTotal',
                    'type' => 'text',
                    'placeholder' => 'Total',
                ),
                'element' => Field::ELEMENT_INPUT,
            ),
        );

        foreach (Ecommerce::get_product_keys() as $key)
        {
            $fields['_'.$key] = array(
                'attr' => array(
                    'class' => 'text',
                    'type' => 'text',
                    'placeholder' => ucwords($key),
                ),
                'element' => Field::ELEMENT_INPUT,
            );
            $fields['_'.$key.'_id'] = array(
                'attr' => array(
                    'class' => 'array_clear',
                    'type' => 'hidden',
                ),
                'element' => Field::ELEMENT_INPUT,
                'hidden' => TRUE,
            );
        }

        return $fields;
    }

    //}}}
    //{{{ public static function field_post_ecommerce_address($key, $data)
    public static function field_post_ecommerce_address($key, $data)
    {
        return $data;
    }

    //}}}
    //{{{ public static function field_post_ecommerce_product($key, $data)
    public static function field_post_ecommerce_product($key, $data)
    {
        $values = array();
        foreach ($data as $k => $v)
        {
            foreach ($v as $index => $val)
            {
                if (strpos($k, '_') === 0)
                {
                    if (strpos($k, '_id') !== FALSE && strlen($k) > 3)
                    {
                        if (strlen($val[0]))
                        {
                            $values[$index]['Options'][str_replace('_id', '', substr($k, 1))]['id'] = $val[0];
                        }
                    }
                    else
                    {
                        $values[$index]['Options'][substr($k, 1)]['name'] = substr($k, 1);
                        $values[$index]['Options'][substr($k, 1)]['data'] = $val[0];
                    }
                }
                else
                {
                    $values[$index][$k] = $val[0];
                }
            }
        }
        foreach ($values as &$v)
        {
            if (!strlen($v['id']))
            {
                unset($v['id']);
            }
            if (ake('Options', $v))
            {
                sort($v['Options']);
            }
        }
        return $values;
    }

    //}}}
}

?>
