<?php
/**
 * CAPTCHA Image Generator
 * Generates a distorted image CAPTCHA to prevent bot submissions
 */

session_start();

// Configuration
$length = 5; // Number of characters
$width = 150;
$height = 50;
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excludes confusing chars like O, 0, I, l, 1

// Generate random code
$captchaCode = '';
for ($i = 0; $i < $length; $i++) {
    $captchaCode .= $characters[random_int(0, strlen($characters) - 1)];
}

// Store in session with expiry
$formId = $_GET['form_id'] ?? 'contact';
$_SESSION['captcha_' . $formId] = [
    'code' => strtolower($captchaCode),
    'expires' => time() + 300 // 5 minutes
];

// Create image
$image = imagecreatetruecolor($width, $height);

// Colors
$bgColor = imagecolorallocate($image, 240, 240, 245);
$textColor = imagecolorallocate($image, 30, 50, 80);
$noiseColor = imagecolorallocate($image, 150, 160, 180);
$lineColor = imagecolorallocate($image, 180, 190, 200);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Add noise dots
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
}

// Add random lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
}

// Add the text with distortion
$fontSize = 5; // Built-in font size (1-5)
$charWidth = imagefontwidth($fontSize);
$charHeight = imagefontheight($fontSize);

for ($i = 0; $i < $length; $i++) {
    $char = $captchaCode[$i];
    $x = 15 + ($i * 25);
    $y = random_int(10, 25);

    // Random rotation effect via offset
    $offsetY = random_int(-5, 5);
    $offsetX = random_int(-2, 2);

    // Slightly vary the color for each character
    $charColor = imagecolorallocate($image, random_int(20, 60), random_int(40, 80), random_int(80, 120));

    imagestring($image, $fontSize, $x + $offsetX, $y + $offsetY, $char, $charColor);
}

// Add more distortion lines over text
for ($i = 0; $i < 3; $i++) {
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
}

// Output
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

imagepng($image);
imagedestroy($image);
