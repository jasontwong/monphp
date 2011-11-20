<?php

if (!User::perm('edit gallery'))
{
    Admin::set('title', 'Permission Denied');
    Admin::set('header', 'Permission Denied');
    return;
}

Admin::set('title', 'Edit Album');
Admin::set('header', 'Edit Album');

// {{{ data prep
$misc_data = $album = array();
$gat = Doctrine::getTable('GalleryAlbum');
$gt = $gat->find(URI_PART_4);

// }}}
//{{{ form submission
if (isset($_POST['album']))
{
    $album = Field::acts('post', $_POST['album'], 'album');
    if ($album['delete'])
    {
        $gt->delete();
        header('Location: /admin/module/gallery/manage/');
        exit;
    }
    $misc_data = Field::acts('post', $_POST['misc_data']);
    $album['status'] = $album['status'] ? Gallery::UNLISTED : Gallery::LISTED;
    $album['slug'] = slugify($album['name']);
    $album['misc_data'] = $misc_data;
    if ($album['cover_image'] === FALSE)
    {
        $album['cover_image'] = array('tmp_name' => '');
    }
    
    $gt->merge($album);
    if ($gt->isValid())
    {
        $gt->save();
    }
    else
    {
        if ($album['cover_image']['tmp_name'] !== '')
        {
            unlink($album['cover_image']['tmp_name']);
            unset($album['cover_image']);
        }
    }
}

//}}}
//{{{ build form
$form = new form(new form_builder_rows);
$form->attr = array(
    'method' => 'post',
    'action' => URI_PATH,
    'name' => 'edit_album',
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
                    Field::act('form', 'text', 'name', deka($gt->name, $album, 'name'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Description'
                ),
                'fields' => array(
                    Field::act('form', 'textarea', 'description', deka($gt->description, $album, 'description'))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Cover Image'
                ),
                'fields' => array(
                    Field::act('form', 'file', 'cover_image', deka(str_replace(DIR_FILE, '', $gt->cover_image['tmp_name']), $album, 'cover_image', 'tmp_name'), array(
                        'attr' => array(
                            'class' => 'thickbox'
                        )
                    ))
                )
            ),
            array(
                'label' => array(
                    'text' => 'Rank'
                ),
                'fields' => array(
                    Field::act('form', 'text', 'rank', deka($gt->rank, $album, 'rank'))
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
                    Field::act('form', 'checkbox_boolean', 'status', deka($gt->status, $album, 'status'))
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
    $action = Field::act('form', $mf['type'], $mf['name'], deka(deka($mf['value'], $gt->misc_data, $mf['name']), $misc_data, $mf['name']), deka(array(), $mf, 'extra'));
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

$form->add_group(
    array(
        'rows' => $misc_rows
    ),
    'misc_data'
);

// }}} 
// {{{ submit
$form->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'Delete Album?'
                ),
                'fields' => array(
                    Field::act('form', 'checkbox_boolean', 'delete', '')
                )
            ),
            array(
                'fields' => Field::act('form', 'submit_reset', '', '')
            ),
        )
    ),
    'album'
);

// }}} 
$fh = $form->build();

//}}}

?>
