<?php

if (!User::perm('add gallery'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Add Album');
Admin::set('header', 'Add Album');

// {{{ data prep
$misc_rows = $misc_data = $album = array();

// }}}
//{{{ form submission
if (isset($_POST['album']))
{
    $album = Field::acts('post', $_POST['album'], 'album');
    $album['misc_data'] = $misc_data = isset($_POST['misc_data']) ? Field::acts('post', $_POST['misc_data']) : array();
    $album['status'] = $album['status'] ? Gallery::UNLISTED : Gallery::LISTED;
    $album['slug'] = slugify($album['name']);
    if ($album['cover_image'] === FALSE)
    {
        $album['cover_image'] = array('tmp_name' => '');
    }
    
    $new_album = new GalleryAlbum();
    $new_album->merge($album);
    if ($new_album->isValid())
    {
        $new_album->save();
        if (User::perm('edit gallery'))
        {
            header('Location: /admin/module/gallery/edit_album/'.$new_album->id);
            exit;
        }
        else
        {
            header('Location: /admin/module/gallery/add_album/');
            exit;
        }
    }
    else
    {
        if ($album['cover_image']['tmp_name'] !== '')
        {
            unlink($album['cover_image']['tmp_name']);
            unset($album['cover_image']);
        }
    }

    $new_album->free();
    unset($new_album);
}

//}}}
//{{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'add_album',
    'enctype' => 'multipart/form-data'
);
//{{{ fields
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Name'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'name', deka('', $album, 'name'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Description'
                ),
                'fields' => array(
                    Field::act('form', 'textarea', 'description', deka('', $album, 'description'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Cover Image'
                ),
                'fields' => array(
                    Field::act('form', 'file', 'cover_image', '')
                )
            ),
            array(
                'label' => array(
                    'text' => 'Rank'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'rank', deka(0, $album, 'rank'))
                ),
                'description' => array(
                    'text' => 'lower number comes first'
                )
            ),
            array(
                'label' => array(
                    'text' => 'Private?'
                ),
                'fields' => array(
                    Field::act('form', 'checkbox_boolean', 'status', deka(0, $album, 'status'))
                )
            ),
        )
    ),
    'album'
);

//}}}
// {{{ extra fields
$misc_fields = Gallery::get_misc_fields();
foreach($misc_fields as $mf)
{
    $action = Field::act('form', $mf['type'], $mf['name'], deka($mf['value'], $misc_data, $mf['name']), deka(array(), $mf, 'extra'));
    switch($mf['type'])
    {
        case 'submit_reset':
        case 'checkbox':
            $field = $action;
        break;
        default:
            $field = array($action);
    }
    $misc_rows[] = array(
        'label' => array(
            'text' => $mf['label']
        ),
        'fields' => $field,
        'description' => array(
            'text' => deka('', $mf, 'description')
        )
    );
}

if (count($misc_rows) > 0)
{
    $form->add_group(
        array(
            'rows' => $misc_rows
        ),
        'misc_data'
    );
}

// }}} 
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

//}}}

?>
