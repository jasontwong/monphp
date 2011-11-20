<?php

$snippet = Doctrine_Query::create()
           ->from('SnippetRegion')
           ->select('name, description')
           ->where('id = ?', URI_PART_4)
           ->orderBy('name ASC')
           ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);

Admin::set('title', 'Delete Snippet Region &ldquo;'.$snippet['name'].'&rdquo;');
Admin::set('header', 'Delete Snippet Region &ldquo;'.$snippet['name'].'&rdquo;');

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
        Doctrine_Query::create()
            ->delete('SnippetRegion')
            ->where('id = ?', $confirm['id'])
            ->execute();
    }
    header('Location: /admin/module/Snippets/');
    exit;
}
//}}}
//{{{ form build
$cform = new FormBuilderRows;
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
