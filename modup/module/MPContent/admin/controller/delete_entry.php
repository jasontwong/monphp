<?php

MPAdmin::set('title', 'Delete Entry');
MPAdmin::set('header', 'Delete Entry');

$spec = array(
    'select' => array(
        'em.id', 'ety.id as entry_type_id', 
    )
);
$et = MPContent::get_entry_type_by_entry_id(URI_PART_4, $spec);
$entry_type_id =& $et['entry_type_id'];
/*
$cemt = Doctrine::getTable('MPContentEntryMeta');
$entry = $cemt->findCurrentEntryTitle(URI_PART_4);
$cett = Doctrine::getTable('MPContentEntryType');
$entry_type = $cett->find($entry['content_entry_type_id']);
*/
if ($user_access = MPUser::has_perm('edit content entries type', 'edit content entries type-'.$entry_type_id))
{
    $user_access_level = MPContent::ACCESS_EDIT;
}
elseif ($user_access = MPUser::has_perm('view content entries type', 'view content entries type-'.$entry_type_id))
{
    $user_access_level = MPContent::ACCESS_VIEW;
}
else
{
    $user_access_level = MPContent::ACCESS_DENY;
}

$module_access_level = MPModule::h('content_entry_edit_access', MPModule::TARGET_ALL, $entry_type_id, URI_PART_4);
$access_level = max($module_access_level, $user_access_level);

if ($access_level !== MPContent::ACCESS_EDIT)
{
    MPAdmin::set('title', 'Permission Denied');
    MPAdmin::set('header', 'Permission Denied');
    $dfh = '';
    return;
}

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
//{{{ form submission
if (isset($_POST['confirm']))
{
    $confirm = $layout->acts('post', $_POST['confirm']);
    if ($confirm['do'])
    {
        $lookup_specs = array(
            'select' => array('em.content_entry_type_id as id')
        );
        $ety = MPContent::get_entry_type_by_entry_id($confirm['id'], $lookup_specs);
        $meta['content_entry_meta_id'] = $confirm['id'];
        $meta['content_entry_type_id'] = $ety['id'];
        MPModule::h('content_entry_delete_start', MPModule::TARGET_ALL, $meta);
        MPContent::delete_entry_by_id($confirm['id']);
        
        //{{{ MPCache: updating block
        $entry_meta_id = $meta['content_entry_meta_id'];
        $entry_type_id = $meta['content_entry_type_id'];
        $content_type = MPContent::get_entry_type_details_by_id($entry_type_id);
        $content_type_name = $content_type['type']['name'];

        // MPCache: update single entry
        MPCache::remove('entry:'.$entry_meta_id, 0, 'MPContent');

        // MPCache: update all entries for content type
        $entries = MPContent::get_entries_details_by_type_id($entry_type_id, array(), FALSE);
        MPCache::set($content_type_name.' - entries', $entries, 0, 'MPContent');

        // MPCache: update ids slugs map for content type
        $ids_slugs = MPContent::get_entries_slugs($content_type_name, FALSE);
        MPCache::set($content_type['type']['name'].' - ids slugs', $ids_slugs, 0, 'MPContent');
        //}}}
        MPModule::h('content_entry_delete_finish', MPModule::TARGET_ALL, $meta);

        header('Location: /admin/module/MPContent/edit_entries/');
        exit;
    }
    else
    {
        header('Location: /admin/module/MPContent/edit_entry/'.$confirm['id'].'/');
        exit;
    }
}

//}}}
//{{{ form build
$form = new MPFormRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('id')
            ),
            array(
                'fields' => $layout->get_layout('do')
            ),
        )
    ),
    'confirm'
);
$dfh = $form->build();

//}}}

?>
