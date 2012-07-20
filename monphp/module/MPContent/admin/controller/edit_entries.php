<?php

// {{{ prep
MPAdmin::set('title', 'Edit Entries');
mp_enqueue_script(
    'mpcontent_entries',
    '/admin/static/MPContent/entries.js',
    array('jquery-ui-sortable'),
    FALSE,
    TRUE
);
$ordering = FALSE;
$limits = array(10,25,50,100);
$limits = array_combine($limits, $limits) + array(0 => 'all');


$types = array('' => 'show all types');
$entry_types = MPContent::get_entry_types();
foreach($entry_types as $type)
{
    $types[$type['name']] = $type['nice_name'];
}
$order_opts = array('modified DESC', 'modified ASC', 'title DESC', 'title ASC');
$keys = array('limit', 'type', 'order', 'query');
$filter = array_fill_keys($keys, '');
// }}}
// {{{ layout
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout(
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
        'field' => MPField::layout(
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
        'field' => MPField::layout(
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
        'field' => MPField::layout('text'),
        'name' => 'query',
        'type' => 'text'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout('submit_reset'),
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
$query = array();
if (strlen($filter['type']))
{
    $query['entry_type.name'] = $filter['type'];
    $entry_type = MPContent::get_entry_type_by_name($filter['type']);
    if (!is_null($entry_type))
    {
        $ordering = $entry_type['ordering'];
    }
}
/* TODO search implementation
if (strlen($filter['query']))
{
    $spec = array(
        'select' => array('eti.content_entry_meta_id as id')
    );
    $rows = MPContent::search_entry_title_by_title($filter['query'], $spec);
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
*/
$entries = MPContent::get_entries($query);
$entries->sort(array(
    'weight' => 1,
    'modified' => -1, 
    '_id' => -1, 
    'title' => 1
));
if ($filter['limit'] != 0)
{
    $page = isset($filter['page'])
        ? $filter['page']
        : 1;
    $limit = is_numeric($filter['limit'])
        ? $filter['limit']
        : 25;
    $entries->skip(($page - 1) * $limit)->limit($limit);
}
// }}}
// {{{ form build
$form = new MPFormRows;
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
            /*
            array(
                'fields' => $layout->get_layout('query'),
            ),
            */
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'filter'
);
// }}}
