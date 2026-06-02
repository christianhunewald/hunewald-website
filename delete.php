<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id']);
    exit;
}

$file = __DIR__ . '/cards.json';
if (!file_exists($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'No cards found']);
    exit;
}

$cards = json_decode(file_get_contents($file), true) ?: [];
$id    = $data['id'];
$before = count($cards);
$cards = array_values(array_filter($cards, fn($c) => ($c['id'] ?? '') !== $id));
$after = count($cards);

if ($before === $after) {
    http_response_code(404);
    echo json_encode(['error' => 'Card not found']);
    exit;
}

file_put_contents($file, json_encode($cards, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok' => true, 'deleted' => $id]);
