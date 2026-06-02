<?php
// save.php — receives gate card data and appends to cards.json
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

// Sanitise fields
$card = [
    'id'        => uniqid('card_', true),
    'timestamp' => date('Y-m-d H:i:s'),
    'ts_de'     => isset($data['ts_de'])     ? substr($data['ts_de'], 0, 30)     : '',
    'card_num'  => isset($data['card_num'])  ? intval($data['card_num'])          : 0,
    'card_type' => isset($data['card_type']) ? substr($data['card_type'], 0, 20) : '',
    'pair'      => isset($data['pair'])      ? strtoupper(substr($data['pair'], 0, 10)) : '',
    'direction' => isset($data['direction']) ? substr($data['direction'], 0, 10) : '',
    'playbook'  => isset($data['playbook'])  ? substr($data['playbook'], 0, 30)  : '',
    'size'      => isset($data['size'])      ? substr($data['size'], 0, 30)      : '',
    'grade'     => isset($data['grade'])     ? substr($data['grade'], 0, 20)     : '',
    'sl_name'   => isset($data['sl_name'])   ? substr($data['sl_name'], 0, 100)  : '',
    'sl_pips'   => isset($data['sl_pips'])   ? substr($data['sl_pips'], 0, 10)   : '',
    'tp_pips'   => isset($data['tp_pips'])   ? substr($data['tp_pips'], 0, 10)   : '',
    'concern'   => isset($data['concern'])   ? substr($data['concern'], 0, 500)  : '',
    'pm1'       => isset($data['pm1'])       ? substr($data['pm1'], 0, 500)      : '',
    'pm2'       => isset($data['pm2'])       ? substr($data['pm2'], 0, 500)      : '',
    'pm3'       => isset($data['pm3'])       ? substr($data['pm3'], 0, 500)      : '',
    'rre_mech'  => isset($data['rre_mech'])  ? substr($data['rre_mech'], 0, 500) : '',
    'h4_dir'    => isset($data['h4_dir'])    ? substr($data['h4_dir'], 0, 20)    : '',
    'outcome'   => 'filed',
];

$file = __DIR__ . '/cards.json';

// Load existing
$cards = [];
if (file_exists($file)) {
    $existing = file_get_contents($file);
    $cards = json_decode($existing, true) ?: [];
}

// Prepend new card (newest first)
array_unshift($cards, $card);

// Save
$result = file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not write file']);
    exit;
}

echo json_encode(['ok' => true, 'id' => $card['id']]);
