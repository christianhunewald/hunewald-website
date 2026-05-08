<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['ok' => false, 'error' => 'Invalid data']);
    exit;
}

// Sanitize inputs
$vorname   = htmlspecialchars(strip_tags($data['vorname'] ?? ''));
$nachname  = htmlspecialchars(strip_tags($data['nachname'] ?? ''));
$email     = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$anliegen  = htmlspecialchars(strip_tags($data['anliegen'] ?? ''));
$nachricht = htmlspecialchars(strip_tags($data['nachricht'] ?? ''));

if (empty($vorname) || empty($email)) {
    echo json_encode(['ok' => false, 'error' => 'Pflichtfelder fehlen']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Ungueltige E-Mail-Adresse']);
    exit;
}

// Load SMTP password from external file (not in GitHub)
$passwordFile = __DIR__ . '/smtp_password.txt';
if (!file_exists($passwordFile)) {
    echo json_encode(['ok' => false, 'error' => 'Server configuration error']);
    exit;
}
$smtpPassword = trim(file_get_contents($passwordFile));

// Load PHPMailer
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'christian.hunewald@gmail.com';
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Recipients
    $mail->setFrom('christian.hunewald@gmail.com', 'hunewald.de Kontaktformular');
    $mail->addAddress('christian.hunewald@gmail.com', 'Christian Hunewald');
    $mail->addReplyTo($email, $vorname . ' ' . $nachname);

    // Content
    $mail->Subject = 'Neue Anfrage von ' . $vorname . ' ' . $nachname;
    $body  = "Neue Kontaktanfrage ueber hunewald.de\n";
    $body .= "=====================================\n\n";
    $body .= "Name:     " . $vorname . " " . $nachname . "\n";
    $body .= "E-Mail:   " . $email . "\n";
    $body .= "Anliegen: " . ($anliegen ?: 'Keine Angabe') . "\n\n";
    $body .= "Nachricht:\n" . $nachricht . "\n\n";
    $body .= "=====================================\n";
    $body .= "Gesendet am: " . date('d.m.Y H:i') . "\n";
    $mail->Body = $body;

    $mail->send();
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
?>
