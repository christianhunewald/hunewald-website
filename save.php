<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$card = [
    'id'             => uniqid('card_', true),
    'timestamp'      => date('Y-m-d H:i:s'),
    'ts_de'          => isset($data['ts_de'])          ? substr($data['ts_de'], 0, 30)          : '',
    'card_type'      => isset($data['card_type'])      ? substr($data['card_type'], 0, 20)      : 'Standard v9',
    'pair'           => isset($data['pair'])           ? strtoupper(substr($data['pair'], 0, 10)) : '',
    'playbook'       => isset($data['playbook'])       ? substr($data['playbook'], 0, 40)       : '',
    'size'           => isset($data['size'])           ? substr($data['size'], 0, 30)           : '',
    'grade'          => isset($data['grade'])          ? substr($data['grade'], 0, 20)          : '',
    'concern'        => isset($data['concern'])        ? substr($data['concern'], 0, 500)       : '',
    'comments'       => isset($data['comments'])       ? substr($data['comments'], 0, 500)      : '',
    'position_notes' => isset($data['position_notes']) ? substr($data['position_notes'], 0, 400) : '',
    'sequence_notes' => isset($data['sequence_notes']) ? substr($data['sequence_notes'], 0, 400) : '',
    'entry_notes'    => isset($data['entry_notes'])    ? substr($data['entry_notes'], 0, 400)   : '',
    'outcome'        => 'filed',
];

$file = __DIR__ . '/cards.json';

$cards = [];
if (file_exists($file)) {
    $existing = file_get_contents($file);
    $cards = json_decode($existing, true) ?: [];
}

array_unshift($cards, $card);

$result = file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not write file']);
    exit;
}

echo json_encode(['ok' => true, 'id' => $card['id']]);
