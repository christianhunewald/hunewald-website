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
$vorname  = htmlspecialchars(strip_tags($data['vorname'] ?? ''));
$nachname = htmlspecialchars(strip_tags($data['nachname'] ?? ''));
$email    = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$anliegen = htmlspecialchars(strip_tags($data['anliegen'] ?? ''));
$nachricht = htmlspecialchars(strip_tags($data['nachricht'] ?? ''));

if (empty($vorname) || empty($email)) {
    echo json_encode(['ok' => false, 'error' => 'Pflichtfelder fehlen']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Ungueltige E-Mail-Adresse']);
    exit;
}

// Build email
$to      = 'christian@hunewald.de';
$subject = '=?UTF-8?B?' . base64_encode('Neue Anfrage von ' . $vorname . ' ' . $nachname) . '?=';

$body = "Neue Kontaktanfrage ueber hunewald.de\n";
$body .= "=====================================\n\n";
$body .= "Name:     " . $vorname . " " . $nachname . "\n";
$body .= "E-Mail:   " . $email . "\n";
$body .= "Anliegen: " . ($anliegen ?: 'Keine Angabe') . "\n\n";
$body .= "Nachricht:\n" . $nachricht . "\n\n";
$body .= "=====================================\n";
$body .= "Gesendet am: " . date('d.m.Y H:i') . "\n";

$headers  = "From: noreply@hunewald.de\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'E-Mail konnte nicht gesendet werden']);
}
?>
