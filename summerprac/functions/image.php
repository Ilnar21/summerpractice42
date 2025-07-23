<?php
function applyGrayscale($path) {
    $img = imagecreatefromjpeg($path);
    imagefilter($img, IMG_FILTER_GRAYSCALE);
    imagejpeg($img, $path);
    imagedestroy($img);
}

function cropToAspect($file_path, $aspect) {
    [$w, $h] = getimagesize($file_path);
    $src = imagecreatefromjpeg($file_path);
    switch ($aspect) {
        case '1:1':
            $side = min($w, $h);
            $src_x = ($w - $side) / 2;
            $src_y = ($h - $side) / 2;
            $new_w = $new_h = $side;
            break;
        case '4:3':
            $target_ratio = 4 / 3;
            if ($w / $h > $target_ratio) {
                $new_h = $h;
                $new_w = $h * $target_ratio;
                $src_x = ($w - $new_w) / 2;
                $src_y = 0;
            } else {
                $new_w = $w;
                $new_h = $w / $target_ratio;
                $src_x = 0;
                $src_y = ($h - $new_h) / 2;
            }
            break;
        case '16:9':
            $target_ratio = 16 / 9;
            if ($w / $h > $target_ratio) {
                $new_h = $h;
                $new_w = $h * $target_ratio;
                $src_x = ($w - $new_w) / 2;
                $src_y = 0;
            } else {
                $new_w = $w;
                $new_h = $w / $target_ratio;
                $src_x = 0;
                $src_y = ($h - $new_h) / 2;
            }
            break;
        default:
            imagedestroy($src);
            return;
    }

    $dst = imagecreatetruecolor($new_w, $new_h);
    imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $new_w, $new_h, $new_w, $new_h);
    imagejpeg($dst, $file_path);
    imagedestroy($src);
    imagedestroy($dst);
}

