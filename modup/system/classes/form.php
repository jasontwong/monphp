<?php

//{{{ abstract class FormBuilder
abstract class FormBuilder
{
    //{{{ properties
    public $attr;
    public $label;
    public $groups = array();

    //}}}
    //{{{ abstract public function build()
    /** 
     * Creates the HTML for the form
     *
     * @return string
     */
    abstract public function build();

    //}}}
    //{{{ public function add_group($group, $key = NULL)
    /**
     * Adds the group array to the property for building later on
     *
     * @param array $group field group
     * @param string $key key for group fields
     * @return void
     */
    public function add_group($group, $key = NULL)
    {
        $this->groups[] = array(
            'key' => $key,
            'layout' => $group
        );
    }

    //}}}
    //{{{ protected function attr($attr, $extra = NULL)
    /**
     * Extracts the attribute array to proper HTML
     * The $extra parameter is to add additional attributes without mucking
     * around with the array being sent to this method. An example would be to
     * quickly add a class atrribute by passing array('class' => 'foo').
     *
     * @param array $attr attributes in $name => $value format
     * @param array $extra optional additional attributes to merge
     * @return string
     */
    protected function attr($attr, $extra = array())
    {
        $attr = $this->attr_merge($attr, $extra);
        if (count($attr))
        {
            foreach ($attr as $k => $v)
            {
                $value = is_array($v) ? implode(' ', $v) : $v;
                $a[] = htmlentities($k).'="'.htmlentities($value, ENT_QUOTES, 'UTF-8').'"';
            }
        }
        return isset($a) ? implode(' ', $a) : '';
    }

    //}}}
    //{{{ protected function attr_merge($attr, $extra)
    /** 
     * Merge attributes together
     * Works like array_merge() but append values instead of replacing. So if
     * There are multiple array values for class, it will append all the class
     * names into an array.
     *
     * @param array $attr
     * @param array $extra
     * @return array
     */
    protected function attr_merge($attr, $extra)
    {
        $result = array();
        if (is_null($attr))
        {
            return $result;
        }
        foreach ($attr as $k => $v)
        {
            // skip for multiple value option forms like select options
            if ($k === 'value' && is_array($v))
            {
                continue;
            }
            if (isset($extra[$k]))
            {
                $w = $extra[$k];
                if ((is_string($w) && strlen($w) > 0) || (is_array($w) && count($w)))
                {
                    $result[$k] = array($v, $extra[$k]);
                }
                else
                {
                    $result[$k] = $v;
                }
                unset($extra[$k]);
            }
            else
            {
                $result[$k] = $v;
            }
        }
        return count($extra) ? array_merge($result, $extra) : $result;
    }

    //}}}
    //{{{ protected function element($el, $value = NULL, $error = NULL)
    /**
     * Builds out just the field element along with an error message.
     * @param array $el element array
     * @param mixed $value value of the element. also looked in $el['attr']
     * @param string $error error message, if any
     * @return string
     */
    protected function element($el, $value = NULL, $error = NULL)
    {
        $text = deka('', $el, 'text');
        $label = deka(NULL, $el, 'label');
        $o = !is_null($label) ? '<div class="label">'.$label.'</div>' : '';
        switch ($el['element'])
        {
            case Field::ELEMENT_INPUT:
                if (eka($el, 'options') && (
                    $el['attr']['type'] === 'checkbox' ||
                    $el['attr']['type'] === 'radio'))
                    {
                        foreach ($el['options'] as $k => $v)
                        {
                            $attr = $el['attr'];
                            if ($attr['type'] === 'radio')
                            {
                                if ($k == $value)
                                {
                                    $attr['checked'] = 'checked';
                                }
                            }
                            else
                            {
                                $attr['name'] .= '[]';
                            }
                            if (is_array($value) && in_array($k, $value))
                            {
                                $attr['checked'] = 'checked';
                            }
                            // Why are we merging values? It automatically skips in attr_merge
                            $iattr = $this->attr($attr, array('value' => $k));
                            $o .= '<label class="option"><input '.$iattr.'>'.$v.'</label>';
                        }
                    }
                    else
                    {
                        $value = is_array($value)
                            ? array_pop($value)
                            : $value;
                        if ($el['attr']['type'] === 'checkbox')
                        {
                            if ($value)
                            {
                                $el['attr']['checked'] = 'checked';
                            }
                            $value = NULL;
                        }
                        /* Why are we merging values? Shouldn't the "new" value replace the old one?
                        $attr = is_null($value)
                            ? $this->attr($el['attr'])
                            : $this->attr($el['attr'], array('value' => $value));
                        */
                        if (!is_null($value))
                        {
                            $el['attr']['value'] = $value;
                        }
                        $attr = $this->attr($el['attr']);
                        $o .= $text 
                            ? '<label><input '.$attr.'>'.$text.'</label>'
                            : '<input '.$attr.'>';
                    }
            break;
            case Field::ELEMENT_TEXTAREA:
                $attr = $this->attr($el['attr']);
                if (is_array($value))
                {
                    $value = deka('', $el, 'attr', 'class') === 'textarea_array'
                        ? implode("\n", $value)
                        : array_pop($value);
                }
                $o .= '<textarea '.$attr.'>'.htmlentities($value, ENT_QUOTES, 'UTF-8').'</textarea>';
            break;
            case Field::ELEMENT_SELECT:
                $attr = $this->attr($el['attr']);
                $o .= '<select '.$attr.'>';
                if (eka($el, 'options'))
                {
                    $grouped = NULL;
                    foreach ($el['options'] as $k => $v)
                    {
                        if (is_null($grouped))
                        {
                            $grouped = is_array($v);
                        }
                        if ($grouped)
                        {
                            $o .= '<optgroup label="'.htmlentities($k, ENT_QUOTES, 'UTF-8').'">';
                            foreach ($v as $k => $val)
                            {
                                $selected = '';
                                if (!is_null($value) && (
                                    (is_array($value) && in_array($k, $value)) ||
                                    (is_string($value) && $k == $value)))
                                    {
                                        $selected = 'selected="selected" ';
                                    }
                                $o .= '<option '.$selected.'value="'.htmlentities($k, ENT_QUOTES, 'UTF-8').'">'.$val.'</option>';
                            }
                            $o .= '</optgroup>';
                        }
                        else
                        {
                            $selected = '';
                            if (!is_null($value) && (
                                (is_array($value) && in_array($k, $value)) ||
                                (is_string($value) && $k == $value)))
                                {
                                    $selected = 'selected="selected" ';
                                }
                            $o .= '<option '.$selected.'value="'.htmlentities($k, ENT_QUOTES, 'UTF-8').'">'.$v.'</option>';
                        }
                    }
                }
                $o .= '</select>';
            break;
            case Field::ELEMENT_BUTTON:
                $attr = $this->attr($el['attr']);
                $o .= '<button '.$attr.'>'.$text.'</button>';
            break;
            default:
                $o .= '';
            break;
        }
        if (!is_null($error))
        {
            $o .= '<div class="error">'.$error.'</div>';
        }
        return $o;
    }
    //}}}
    //{{{ protected function field($field, $key = NULL)
    /**
     * Create the needed HTML for the field
     * Returns an array, one with hidden inputs if needed, and the other with
     * the HTML field that will show. It will be up to the designer to figure
     * out which to place first. If there are no hidden input fields needed,
     * that array key will return an empty string.
     *
     * @param array $field array of field information
     * @return array
     */
    protected function field($field, $key = NULL)
    {
        $result = '';
        $fields = array();
        $groups = array();
        $array = deka(FALSE, $field, 'array');
        $value = deka(array(), $field, 'value');
        $option = deka(array(), $field, 'options');
        $error = deka(array(), $field, 'error');
        $before = deka(array(), $field, 'html_before');
        $after = deka(array(), $field, 'html_after');
        $hidden = deka(array(), $field, 'hidden');
        $name = deka('', $field, 'name');
        if ($array)
        {
            $max = $this->max_values($value);
            for ($i = 0; $i < $max; ++$i)
            {
                $groups = array();
                foreach ($field['field'] as $sub_name => $sub_field)
                {
                    $v = deka(NULL, $value, $sub_name, $i);
                    $o = deka(NULL, $option, $sub_name, $i);
                    $e = deka(NULL, $error, $sub_name, $i);
                    $b = deka('', $before, $sub_name, $i);
                    $a = deka('', $after, $sub_name, $i);
                    $h = deka(FALSE, $hidden, $sub_name, $i);
                    if ($h)
                    {
                        continue;
                    }
                    $combine = deka(FALSE, $sub_field, 'hidden');
                    if (!is_null($o))
                    {
                        $sub_field['options'] = $o;
                    }
                    if (!eka($sub_field, 'attr', 'name'))
                    {
                        $sub_field['attr']['name'] = $sub_name;
                    }
                    if ($name)
                    {
                        $sub_field['attr']['name'] = prepend_name($name, $sub_field['attr']['name']);
                    }
                    if ($key)
                    {
                        $sub_field['attr']['name'] = prepend_name($key, $sub_field['attr']['name']);
                    }
                    $sub_field['attr']['name'] .= '['.$i.'][]';
                    if ($combine)
                    {
                        if (!ake(0, $groups))
                        {
                            $groups[0] = '';
                        }
                        $groups[0] .= $b.$this->element($sub_field, $v, $e).$a;
                    }
                    else
                    {
                        $groups[] = $b.$this->element($sub_field, $v, $e).$a;
                    }
                }
                $fields[] = $groups;
            }
        }
        else
        {
            if ($field['field'])
            {
                foreach ($field['field'] as $sub_name => $sub_field)
                {
                    $v = deka(NULL, $value, $sub_name);
                    $o = deka(NULL, $option, $sub_name);
                    $e = deka(NULL, $error, $sub_name);
                    $b = deka('', $before, $sub_name);
                    $a = deka('', $after, $sub_name);
                    $h = deka(FALSE, $hidden, $sub_name);
                    if ($h)
                    {
                        continue;
                    }
                    $combine = deka(FALSE, $sub_field, 'hidden');
                    if (!is_null($o))
                    {
                        $sub_field['options'] = $o;
                    }
                    if (!eka($sub_field, 'attr', 'name'))
                    {
                        $sub_field['attr']['name'] = $sub_name;
                    }
                    if ($name)
                    {
                        $sub_field['attr']['name'] = prepend_name($name, $sub_field['attr']['name']);
                    }
                    if ($key)
                    {
                        $sub_field['attr']['name'] = prepend_name($key, $sub_field['attr']['name']);
                    }
                    if ($combine)
                    {
                        if (!ake(0, $groups))
                        {
                            $groups[0] = '';
                        }
                        $groups[0] .= $b.$this->element($sub_field, $v, $e).$a;
                    }
                    else
                    {
                        $groups[] = $b.$this->element($sub_field, $v, $e).$a;
                    }
                }
            }
            $fields = $groups;
        }
        return $fields;
    }

    //}}}
    //{{{ protected function max_values($values)
    /**
     * Looks to see the max number of values for fields.
     * Use this to check how many 'multiples' of fields is needed on forms with
     * the array flag set to TRUE.
     * @param array $values values array of the field structure
     * @return int
     */
    protected function max_values($values)
    {
        $max = 1;
        foreach ($values as $k => $value)
        {
            if (empty($value))
            {
                return $max;
            }
            $c = count(array_keys($value));
            if ($c > $max)
            {
                $max = $c;
            }
        }
        return $max;
    }
    //}}}
    //{{{ protected function name_id($name)
    /**
     * Converts html form name to an id for the label's for attribute
     *
     * @param string $name
     * @return string
     */
    protected function name_id($name)
    {
        if (substr($name, -1) === ']')
        {
            $name = substr($name, 0, -1);
        }
        $name = str_replace('][', '_', $name);
        $name = str_replace('[', '_', $name);
        $name = str_replace(']', '_', $name);
        $name = str_replace(' ', '_', $name);
        return $name;
    }

    //}}}
}

//}}}
//{{{ class FormBuilderRows
/**
 * Default form builder class used primarily in the back end
 */
class FormBuilderRows extends FormBuilder
{
    //{{{ protected function label($details, $attr = array())
    /**
     * Creates the label div 
     *
     * @param array $details the label details
     * @param array $attr additional attributes to use
     * @return string
     */
    protected function label($details, $attr = array())
    {
        if (ake('text', $details))
        {
            $dattr = deka(array(), $details, 'attr');
            $attr = $this->attr($dattr, $attr);
            $label = "<div {$attr}>{$details['text']}</div>\n";
        }
        else
        {
            $label = '';
        }
        return $label;
    }

    //}}}
    //{{{ public function build($data)
    public function build()
    {
        $form = "<form {$this->attr($this->attr)}><div class='form_wrapper'>\n";

        $label = $this->label($this->label, array('class' => 'form_label'));

        //{{{ loop groups
        foreach ($this->groups as $grouping)
        {
            $group = $grouping['layout'];
            $gkey = $grouping['key'];
            $rows = array();
            $gattr = $this->attr(
                deka(array(), $group, 'attr'), 
                array('class' => 'group')
            );
            $ghtml = "<div {$gattr}>\n";
            if (eka($group, 'label'))
            {
                $group['label']['attr']['class'][] = 'label';
                $lhtml = $this->label($group['label']);
            }
            else
            {
                $lhtml = '';
            }

            //{{{ loop fields
            foreach ($group['rows'] as $row)
            {
                $hidden_html = $rlhtml = $rdhtml = $rfhtml = '';
                $row['hidden'] = deka(FALSE, $row, 'hidden');

                $rattr = $this->attr(
                    deka(array(), $row, 'row', 'attr'),
                    array('class' => 'row')
                );
                $rhtml = "<div {$rattr}>\n";

                if (eka($row, 'label', 'text'))
                {
                    $rlattr = $this->attr(
                        deka(array(), $row, 'label', 'attr'),
                        array('class' => 'label')
                    );
                    $rltext = htmlentities($row['label']['text'], ENT_QUOTES, 'UTF-8');
                    $rlhtml = '<div '.$rlattr.'>'.$rltext.'</div>';
                }

                if (eka($row, 'description', 'text'))
                {
                    $rdattr = $this->attr(
                        deka(array(), $row, 'description', 'attr'),
                        array('class' => 'description')
                    );
                    $rdtext = htmlentities($row['description']['text'], ENT_QUOTES, 'UTF-8');
                    $rdhtml = '<div '.$rdattr.'>'.$rdtext.'</div>';
                }

                $rarray = deka(FALSE, $row, 'fields', 'array');
                $fsets = $this->field($row['fields'], $gkey);
                $rfhtml .= '<div class="fields fields_'.$row['fields']['type'].'">';
                $keys_last_field = array_keys($fsets);
                $num_last_field = empty($keys_last_field) ? 0 : max($keys_last_field);
                foreach ($fsets as $k => $fset)
                {
                    if ($rarray)
                    {
                        foreach ($fset as $set)
                        {
                            $rfhtml .= '<div class="field">'.$set.'</div>';
                        }
                        if ($num_last_field !== $k && $num_last_field !== 0)
                        {
                            $rfhtml .= '</div><div class="fields fields_'.$row['fields']['type'].' content_multiple_fields_additional">';
                        }
                    }
                    else
                    {
                        $rfhtml .= '<div class="field">'.$fset.'</div>';
                        if ($row['hidden'])
                        {
                            $hidden_html = $fset;
                        }
                    }
                }
                $rfhtml .= '</div>';
                $rows[] = strlen($hidden_html)
                    ? $hidden_html
                    : "${rhtml}\n${rlhtml}\n${rdhtml}\n${rfhtml}\n</div>\n";
            }

            //}}}
            $groups[] = $ghtml.$lhtml.implode($rows).'</div>';
        }

        //}}}

        $o = $form.$label.implode($groups).'</div></form>';
        return $o;
    }
    //}}}
}

//}}}

?>
