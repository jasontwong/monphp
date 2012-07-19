<?php
// {{{ prep
if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);

if (is_null($entry_type))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry type does not exist');
    header('Location: /admin/');
    exit;
}
$entry_field_group = array();
$entry_field_group_key = '';
foreach ($entry_type['field_groups'] as $k => &$fg)
{
    if (URI_PART_5 === $fg['name'])
    {
        $entry_field_group_key = $k;
        $entry_field_group = &$fg;
        break;
    }
}
$header = 'Delete Field Group &ldquo;' . $entry_field_group['nice_name'] . '&rdquo;';
MPAdmin::set('title', $header);
MPAdmin::set('header', $header);
// }}}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit_confirm',
            array(
                'submit' => array(
                    'text' => 'Delete this field group'
                ),
                'cancel' => array(
                    'text' => 'Cancel'
                )
            )
        ),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);
// }}}
// {{{ form submission
if (isset($_POST['confirm']))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        try
        {
            unset($entry_type['field_groups'][$entry_field_group_key]);
            MPContent::save_entry_type($entry_type);
            foreach ($entry_field_group['fields'] as &$field)
            {
                MPField::deregister_field($field['_id']);
            }
            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'The field group was successfully deleted');
            header('Location: /admin/module/MPContent/edit_type/' . URI_PART_4 . '/');
            exit;
        }
        catch (Exception $e)
        {
            MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was a problem deleting that field group');
        }
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_field_group/' . URI_PART_4 . '/' . URI_PART_5 . '/');
        exit;
    }
}
// }}}
// {{{ form build
$gform = new MPFormRows;
$gform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$gform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            )
        )
    ),
    'confirm'
);
$gfh = $gform->build();
// }}}
