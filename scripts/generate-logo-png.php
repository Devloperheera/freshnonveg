<?php

$outDir = __DIR__.'/../public/website_assets/image';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$width = 400;
$height = 100;
$im = imagecreatetruecolor($width, $height);
if ($im === false) {
    fwrite(STDERR, "GD unavailable\n");
    exit(1);
}

$white = imagecolorallocate($im, 255, 255, 255);
$red = imagecolorallocate($im, 229, 57, 53);
$orange = imagecolorallocate($im, 245, 124, 0);
$green = imagecolorallocate($im, 43, 174, 73);

imagefilledrectangle($im, 0, 0, $width, $height, $white);

$font = 'C:\\Windows\\Fonts\\arialbd.ttf';
if (! is_file($font)) {
    $font = 'C:\\Windows\\Fonts\\Arialbd.ttf';
}

if (is_file($font)) {
    imagettftext($im, 28, 0, 18, 62, $red, $font, 'Fresh');
    imagettftext($im, 28, 0, 130, 62, $orange, $font, 'Non');
    imagettftext($im, 28, 0, 220, 62, $green, $font, 'Veg');
    imagefilledellipse($im, 360, 50, 22, 22, $green);
} else {
    imagestring($im, 5, 20, 40, 'Fresh Non Veg', $red);
}

$pngPath = $outDir.'/logo.png';
imagepng($im, $pngPath);
imagedestroy($im);

$favicon = imagecreatetruecolor(64, 64);
$bg = imagecolorallocate($favicon, 43, 174, 73);
imagefilledrectangle($favicon, 0, 0, 64, 64, $bg);
$txt = imagecolorallocate($favicon, 255, 255, 255);
if (is_file($font)) {
    imagettftext($favicon, 22, 0, 10, 42, $txt, $font, 'FN');
} else {
    imagestring($favicon, 5, 18, 24, 'FN', $txt);
}
imagepng($favicon, $outDir.'/favicon.png');
imagedestroy($favicon);

echo "Wrote {$pngPath}\n";
