<?php
// {{{ prep
MPAdmin::set('title', 'Delete Entry');
MPAdmin::set('header', 'Delete Entry');
$entry = MPContent::get_entry_by_id(URI_PART_4);
if (is_null($entry))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry does not exist');
    header('Location: /admin/');
    exit;
}
$entry_type = MPContent::get_entry_type_by_name($entry['entry_type']['name']);
if (is_null($entry_type))
{
    MPAdmin::notify(MPAdmin::TYPE_ERROR, 'That entry does not belong to the entry type: ' . $entry_type['nice_name']);
    header('Location: /admin/');
    exit;
}
if ($user_access = MPUser::has_perm('edit content entries type', 'edit content entries type-'.$entry_type['name']))
{
    $user_access_level = MPContent::ACCESS_EDIT;
}
elseif ($user_access = MPUser::has_perm('view content entries type', 'view content entries type-'.$entry_type['name']))
{
    $user_access_level = MPContent::ACCESS_VIEW;
}
else
{
    $user_access_level = MPContent::ACCESS_DENY;
}

$module_access_level = MPModule::h('mpcontent_entry_edit_access', MPModule::TARGET_ALL, $entry_type['name'], URI_PART_4);
$access_level = max($module_access_level, $user_access_level);

if ($access_level !== MPContent::ACCESS_EDIT)
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    $dfh = '';
    return;
}
// }}}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_confirm'),
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
            MPModule::h('mpcontent_entry_delete_start', MPModule::TARGET_ALL, $entry);
            MPContent::delete_entry_by_id($entry['_id']);
            MPModule::h('mpcontent_entry_delete_finish', MPModule::TARGET_ALL, $entry);
            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'The entry was successfully deleted');
            header('Location: /admin/module/MPContent/edit_entries/');
            exit;
        }
        catch (Exception $e)
        {
            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'There was an error deleting the entry');
        }
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_entry/' . URI_PART_4 . '/');
        exit;
    }
}
// }}}
// {{{ form build
$form = new MPFormRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            ),
        )
    ),
    'confirm'
);
$dfh = $form->build();
// }}}
