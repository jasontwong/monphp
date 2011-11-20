<?php

Admin::set('title', 'Delete Entry');
Admin::set('header', 'Delete Entry');

$spec = array(
    'select' => array(
        'em.id', 'ety.id as entry_type_id', 
    )
);
$et = Content::get_entry_type_by_entry_id(URI_PART_4, $spec);
$entry_type_id =& $et['entry_type_id'];
/*
$cemt = Doctrine::getTable('ContentEntryMeta');
$entry = $cemt->findCurrentEntryTitle(URI_PART_4);
$cett = Doctrine::getTable('ContentEntryType');
$entry_type = $cett->find($entry['content_entry_type_id']);
*/
if ($user_access = User::has_perm('edit content entries type', 'edit content entries type-'.$entry_type_id))
{
    $user_access_level = Content::ACCESS_EDIT;
}
elseif ($user_access = User::has_perm('view content entries type', 'view content entries type-'.$entry_type_id))
{
    $user_access_level = Content::ACCESS_VIEW;
}
else
{
    $user_access_level = Content::ACCESS_DENY;
}

$module_access_level = Module::h('content_entry_edit_access', Module::TARGET_ALL, $entry_type_id, URI_PART_4);
$access_level = max($module_access_level, $user_access_level);

if ($access_level !== Content::ACCESS_EDIT)
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    $dfh = '';
    return;
}

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => URI_PART_4
        )
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_confirm'),
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
        $ety = Content::get_entry_type_by_entry_id($confirm['id'], $lookup_specs);
        $meta['content_entry_meta_id'] = $confirm['id'];
        $meta['content_entry_type_id'] = $ety['id'];
        Module::h('content_entry_delete_start', Module::TARGET_ALL, $meta);
        Content::delete_entry_by_id($confirm['id']);
        
        //{{{ Cache: updating block
        $entry_meta_id = $meta['content_entry_meta_id'];
        $entry_type_id = $meta['content_entry_type_id'];
        $content_type = Content::get_entry_type_details_by_id($entry_type_id);
        $content_type_name = $content_type['type']['name'];

        // Cache: update single entry
        Cache::remove('entry:'.$entry_meta_id, 0, 'Content');

        // Cache: update all entries for content type
        $entries = Content::get_entries_details_by_type_id($entry_type_id, array(), FALSE);
        Cache::set($content_type_name.' - entries', $entries, 0, 'Content');

        // Cache: update ids slugs map for content type
        $ids_slugs = Content::get_entries_slugs($content_type_name, FALSE);
        Cache::set($content_type['type']['name'].' - ids slugs', $ids_slugs, 0, 'Content');
        //}}}
        Module::h('content_entry_delete_finish', Module::TARGET_ALL, $meta);

        header('Location: /admin/module/Content/edit_entries/');
        exit;
    }
    else
    {
        header('Location: /admin/module/Content/edit_entry/'.$confirm['id'].'/');
        exit;
    }
}

//}}}
//{{{ form build
$form = new FormBuilderRows;
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
