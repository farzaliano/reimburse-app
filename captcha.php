<?php
session_start();

// Buat kode captcha (6 karakter acak: huruf dan angka)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_code = substr(str_shuffle($chars), 0, 6);
$_SESSION['captcha'] = $captcha_code;

// Buat gambar
$image = imagecreatetruecolor(120, 40);
$bg_color = imagecolorallocate($image, 255, 255, 255); // putih
$text_color = imagecolorallocate($image, 0, 0, 0); // hitam
$line_color = imagecolorallocate($image, 64, 64, 64); // abu2

imagefilledrectangle($image, 0, 0, 120, 40, $bg_color);

// Tambahkan garis acak sebagai gangguan
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $line_color);
}

// Tulis kode ke gambar
imagestring($image, 5, 20, 10, $captcha_code, $text_color);

// Tampilkan gambar
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
<!-- site key 6LfKxXMrAAAAAEsZ2J-HKXxOeqXBHPZQLUIEoxW6
    secret key 6LfKxXMrAAAAAL0I8t79p9RcZUHsdyGpITtpcdoW
-->