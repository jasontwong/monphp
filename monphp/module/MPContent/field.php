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
            ->find(array(), array('name' => TRUE, 'nice_name' => TRUE));
        $field = '';
        foreach ($types as $type)
        {
            $name = $type['name'];
            $nice_name = $type['nice_name'];
            $field .= "<label><input type='checkbox' value='{$name}' />{$nice_name}</label>";
        }

        return array(
            'data' => array(
                'description' => 'Choose the content type',
                'field' => $field,
                'label_field' => FALSE,
                'required_option' => FALSE,
                'type' => 'checkbox'
            ),
            'ordering' => array(
                'description' => '',
                'field' => "<label><input type='checkbox' value='1' /> Allow Ordering?</label>",
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
