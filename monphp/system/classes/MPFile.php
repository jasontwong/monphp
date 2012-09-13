<?php

class MPFile
{
    // {{{ public static function get_file($query = array(), $fields = array())
    /**
     * This funciton should be used to get files from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSFile 
     */
    public static function get_file($query = array(), $fields = array())
    {
        return MPDB::getGridFS('mp')->findOne($query, $fields);
    }
    // }}}
    // {{{ public static function get_files($query = array(), $fields = array())
    /**
     * This funciton should be used to get a file from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return MongoGridFSCursor
     */
    public static function get_files($query = array(), $fields = array())
    {
        return MPDB::getGridFS('mp')->find($query, $fields);
    }
    // }}}
    // {{{ public static function get_image($query = array(), $fields = array())
    /**
     * This funciton should be used to get an image from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return array
     */
    public static function get_image($query = array(), $fields = array())
    {
        $images = array();
        $grid_fs = MPDB::getGridFS('mp');
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
     * This funciton should be used to get images from the GridFS coillection
     * 
     * @param array $query
     * @param array $fields fields to return
     * @return array
     */
    public static function get_images($query = array(), $fields = array())
    {
        $all_images = array();
        $grid_fs = MPDB::getGridFS('mp');
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
     * This funciton should be used to remove files from the GridFS coillection
     * 
     * @param array $query
     * @return bool
     */
    public static function remove_files($query = array())
    {
        return MPDB::getGridFS('mp')->remove($query, array('safe' => TRUE));
    }
    // }}}
    // {{{ public static function remove_images($query = array())
    /**
     * This funciton should be used to remove images from the GridFS coillection
     * 
     * @param array $query
     * @return bool
     */
    public static function remove_images($query = array())
    {
        $grid_fs = MPDB::getGridFS('mp');
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
     * @param string $filename the location of the file to be moved to
     * @param array $meta additional metadata that needs to be added to the file
     * @return mixed MongoId if successful, else NULL
     */
    public static function save_file($file, $filename, $meta = array())
    {
        $success = move_uploaded_file($file, $filename);
        if ($success)
        {
            $grid = MPDB::getGridFS('mp');
            $grid->ensureIndex(
                array(
                    'files_id' => 1,
                    'n' => 1,
                ), 
                array(
                    'unique' => 1, 
                )
            );
            $stat = stat($filename);
            $stat['nice_mtime'] = date('Y-m-d H:i:s', $stat['mtime']);
            $stat['nice_size'] = size_readable($stat['size']);
            $mime_type = finfo::file($filename, FILEINFO_MIME_TYPE);
            $name = basename($filename);
            $meta = array(
                'metadata' => array_merge(
                    $meta,
                    array(
                        'filename' => $name,
                        'mime' => $mime_type,
                        'location' => $filename,
                        'stat' => $stat,
                    )
                ),
            );
            return $grid->storeFile($filename, $meta, array('safe' => TRUE));
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
     * @param string $filename the location of the file to be moved to
     * @param array $meta additional metadata that needs to be added to the file
     * @param array $sizes an array of sizes to create besides the original
     * @return array the array of ids returned from GridFS
     */
    public static function save_image($file, $filename, $meta = array(), $sizes = array())
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
        $id = self::save_file($file, $filename, $meta);
        $file_ids = array();
        if (!is_null($id))
        {
            $file_ids['original'] = $id;

            $quality = 90;
            $basename = file_extension($name);
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
                    $resized_filename = $basename[0] . '-' . $label.$basename[1];
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
                        // this is manually saved because the save_file function
                        // includes move_uploaded_file
                        $meta['width'] = $size['width'];
                        $meta['height'] = $size['height'];
                        $meta['size'] = $label;
                        $meta['reference'] = $id;
                        $meta['filename'] = $resized_filename;
                        $meta['location'] = $resized_file;
                        $meta['mime'] = finfo::file($resized_filename, FILEINFO_MIME_TYPE);

                        $stat = stat($resized_filename);
                        $stat['nice_mtime'] = date('Y-m-d H:i:s', $stat['mtime']);
                        $stat['nice_size'] = size_readable($stat['size']);
                        $meta['stat'] = $stat;

                        $file_ids[$label] = $grid->storeFile(
                            $resized_file, 
                            array('metadata' => $meta), 
                            array('safe' => TRUE)
                        );
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
