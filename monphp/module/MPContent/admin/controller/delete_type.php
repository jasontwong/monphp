<?php
// {{{ prep
if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Delete Content Type');
MPAdmin::set('header', 'Delete Content Type');

$entry_type = MPContent::get_entry_type_by_name(URI_PART_4);
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
            $success = MPContent::delete_entry_type_by_name(URI_PART_4);
            MPAdmin::notify(MPAdmin::TYPE_SUCCESS, 'The entry type was successfully deleted');
            header('Location: /admin/module/MPContent/new_type/');
            exit;
        }
        catch (Exception $e)
        {
            MPAdmin::notify(MPAdmin::TYPE_ERROR, 'There was a problem deleting the entry type');
            header('Location: /admin/module/MPContent/edit_type/' . $entry_type['name'] . '/');
            exit;
        }
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_type/' . $entry_type['name'] . '/');
        exit;
    }
}

// }}}
// {{{ form build
$cform = new MPFormRows;
$cform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$cform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('do')
            ),
        ),
    ),
    'confirm'
);
$cfh = $cform->build();
// }}}
