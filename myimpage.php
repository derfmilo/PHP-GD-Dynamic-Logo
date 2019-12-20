<?php
$angle = 45;
$image_width = 300;  
$image_height = 200;
$get_radius = ($image_width/8)*3;
$r = $get_radius;
$circle = $image_width/2;
$cx = $circle;
$cy = $circle;
$font_title = 'fonts/Baloo-Regular.ttf';
$font_tag = 'fonts/Baloo-Regular.ttf';

$tag = (isset($_GET['tagline'])) ? $_GET['tagline'] : "No Tagline";
$arch = (isset($_GET['arch'])) ? $_GET['arch'] : false;
$txt1 = (isset($_GET['title'])) ? $_GET['title'] : "No Title";
$tag_size = (isset($_GET['tag_size'])) ? $_GET['tag_size'] : 16;
$tag_color = (isset($_GET['tag_color'])) ? explode(",",$_GET['tag_color']) : [0,0,0];
$title_size = (isset($_GET['title_size'])) ? $_GET['title_size'] : 20;
$title_color = (isset($_GET['title_color'])) ? explode(",",$_GET['title_color']) : [0,0,0];
$icon_color = (isset($_GET['icon_color'])) ? explode(",",$_GET['icon_color']) : [0,0,0];
$percent = (isset($_GET['ratio'])) ? $_GET['ratio'] : 0.5;
$filename = (isset($_GET['file'])) ? $_GET['file'] : "truck.png";

$im = imagecreate($image_width,$image_height);
$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
$tcolor = imagecolorallocate($im, $tag_color[0],$tag_color[1],$tag_color[2]);
$titlecolor = imagecolorallocate($im, $title_color[0],$title_color[1],$title_color[2]);

//RESIZE Move image Icon Center and colorize
imageOverlay($filename,$icon_color[0],$icon_color[1],$icon_color[2]);
list($width, $height) = getimagesize('result.png');
$new_width = $width * $percent;
$new_height = $height * $percent;
$image = imagecreatefrompng('result.png');
$iconx = ($image_width-$new_width)/2;
$icony = ($image_height-$new_height)/2;
imagecopyresampled($im, $image, $iconx, $icony, 0, 0, $new_width, $new_height, $width, $height);

if($arch == true){
    $pad = 2; //7.^php $pad=-1//5.6php and below $pad=2 extra char spacing for text
    $s = 180;
    $e = 360;
    textOnArc($im,$cx,$cy,$r,$s,$e,$titlecolor,$txt1,$font_title,$title_size,$pad);
}else{
    $title_box = imagettfbbox($title_size,$angle,$font_title,$txt1);
    $text_width = $title_box[2]-$title_box[0];
    $text_height = $title_box[7]-$title_box[1];
    $get_tag_position = ($image_width/8)*1.2;
    imagettftext($im, $title_size, 0, get_center_text_position($image_width, $title_size, $font_title, $txt1), $get_tag_position, $titlecolor, $font_title, $txt1); 
}
$text_box = imagettfbbox($tag_size,$angle,$font_tag,$tag);
$text_width = $text_box[2]-$text_box[0];
$text_height = $text_box[7]-$text_box[1];
$x = ($image_width/2) - ($text_width/2);
$y = ($image_height/2) - ($text_height/2);
$get_tag_position = ($image_width/8)*4.5;
imagettftext($im, $tag_size, 0, get_center_text_position($image_width, $tag_size, $font_tag, $tag), $get_tag_position, $tcolor, $font_tag, $tag);

header("content-type: image/png");
imagepng($im);
imagedestroy($im);

function imageOverlay($filename,$r,$g,$b){
    $imi = imageCreateFromPng($filename);
    // Iterate over all pixels
    for ($x = 0; $x < imagesx($imi); $x++) {
       for ($y = 0; $y < imagesy($imi); $y++) {
          // Get color, and transparency of this pixel
          $col=imagecolorat($imi,$x,$y);
          // Extract alpha
          $alpha = ($col & 0x7F000000) >> 24;
          // Make black with original alpha
          $repl=imagecolorallocatealpha($imi,$r,$g,$b,$alpha);
          // Replace in image
          imagesetpixel($imi,$x,$y,$repl); 
       }
    }
    $white_0   = imagecolorallocatealpha($imi, 255, 255, 255, 1);
    // set background to opaque white.
    imagefill($imi, 0, 0, $white_0);
    imagePNG($imi,"result.png");
}

function textWidth($txt, $font, $size){
    $bbox = imagettfbbox($size,0,$font,$txt);
    $w = abs($bbox[4]-$bbox[0]);
    return $w;
}
function textOnArc($im,$cx,$cy,$r,$s,$e,$txtcol,$txt,$font,$size, $pad=0){
    $tlen = strlen($txt);
    $arccentre = ($e + $s)/2;
    $total_width = textWidth($txt, $font, $size) - ($tlen-1)*$pad;
    $textangle = rad2deg($total_width / $r);
    $s = $arccentre - $textangle/2;
    $e = $arccentre + $textangle/2;
    for ($i=0, $theta = deg2rad($s); $i < $tlen; $i++){
        $ch = $txt{$i};
        $tx = $cx + $r*cos($theta);
        $ty = $cy + $r*sin($theta);
        $dtheta = (textWidth($ch,$font,$size))/$r;
        $angle = rad2deg(M_PI*3/2 - ($dtheta/2 + $theta) );
        imagettftext($im, $size, $angle, $tx, $ty, $txtcol, $font, $ch);
        $theta += $dtheta;
    }
}
function get_center_text_position($img_width, $font_size, $font_file, $string) {
    $bounding_box_size = imagettfbbox($font_size, 0, $font_file, $string);
    $text_width = $bounding_box_size[2] - $bounding_box_size[0];
    return ceil(($img_width - $text_width) / 2);
}
?>