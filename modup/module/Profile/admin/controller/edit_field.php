<?php

if (!defined('URI_PART_4') || !is_numeric(URI_PART_4))
{
    header('Location: /admin/module/Profile/manage/');
    exit;
}

$pft = Doctrine::getTable('ProfileField');
$pf = $pft->find(URI_PART_4);

$types = array(
    'text', 'dropdown', 'textarea', 'radio', 'checkbox', 'date', 'checkbox_boolean'
);

$groups = Doctrine_Query::create()
    ->from('ProfileGroup g')
    ->orderBy('g.name ASC')
    ->fetchArray();

$group_options = array();
foreach ($groups as $group)
{
    $group_options[$group['id']] = $group['name'];
}

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'name',
        'type' => 'text',
        'value' => array(
            'data' => $pf->name
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => array_combine($types, $types)
                )
            )
        ),
        'name' => 'type',
        'type' => 'dropdown',
        'value' => array(
            'data' => $pf->type
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $group_options
                ),
            )
        ),
        'name' => 'profile_group_id',
        'type' => 'dropdown',
        'value' => array(
            'data' => $pf->profile_group_id
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'weight',
        'type' => 'text',
        'value' => array(
            'data' => $pf->weight
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea'),
        'name' => 'description',
        'type' => 'textarea',
        'value' => array(
            'data' => $pf->description
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('checkbox_boolean'),
        'name' => 'required',
        'type' => 'checkbox_boolean',
        'value' => array(
            'data' => $pf->required
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('textarea_array'),
        'name' => 'meta',
        'type' => 'textarea_array',
        'value' => array(
            'data' => $pf->meta['options']
        )
    )
);

// }}}
// {{{ post submission
if (isset($_POST['field']))
{
    $field = $layout->acts('post', $_POST['field']);
    $layout->merge($_POST['field']);
    if (!is_numeric($field['weight']))
    {
        $field['weight'] = 0;
    }
    if (!empty($field['meta']))
    {
        $field['meta'] = array(
            'options' => $field['meta']
        );
    }
    $pf->merge($field);
    if ($pf->isModified())
    {
        $pf->save();
    }
}

// }}}
// {{{ form build
$form = new FormBuilderRows();
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Name'
                ),
                'fields' => $layout->get_layout('name'),
            ),
            array(
                'label' => array(
                    'text' => 'Type'
                ),
                'fields' => $layout->get_layout('type'),
            ),
            array(
                'label' => array(
                    'text' => 'Group'
                ),
                'fields' => $layout->get_layout('profile_group_id'),
            ),
            array(
                'label' => array(
                    'text' => 'Weight'
                ),
                'fields' => $layout->get_layout('weight'),
            ),
            array(
                'label' => array(
                    'text' => 'Description'
                ),
                'fields' => $layout->get_layout('description'),
            ),
            array(
                'label' => array(
                    'text' => 'Required'
                ),
                'fields' => $layout->get_layout('required'),
            ),
            array(
                'label' => array(
                    'text' => 'Meta'
                ),
                'fields' => $layout->get_layout('meta'),
            ),
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'field'
);
    
// }}}

?>
