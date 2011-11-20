<?php

if (!User::perm('edit gallery'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Manage Album Items');
Admin::set('header', 'Manage Album Items');

if (URI_PARTS < 5)
{
    header('Location: /admin/module/gallery/manage/');
    exit;
}
// {{{ data prep
$item = array();
$gat = Doctrine::getTable('GalleryAlbum');
$ga = $gat->find(URI_PART_4);
if ($ga === FALSE)
{
    header('Location: /admin/module/gallery/manage/');
    exit;
}
$album = $ga->toArray();

// }}}
// {{{ form submission
if (isset($_POST['items']))
{
}

// }}}
$ga->free();
unset($ga);
// {{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'add_item',
    'enctype' => 'multipart/form-data'
);

$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Title'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'title', deka('', $item, 'title'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Type'
                ),
                'fields' => array(
                    Field::act('form', 'dropdown', 'type', deka('', $item, 'type'), array(
                        'options' => Gallery::get_item_types()
                    ))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Upload new Image'
                ),
                'fields' => array(
                    Field::act('form', 'file', 'add', '')
                )
            )
        )
    ),
    'item'
);
// {{{ submit
$form->add_group(
    array(
        'rows' => array(
            array(
                'fields' => Field::act('form', 'submit_reset', '', '')
            ),
        )
    )
);

// }}} 
$fh = $form->build();

// }}}
// {{{ image prep
$git = Doctrine::getTable('GalleryItem');
$images = $git->findByAlbumId(URI_PART_4, Doctrine::HYDRATE_ARRAY);

// }}}

?>
