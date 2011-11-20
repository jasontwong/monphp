<?php 


Admin::set('title', 'Edit Taxonomy Term');
Admin::set('header', 'Edit Taxonomy Term');

if (!eka($_POST, 'term'))
{
    header('Location: /admin/');
    exit;
}

$scheme_id = $_POST['term']['id']['data'];
$tt = Doctrine::getTable('TaxonomyScheme');
$scheme = $tt->findOneById($scheme_id);
if (!User::has_perm('edit taxonomy', 'edit taxonomy-'.$scheme['id']))
{
    $sfh = '';
    $tfh = '';
    exit;
}
$taxm = new TaxonomyManager($scheme['module'], $scheme['mkey']);
$terms = $taxm->get_terms($scheme['name']);

//{{{ layout - terms
$tlayout = new Field();
if ($scheme['type'] == Taxonomy::TYPE_TREE)
{
    $options = array();
    foreach ($terms as $term)
    {
        $options[$term['id']] = $term;
    }
    $options = $taxm->arrange_terms($options, $scheme['type']);
    foreach ($options as &$option)
    {
        $option = '<a href="/admin/module/Taxonomy/edit_term/'.$option['id'].'">'.$option['term'].'</a>';
    }
    $tlayout->add_layout(
        array(
            'field' => Field::layout(
                'checkbox',
                array(
                    'data' => array(
                        'options' => $options
                    )
                )
            ),
            'name' => 'terms',
            'type' => 'checkbox',
        )
    );
}
$tlayout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => $scheme_id
        )
    )
);
//}}}

$udata = $tlayout->acts('post', $_POST['term']);

//{{{ layout - confirmation
$clayout = new Field();
$hiddens = array(
    'field' => Field::layout('hidden'),
    'name' => 'terms',
    'type' => 'hidden',
    'array' => TRUE,
);
foreach ($udata['terms'] as $term)
{
    $hiddens['value']['data'][] = $term;
}
$clayout->add_layout($hiddens);
$clayout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'confirm',
        'type' => 'hidden',
        'value' => array(
            'data' => 1
        )
    )
);
$clayout->add_layout(
    array(
        'field' => Field::layout('hidden'),
        'name' => 'id',
        'type' => 'hidden',
        'value' => array(
            'data' => $scheme_id
        )
    )
);
$clayout->add_layout(
    array(
        'field' => Field::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'attr' => array(
                        'value' => 'delete'
                    ),
                    'text' => 'Delete'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset',
    )
);
//}}}

$cdata = eka($_POST, 'term', 'confirm') 
    ? $clayout->acts('post', $_POST['term'])
    : NULL;

if (deka(FALSE, $cdata, 'confirm'))
{
    $ids = array();
    foreach ($cdata['terms'] as $terms)
    {
        foreach ($terms as $tid)
        {
            $ids[] = $tid;
        }
    }
    $taxm->remove_terms($ids);
    header('Location: /admin/module/Taxonomy/edit_scheme/'.$scheme_id.'/');
    exit;
}

//{{{ build
$cform = new FormBuilderRows;
$cform->attr = array(
    'action' => URI_PATH,
    'method' => 'post'
);
$cform->label = array(
    'text' => 'Delete Taxonomy Terms'
);
$crows['rows'][] = array(
    'fields' => $clayout->get_layout('confirm'),
    'hidden' => TRUE
);
$crows['rows'][] = array(
    'fields' => $clayout->get_layout('id'),
    'hidden' => TRUE
);
$crows['rows'][] = array(
    'fields' => $clayout->get_layout('terms'),
    'hiden' => TRUE
);
$cform->add_group($crows, 'term');
$cform->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $clayout->get_layout(
                    'submit'
                )
            )
        )
    ),
    'form'
);
$cfh = $cform->build();
//}}}

?>
