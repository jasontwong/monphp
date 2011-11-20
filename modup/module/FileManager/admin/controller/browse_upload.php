<?php

$success = FALSE;
if (isset($_FILES['upload']))
{
    if ($_FILES['upload']['error'] == UPLOAD_ERR_OK)
    {
        $tmp_name = $_FILES['upload']['tmp_name'];
        if (!isset($_GET['upload_dir']))
        {
            unlink($tmp_name);
        }
        else
        {
            $file_manager = new FileManager();
            $success = $file_manager->save_file($_GET['upload_dir'], $_FILES['upload']['name'], $tmp_name);
        }
    }
}

?>
