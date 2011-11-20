<?php

if (!defined('URI_PART_4') || !is_numeric(URI_PART_4))
{
    header('Location: /admin/Sitemap/generator/');
    exit;
}

$set = Doctrine::getTable('SitemapEntry');
$parent_entry = $set->find(URI_PART_4);

$cett = Doctrine::getTable('ContentEntryType');
$cemt = Doctrine::getTable('ContentEntryMeta');
$types = $cett->getTypes();
$options['type'] = array('' => 'None');
$options['entry']['None'] = $options['type'];
$mpost = array();
foreach ($types as $type)
{
    $options['type'][$type['id']] = $type['name'];
    try
    {
        $metas = $cemt->filterByType($type['name']);
        foreach ($metas['data'] as $entry)
        {
            $options['entry'][$type['name']][$entry['id']] = $entry['title'];
            $mpost[$type['id']][$entry['id']] = $entry['slug'];
        }
    }
    catch (Exception $e)
    {
    }
}

// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $options['type']
                )
            )
        ),
        'name' => 'ctypes',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $options['entry']
                )
            )
        ),
        'name' => 'centries',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'custom',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Add Child'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
// {{{ submission
if (isset($_POST['do']))
{
    $sitemap = $layout->acts('post', $_POST['sitemap']);
    if (is_numeric($sitemap['ctypes']))
    {
        $entry = new SitemapEntry();
        $entry->level = $parent_entry->level + 1;
        $entry->parent_id = $parent_entry->id;
        $entry->assoc_id = $sitemap['ctypes'];
        $entry->assoc_name = 'ContentEntryType';
        $entry->build_field = array('slug');
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
    elseif (is_numeric($sitemap['centries']))
    {
        $entry = new SitemapEntry();
        $entry->level = $parent_entry->level + 1;
        $entry->parent_id = $parent_entry->id;
        $entry->assoc_id = $sitemap['centries'];
        $entry->assoc_name = 'ContentEntryMeta';
        $entry->build_field = array('slug');
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
    elseif (strlen($sitemap['custom']))
    {
        $entry = new SitemapEntry();
        $entry->level = $parent_entry->level + 1;
        $entry->parent_id = $parent_entry->id;
        $entry->assoc_id = 0000;
        $entry->slug = slugify($sitemap['custom']);
        if ($entry->isValid())
        {
            $entry->save();
        }
        $entry->free();
    }
}

// }}}
// {{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Entry Type'
                ),
                'description' => array(
                    'text' => 'All Entries of type'
                ),
                'fields' => $layout->get_layout('ctypes')
            ),
            array(
                'label' => array(
                    'text' => 'Entry Title'
                ),
                'description' => array(
                    'text' => 'Specific Entry Slug'
                ),
                'fields' => $layout->get_layout('centries')
            ),
            array(
                'label' => array(
                    'text' => 'Custom Slug'
                ),
                'fields' => $layout->get_layout('custom')
            )
        )
    ),
    'sitemap'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit')
            )
        )
    ),
    'do'
);

// }}}

?>
