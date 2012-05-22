<?php

class MPFile
{
    // {{{ public static function save_file($file, $name, $meta = array(), $sizes = array())
    /**
     * This function should save the file to a GridFS, create multiple sizes if needed,
     * and add the proper metadata to the files.
     * 
     * @param string $file full path of the file on the server to save
     * @param string $name the name of the file to use
     * @param array $meta additional metadata that needs to be added to the file
     * @param array $sizes an array of sizes to create besides the original
     * @return array the array of ids returned from GridFS
     */
    public static function save_file($file, $name, $meta = array(), $sizes = array())
    {
        $filename = '/tmp/' . $name;
        $success = move_uploaded_file($file, $filename);
        $file_ids = array();
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
            list($width, $height, $mime_type) = getimagesize($filename);
            $meta = array(
                'metadata' => $meta,
                'filename' => $name,
                'width' => $width,
                'height' => $height,
                'mime' => $mime_type,
                'size' => 'original',
            );
            $file_ids['original'] = $grid->storeFile($filename, $meta);

            $quality = 90;
            $basename = file_extension($name);
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
                    $resized_file = $resized_path.'/'.$basename[0].'-'.$label.$basename[1];
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

                    if (isset($orig_image))
                    {
                        $meta['width'] = $size['width'];
                        $meta['height'] = $size['height'];
                        $meta['size'] = $label;
                        $file_ids[$label] = $grid->storeFile($resized_file, $meta);
                        imagedestroy($orig_image);
                    }
                    imagedestroy($image);
                }
            }
        }

        return $file_ids;
    }
    // }}}
    // {{{ public static function get_files($ids = array(), $sizes = 'original', $meta = array())
    /**
     * This funciton should be used to get files from the GridFS coillection
     * 
     * @param array|string|MongoID $ids a query for a single id or multiple ids
     * @param array|string $sizes a query for a single size or multiple sizes
     * @param array $meta metadata to query for
     * @return array a set of MongoGridFSFiles that were queried
     */
    public static function get_files($ids = array(), $sizes = 'original', $meta = array())
    {
        $data = $query = array();
        if ($ids)
        {
            if (is_string($ids))
            {
                $query['_id'] = new MongoID($ids);
            }
            else 
            {
                if (is_array($ids))
                {
                    foreach ($ids as &$id)
                    {
                        if (is_string($id))
                        {
                            $id = new MongoID($id);
                        }
                    }
                }
                $query['_id'] = $ids;
            }
        }
        if ($sizes)
        {
            if (is_array($sizes))
            {
                $query['size'] = array(
                    '$in' => $sizes,
                );
            }
            else
            {
                $query['size'] = $sizes;
            }
        }
        if ($meta)
        {
            foreach ($meta as $k => &$v)
            {
                $query['metadata.' . $k] = is_array($v)
                    ? array('$in' => $v)
                    : $v;
            }
        }
        if ($query)
        {
            $result = MonDB::getGridFS('mp')->find($query);
            $data = iterator_to_array($data);
        }
        return $data;
    }
    // }}}
}
