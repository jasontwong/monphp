<?php

class MPContentField
{
    //{{{ public static function field_layout_relationship($meta = array())
    public static function field_layout_relationship($meta = array())
    {
        $names = deka(array(), $meta, 'data', 'meta', 'content_type_name');
        $titles = $options = array();
        if (is_array($names) && $names)
        {
            $entries = MPDB::selectCollection('mpcontent_entry')
                ->find(array(
                    'entry_type.name' => array(
                        '$in' => $names,
                    ),
                ));
            // $entries = $cemt->queryTypeEntries($id)->fetchArray();
            foreach ($entries as $entry)
            {
                $titles[$entry['_id']->{'$id'}] = $entry['title'];
            }
        }
        asort($titles);
        $options += $titles;
        if (deka(FALSE, $meta, 'data', 'meta', 'ordering'))
        {
            $field = MPField::layout('list_double_ordered');
            $field['data']['options'] = $options;
            return $field;
        }
        else
        {
            $options += array('' => 'None');
            return array(
                'data' => array(
                    'element' => MPField::ELEMENT_SELECT,
                    'options' => $options
                )
            );
        }
    }

    //}}}
    //{{{ public static function field_meta_relationship()
    public static function field_meta_relationship()
    {
        $types = MPDB::selectCollection('mpcontent_entry_type')
            ->find(
                array(), 
                array('name' => TRUE, 'nice_name' => TRUE)
            );
        $options = array();
        foreach ($types as $type)
        {
            $options[$type['name']] = $type['nice_name'];
        }

        return array(
            'data' => array(
                'field' => MPField::layout(
                    'checkbox',
                    array(
                        'data' => array(
                            'options' => $options,
                        ),
                    )
                ),
                'type' => 'checkbox',
                'label' => 'Choose content types',
            ),
            'ordering' => array(
                'field' => MPField::layout(
                    'checkbox_boolean',
                    array(
                        'data' => array(
                            'text' => 'Allow Ordering?',
                        ),
                    )
                ),
                'type' => 'checkbox_boolean'
            ),
        );
    }

    //}}}
    //{{{ public static function field_public_relationship()
    public static function field_public_relationship()
    {
        return array(
            'description' => 'Related entry',
            'meta' => TRUE,
            'name' => 'Relationship',
        );
    }

    //}}}
}
