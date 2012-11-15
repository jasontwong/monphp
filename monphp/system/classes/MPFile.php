<?php

class MPFile
{
    // {{{ public static function download_file($query = array(), $fields = array())
    /**
     * This function should send the file to the browser
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSFile 
     */
    public static function download_file($query = array(), $fields = array())
    {
        $file = MPDB::getGridFS(MP_GRIDFS)->findOne($query, $fields);
        /*
        header('Content-type: '.$v['mime']);
        header('Content-Length: '.$v['length']);
        echo $file->getBytes();
        */
        var_dump($file);
        return $file;
    }
    // }}}

    // {{{ public static function force_download_file($query = array(), $fields = array())
    /**
     * This function should send the file with content type attachment so
     * it forces the browser to send file as a download.
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSFile 
     */
    public static function force_download_file($query = array(), $fields = array())
    {
        $file = MPDB::getGridFS(MP_GRIDFS)->findOne($query, $fields);
        /*
        header('Content-type: '.$v['mime']);
        header('Content-Length: '.$v['length']);
        echo $file->getBytes();
        */
        var_dump($file);
        return $file;
    }
    // }}}

    // {{{ public static function get_file($query = array(), $fields = array())
    /**
     * This function should be used to get files from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSFile 
     */
    public static function get_file($query = array(), $fields = array())
    {
        return MPDB::getGridFS(MP_GRIDFS)->findOne($query, $fields);
    }
    // }}}
    // {{{ public static function get_files($query = array(), $fields = array())
    /**
     * This function should be used to get a file from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSCursor
     */
    public static function get_files($query = array(), $fields = array())
    {
        return MPDB::getGridFS(MP_GRIDFS)->find($query, $fields);
    }
    // }}}
    // {{{ public static function get_image($query = array(), $fields = array())
    /**
     * This function should be used to get an image from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return array
     */
    public static function get_image($query = array(), $fields = array())
    {
        $images = array();
        $grid_fs = MPDB::getGridFS(MP_GRIDFS);
        $image = $grid_fs->findOne($query, $fields);
        if (!is_null($image))
        {
            $images[$image['metadata']['size']] = $image;
            $query = array(
                'metadata.reference' => $image['_id'],
            );
            $sizes = $grid_fs->find($query);
            foreach ($sizes as $size)
            {
                $images[$size['metadata']['size']] = $size;
            }
        }
        return $images;
    }
    // }}}
    // {{{ public static function get_images($query = array(), $fields = array())
    /**
     * This function should be used to get images from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return array
     */
    public static function get_images($query = array(), $fields = array())
    {
        $all_images = array();
        $grid_fs = MPDB::getGridFS(MP_GRIDFS);
        $images = $grid_fs->find($query, $fields);
        foreach ($images as $image)
        {
            $data = array();
            $data[$image['metadata']['size']] = $image;
            $query = array(
                'metadata.reference' => $image['_id'],
            );
            $sizes = $grid_fs->find($query);
            foreach ($sizes as $size)
            {
                $data[$size['metadata']['size']] = $size;
            }
            $all_images[] = $data;
        }
        return $all_images;
    }
    // }}}

    // {{{ public static function remove_files($query = array())
    /**
     * This function should be used to remove files from the GridFS coillection
     * 
     * @param array $query
     * @return bool
     */
    public static function remove_files($query = array())
    {
        return MPDB::getGridFS(MP_GRIDFS)->remove($query, array('safe' => TRUE));
    }
    // }}}
    // {{{ public static function remove_images($query = array())
    /**
     * This function should be used to remove images from the GridFS coillection
     * 
     * @param array $query
     * @return bool
     */
    public static function remove_images($query = array())
    {
        $grid_fs = MPDB::getGridFS(MP_GRIDFS);
        $images = $grid_fs->find($query, $fields);
        $filenames = $ids = array()
        foreach ($images as $image)
        {
            $ids[] = $image['_id'];
            $filenames[] = $image['metadata']['location'];
        }
        $success = $grid_fs->remove($query, array('safe' => TRUE));
        if (!empty($ids))
        {
            $query = array(
                'metadata.reference' => array(
                    '$in' => $ids,
                ),
            ); 
            $images = $grid_fs->find($query, $fields);
            foreach ($images as $image)
            {
                $filenames[] = $image['metadata']['location'];
            }
            $grid_fs->remove($query, array('safe' => TRUE));
        }
        foreach ($filenames as &$file)
        {
            unlink($file);
        }
        return $success;
    }
    // }}}

    // {{{ public static function save_file($file, $filename, $meta = array())
    /**
     * This function should save the file to a GridFS, create multiple sizes if needed,
     * and add the proper metadata to the files.
     * 
     * @param string $file full path of the file on the server to save
     * @param array $meta additional metadata that needs to be added to the file
     * @return mixed MongoId if successful, else NULL
     */
    public static function save_file($file, $meta = array())
    {
        if (is_file($file))
        {
            $grid = MPDB::getGridFS(MP_GRIDFS);
            $grid->ensureIndex(
                array(
                    'files_id' => 1,
                    'n' => 1,
                ), 
                array(
                    'unique' => 1, 
                )
            );
            $stat = stat($file);
            $stat['nice_mtime'] = date('Y-m-d H:i:s', $stat['mtime']);
            $stat['nice_size'] = size_readable($stat['size']);
            $mime_type = finfo::file($file, FILEINFO_MIME_TYPE);
            $name = basename($file);
            $meta = array(
                'metadata' => array_merge(
                    $meta,
                    array(
                        'filename' => $name,
                        'mime' => $mime_type,
                        'location' => $file,
                        'stat' => $stat,
                    )
                ),
            );
            return $grid->storeFile($file, $meta, array('safe' => TRUE));
        }
        return NULL;
    }
    // }}}
    // {{{ public static function save_image($file, $filename, $meta = array(), $sizes = array())
    /**
     * This function should save the file to a GridFS, create multiple sizes if needed,
     * and add the proper metadata to the files.
     * 
     * @param string $file full path of the file on the server to save
     * @param array $meta additional metadata that needs to be added to the file
     * @param array $sizes an array of sizes to create besides the original
     * @return array the array of ids returned from GridFS
     */
    public static function save_image($file, $meta = array(), $sizes = array())
    {
        if (is_file($file))
        {
            list($width, $height, $mime_type) = getimagesize($file);
            $meta = array_merge(
                $meta,
                array(
                    'width' => $width,
                    'height' => $height,
                    'size' => 'original',
                )
            );
        }
        $id = self::save_file($file, $meta);
        $file_ids = array();
        if (!is_null($id))
        {
            $file_ids['original'] = $id;

            $quality = 90;
            $pinfo = pathinfo($name);
            $resized_path = dirname($filename);
            foreach ($sizes as $label => $size)
            {
                if ($size['width'] > 0 && $size['height'] > 0 && ($width > $size['width'] || $height > $size['height']))
                {
                    $ratio_orig = $width / $height;
                    if (($size['width'] / $size['height']) > $ratio_orig)
                    {
                       $size['width'] = $size['height'] * $ratio_orig;
                    } 
                    else 
                    {
                       $size['height'] = $size['width'] / $ratio_orig;
                    }
                    $image = imagecreatetruecolor($size['width'], $size['height']);
                    $resized_filename = $pinfo['filename'] . '-' . $label.'.'.$pinfo['extension'];
                    $resized_file = $resized_path . '/' . $resized_filename;
                    $orig_image = NULL;
                    switch ($mime_type)
                    {
                        case IMAGETYPE_GIF:
                            $orig_image = imagecreatefromgif($filename);
                            imagealphablending($image, false);
                            imagesavealpha($image, true);
                            imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                            imagegif($image, $resized_file);
                        break;
                        case IMAGETYPE_JPEG:
                            $orig_image = imagecreatefromjpeg($filename);
                            imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                            imagejpeg($image, $resized_file, $quality);
                        break;
                        case IMAGETYPE_PNG:
                            $orig_image = imagecreatefrompng($filename);
                            imagealphablending($image, false);
                            imagesavealpha($image, true);
                            imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                            if ( function_exists('imageistruecolor') && imageistruecolor( $orig_image ) )
                            {
                                imagetruecolortopalette( $image, false, imagecolorstotal( $orig_image ) );
                            }
                            imagepng($image, $resized_file);
                        break;
                        case IMAGETYPE_WBMP:
                            $orig_image = imagecreatefromwbmp($filename);
                            imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                            imagewbmp($image, $resized_file);
                        break;
                        case image_type_to_mime_type(IMAGETYPE_XBM):
                            $orig_image = imagecreatefromxbm($filename);
                            imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                            imagexbm($image, $resized_file);
                        break;
                    }
                    if (!is_null($orig_image))
                    {
                        $meta = array(
                            'height' => $size['height'],
                            'reference' => $id,
                            'size' => $label,
                            'width' => $size['width'],
                        );
                        self::save_file($resized_file, $meta);
                        imagedestroy($orig_image);
                    }
                    imagedestroy($image);
                }
            }
        }
        return $file_ids;
    }
    // }}}
}
