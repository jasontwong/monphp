<?php

if (!MPUser::perm('edit content type'))
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    return;
}

MPAdmin::set('title', 'Delete MPContent Type');
MPAdmin::set('header', 'Delete MPContent Type');

// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_confirm'),
        'name' => 'do',
        'type' => 'submit_confirm'
    )
);

// }}}
//{{{ type form submission
if (isset($_POST['confirm']))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        /*
        function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }
        */
        //$s = microtime_float();
        MPContent::delete_entry_type_by_id(URI_PART_4);
        //$e = microtime_float();
        //var_dump($s, $e, ($e - $s));
        header('Location: /admin/module/MPContent/new_type/');
        exit;
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_type/'.$confirm['id'].'/');
        exit;
    }
}

//}}}
$type = MPContent::get_entry_type_by_id(
    URI_PART_4,
    array('select' => 'ety.name')
);
//{{{ type form build
$cform = new MPFormRows;
$cform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$cform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('id')
            ),
            array(
                'fields' => $layout->get_layout('do')
            )
        )
    ),
    'confirm'
);
$cfh = $cform->build();

//}}}

?>

<p class='warning'>Are you sure you want to delete the &ldquo;<?php echo htmlentities($type['name']) ?>&rdquo; type? This can not be undone.</p>

<?php echo $cfh ?>
