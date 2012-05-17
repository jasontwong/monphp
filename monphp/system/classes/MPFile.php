<?php

class MPFile
{
    // {{{ public static function save_file($path, $name, $tmp_file, $sizes)
    public static function save_file($path, $name, $tmp_file, $sizes)
    {
        $original_file = $path.'/'.$name;
        $success = move_uploaded_file($tmp_file, $original_file);

        if ($success)
        {
            $resized_path = $path.'/_resized';
            mkdir($resized_path);
            if (is_dir($resized_path) && list($width, $height, $mime_type) = getimagesize($original_file))
            {
                $quality = 90;
                $basename = file_extension($name);
                foreach ($sizes as $label => $size)
                {
                    if ($size['width'] > 0 && $size['height'] > 0 && ($width > $size['width'] || $height > $size['height']))
                    {
                        $ratio_orig = $width/$height;

                        if ($size['width']/$size['height'] > $ratio_orig) 
                        {
                           $size['width'] = $size['height']*$ratio_orig;
                        } 
                        else 
                        {
                           $size['height'] = $size['width']/$ratio_orig;
                        }
                        
                        $image = imagecreatetruecolor($size['width'], $size['height']);
                        $resized_file = $resized_path.'/'.$basename[0].'-'.$label.$basename[1];
                        switch ($mime_type)
                        {
                            case IMAGETYPE_GIF:
                                $orig_image = imagecreatefromgif($original_file);
                                imagealphablending($image, false);
                                imagesavealpha($image, true);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagegif($image, $resized_file);
                            break;
                            case IMAGETYPE_JPEG:
                                $orig_image = imagecreatefromjpeg($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagejpeg($image, $resized_file, $quality);
                            break;
                            case IMAGETYPE_PNG:
                                $orig_image = imagecreatefrompng($original_file);
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
                                $orig_image = imagecreatefromwbmp($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagewbmp($image, $resized_file);
                            break;
                            case image_type_to_mime_type(IMAGETYPE_XBM):
                                $orig_image = imagecreatefromxbm($original_file);
                                imagecopyresampled($image, $orig_image, 0, 0, 0, 0, $size['width'], $size['height'], $width, $height);
                                imagexbm($image, $resized_file);
                            break;
                        }
                        if (isset($orig_image))
                        {
                            imagedestroy($orig_image);
                        }
                        imagedestroy($image);
                    }
                }
            }
        }

        return $success;
    }
    //}}}
}
