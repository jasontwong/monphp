<?php

/*
if (!User::perm('edit content'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}
*/

Admin::set('title', 'Edit Entries');
// Admin::set('header', 'Edit Entries');

// {{{ data prep
$ordering = FALSE;
$limits = array(10,25,50,100);
$limits = array_combine($limits, $limits);
$limits += array(0 => 'all');

$types = array('' => 'show all types');
$entry_types = Content::get_entry_types(array(), array('select' => array('ety.id', 'ety.name')));
foreach($entry_types as $type)
{
    $types[$type['id']] = $type['name'];
}
$order_opts = array('modified DESC', 'modified ASC', 'title DESC', 'title ASC');
$keys = array('limit', 'type', 'order', 'query');
$filter = array_fill_keys($keys, '');

// }}}
// {{{ layout
$layout = new Field();
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $limits
                )
            )
        ),
        'name' => 'limit',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $types
                )
            )
        ),
        'name' => 'type',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => array_combine($order_opts, $order_opts)
                )
            )
        ),
        'name' => 'order',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('text'),
        'name' => 'query',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => Field::layout('submit_reset'),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);

// }}}
// {{{ filter
if (isset($_GET['filter']))
{
    $filter = array_merge($filter, $layout->acts('post', $_GET['filter']));
    $layout->merge($_GET['filter']);
}
$cemt = Doctrine::getTable('ContentEntryMeta');
if (!strlen($filter['order']))
{
    $filter['order'] = 'modified DESC';
}
if (is_numeric($filter['type']))
{
    $type = Content::get_entry_type_by_id($filter['type']);
    $ordering = $type['ordering'];
    $entries_query = $cemt->queryTypeEntries($filter['type'], $ordering && $filter['limit'] == 0, $filter['order']);
}
else
{
    $ordering = FALSE;
    $entries_query = $cemt->queryAllEntries($filter['order']);
}
if (strlen($filter['query']))
{
    $spec = array(
        'select' => array('eti.content_entry_meta_id as id')
    );
    $rows = Content::search_entry_title_by_title($filter['query'], $spec);
    $ids = array();
    foreach ($rows as $row)
    {
        $ids[] = $row['id'];
    }
    if (empty($ids))
    {
        echo 'No results for: '.$filter['query'];
    }
    $entries_query->andWhereIn('em.id', $ids);
}

if ($filter['limit'] != 0)
{
    $page = isset($filter['page'])
        ? $filter['page']
        : 1;
    $limit = is_numeric($filter['limit'])
        ? $filter['limit']
        : 25;
    $entries_query->offset(($page - 1) * $limit)->limit($limit);
}
$entries = $entries_query->fetchArray();

// }}}
// {{{ form build
$form = new FormBuilderRows;
$form->attr = array(
    'action' => URI_PATH,
    'method' => 'get',
    'id' => 'entry-search'
);
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('limit'),
            ),
            array(
                'fields' => $layout->get_layout('type'),
            ),
            array(
                'fields' => $layout->get_layout('order'),
            ),
            array(
                'fields' => $layout->get_layout('query'),
            ),
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'filter'
);

// }}}

?>
