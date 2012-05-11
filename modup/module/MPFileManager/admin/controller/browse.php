<?php

/*
// user skipped to a different path
if (eka($_GET, 'p'))
{
    $p = $_GET['p'] === '/' ? '' : $_GET['p'];
    header('Location: /admin/module/MPFileManager/browse'.$p.'/');
    exit;
}

// user switched view modes
if (!eka($_SESSION, 'MPFileManager', 'view_mode'))
{
    $_SESSION['MPFileManager']['view_mode'] = MPFileManager::VIEW_GRID;
}
if (eka($_GET, 'v'))
{
    switch ($_GET['v'])
    {
        case MPFileManager::VIEW_GRID:
            $_SESSION['MPFileManager']['view_mode'] = MPFileManager::VIEW_GRID;
        break;
        case MPFileManager::VIEW_LIST:
            $_SESSION['MPFileManager']['view_mode'] = MPFileManager::VIEW_LIST;
        break;
        default:
        break;
    }
    header('Location: '.URI_PATH);
    exit;
}

$path = MPFileManager::file_path();
if (URI_PARTS > 4)
{
    $path .= '/'.str_replace('/admin/module/MPFileManager/browse/', '', URI_PATH);
}
$web_path = MPFileManager::web_path();

try
{
    $files = MPFileManager::scan($path);
    $message = '';
}
catch (MPFileManagerIsFileException $e)
{
    $files = array();
    $message = 'The path specified is a file, not a directory.';
}
catch (MPFileManagerNotExistException $e)
{
    $files = array();
    $message = 'The path does not exist.';
}

//{{{ layout
$dirs = MPFileManager::dir_scan($path);
$layout = new MPField();
$layout->add_layout(
    array(
        'field' => MPField::layout('file'),
        'name' => 'file',
        'type' => 'file'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'dropdown',
            array(
                'data' => array(
                    'options' => $dirs
                )
            )
        ),
        'name' => 'dir',
        'type' => 'dropdown'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit_reset',
            array(
                'submit' => array(
                    'text' => 'Save'
                )
            )
        ),
        'name' => 'submit',
        'type' => 'submit_reset'
    )
);
$layout->add_layout(
    array(
        'field' => MPField::layout(
            'submit',
            array(
                'data' => array(
                    'text' => 'Browse'
                )
            )
        ),
        'name' => 'browse',
        'type' => 'submit'
    )
);
//}}}
//{{{ make form
$form_file = new MPFormBuilderRows;
$form_file->attr = array(
    'method' => 'post',
    'action' => URI_PATH
);
$form_file->add_group(
    array(
        'rows' => array(
            array(
                'label' => array(
                    'text' => 'File'
                ),
                'fields' => $layout->get_layout('file'),
            )
        )
    )
);
$form_file->add_group(
    array(
        'rows' => array(
            array(
                'fields' => $layout->get_layout('submit'),
            ),
        )
    ),
    'form'
);
$ffh = $form_file->build();

if ($dirs)
{
    $form_dir = new MPFormBuilderRows;
    $form_dir->attr = array(
        'method' => 'post',
        'action' => URI_PATH
    );
    $form_dir->add_group(
        array(
            'rows' => array(
                array(
                    'label' => array(
                        'text' => 'Skip to a directory'
                    ),
                    'fields' => $layout->get_layout('dir'),
                )
            )
        )
    );
    $form_dir->add_group(
        array(
            'rows' => array(
                array(
                    'fields' => $layout->get_layout('browse'),
                ),
            )
        ),
        'form'
    );
    $fdh = $form_dir->build();
}
else
{
    $fdh = '';
}

//}}}
*/

?>
