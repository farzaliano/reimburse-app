<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'liano.farza@gmail.com';     // Ganti email
  $mail->Password   = 'nbxs omss qmmk wubt';       // Ganti App Password Gmail
  $mail->SMTPSecure = 'tls';                       // Gunakan TLS
  $mail->Port       = 587;

  // Bypass SSL verify (sementara)
  $mail->SMTPOptions = [
    'ssl' => [
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    ]
  ];

  $mail->setFrom('liano.farza@gmail.com', 'Tes ClaimEase');
  $mail->addAddress('liano.farza@gmail.com', 'Farza');

  $mail->isHTML(true);
  $mail->Subject = 'Tes Email';
  $mail->Body    = 'Ini adalah tes email dari <b>PHPMailer</b>';

  $mail->send();
  echo '✅ Email berhasil dikirim.';
} catch (Exception $e) {
  echo "❌ Gagal: {$mail->ErrorInfo}";
}
