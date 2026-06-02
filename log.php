<?php
// log.php — view all filed gate cards
$file  = __DIR__ . '/cards.json';
$cards = [];
if (file_exists($file)) {
    $cards = json_decode(file_get_contents($file), true) ?: [];
}

// Filter
$filter_pair  = isset($_GET['pair'])  ? strtoupper(trim($_GET['pair']))  : '';
$filter_grade = isset($_GET['grade']) ? trim($_GET['grade'])             : '';
$filter_type  = isset($_GET['type'])  ? trim($_GET['type'])              : '';

if ($filter_pair)  $cards = array_filter($cards, fn($c) => strpos($c['pair'],  $filter_pair)  !== false);
if ($filter_grade) $cards = array_filter($cards, fn($c) => strpos($c['grade'], $filter_grade) !== false);
if ($filter_type)  $cards = array_filter($cards, fn($c) => strpos($c['card_type'], $filter_type) !== false);

$total = count($cards);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gate Card Log</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&family=Inter:wght@400;500;600&display=swap');
:root {
  --black: #0d0d0d; --white: #fff; --border: #ddd; --bg: #f5f5f2;
  --red: #c0392b; --green: #27ae60; --accent: #c0392b;
  --mono: 'IBM Plex Mono', monospace; --sans: 'Inter', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); font-family: var(--sans); font-size: 13px; color: var(--black); padding: 20px 16px 60px; }
.page-header { max-width: 900px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.page-title { font-family: var(--mono); font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
.page-sub { font-size: 11px; color: #888; margin-top: 2px; }
.btn-new { display: inline-block; padding: 8px 16px; background: var(--black); color: white; font-family: var(--mono); font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; text-decoration: none; border-radius: 3px; }
.filters { max-width: 900px; margin: 0 auto 14px; display: flex; gap: 8px; flex-wrap: wrap; }
.filters input, .filters select { border: 1px solid var(--border); border-radius: 3px; padding: 6px 10px; font-family: var(--mono); font-size: 11px; background: white; outline: none; }
.filters input:focus, .filters select:focus { border-color: var(--black); }
.filters button { padding: 6px 14px; background: var(--black); color: white; border: none; border-radius: 3px; font-family: var(--mono); font-size: 10px; font-weight: 700; letter-spacing: 0.08em; cursor: pointer; }
.stats-row { max-width: 900px; margin: 0 auto 14px; display: flex; gap: 12px; flex-wrap: wrap; }
.stat-box { background: white; border: 1px solid var(--border); border-radius: 4px; padding: 8px 14px; font-family: var(--mono); }
.stat-box .val { font-size: 20px; font-weight: 700; }
.stat-box .lbl { font-size: 9px; color: #888; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 1px; }
.stat-box.green .val { color: var(--green); }
.stat-box.red   .val { color: var(--red); }
.card-list { max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; }
.card-item { background: white; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; }
.card-item-header { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: var(--black); color: white; cursor: pointer; }
.card-item-header .ci-num { font-family: var(--mono); font-size: 10px; color: #888; min-width: 30px; }
.card-item-header .ci-ts  { font-family: var(--mono); font-size: 10px; color: #aaa; min-width: 120px; }
.card-item-header .ci-pair { font-family: var(--mono); font-size: 13px; font-weight: 700; min-width: 70px; }
.card-item-header .ci-play { font-size: 11px; color: #ccc; flex: 1; }
.card-item-header .ci-grade { font-family: var(--mono); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 2px; white-space: nowrap; }
.ci-grade.aplus { background: #e8b84b; color: black; }
.ci-grade.bstd  { background: #444; color: white; }
.ci-grade.rre   { background: #1a2a4a; color: #7090d0; }
.ci-grade.other { background: #333; color: #aaa; }
.card-item-body { display: none; padding: 12px; border-top: 1px solid #eee; }
.card-item-body.open { display: block; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.detail-field .df-label { font-family: var(--mono); font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #999; margin-bottom: 3px; }
.detail-field .df-value { font-size: 12px; color: var(--black); font-weight: 500; word-break: break-word; }
.detail-field.full { grid-column: 1 / -1; }
.pm-block { background: #f7f7f5; border: 1px solid #eee; border-radius: 3px; padding: 10px 12px; margin-top: 8px; }
.pm-block .pm-label { font-family: var(--mono); font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #888; margin-bottom: 4px; }
.pm-block .pm-text  { font-size: 12px; color: var(--black); line-height: 1.5; }
.concern-block { background: #fff0f0; border: 1px solid #f0c0c0; border-radius: 3px; padding: 10px 12px; margin-top: 8px; }
.concern-block .pm-label { color: var(--red); }
.empty-state { max-width: 900px; margin: 40px auto; text-align: center; color: #999; font-family: var(--mono); font-size: 12px; }
</style>
</head>
<body>

<div class="page-header">
  <div>
    <div class="page-title">Gate Card Log</div>
    <div class="page-sub"><?= $total ?> card<?= $total !== 1 ? 's' : '' ?> filed</div>
  </div>
  <a class="btn-new" href="gate-card.html">+ New Card</a>
</div>

<!-- FILTERS -->
<form class="filters" method="GET">
  <input type="text" name="pair" placeholder="Pair (e.g. GBPAUD)" value="<?= htmlspecialchars($filter_pair) ?>">
  <select name="grade">
    <option value="">All grades</option>
    <option value="A+" <?= $filter_grade === 'A+' ? 'selected' : '' ?>>A+</option>
    <option value="B Standard" <?= $filter_grade === 'B Standard' ? 'selected' : '' ?>>B Standard</option>
    <option value="RRE" <?= $filter_grade === 'RRE' ? 'selected' : '' ?>>RRE</option>
  </select>
  <select name="type">
    <option value="">All types</option>
    <option value="Standard" <?= $filter_type === 'Standard' ? 'selected' : '' ?>>Standard</option>
    <option value="RRE" <?= $filter_type === 'RRE' ? 'selected' : '' ?>>RRE</option>
  </select>
  <button type="submit">Filter</button>
  <?php if ($filter_pair || $filter_grade || $filter_type): ?>
    <a href="log.php" style="padding:6px 10px; font-family:var(--mono); font-size:10px; color:#888; text-decoration:none;">✕ Clear</a>
  <?php endif; ?>
</form>

<!-- STATS -->
<?php
$all_cards = json_decode(file_get_contents($file) ?: '[]', true) ?: [];
$total_all  = count($all_cards);
$a_plus     = count(array_filter($all_cards, fn($c) => strpos($c['grade'], 'A+') !== false));
$b_std      = count(array_filter($all_cards, fn($c) => strpos($c['grade'], 'B Standard') !== false));
$rre_cards  = count(array_filter($all_cards, fn($c) => strpos($c['card_type'], 'RRE') !== false));
?>
<div class="stats-row">
  <div class="stat-box"><div class="val"><?= $total_all ?></div><div class="lbl">Total Filed</div></div>
  <div class="stat-box green"><div class="val"><?= $a_plus ?></div><div class="lbl">A+ Cards</div></div>
  <div class="stat-box"><div class="val"><?= $b_std ?></div><div class="lbl">B Standard</div></div>
  <div class="stat-box"><div class="val"><?= $rre_cards ?></div><div class="lbl">RRE Cards</div></div>
</div>

<!-- CARD LIST -->
<div class="card-list">
<?php if (empty($cards)): ?>
  <div class="empty-state">No cards filed yet<?= ($filter_pair || $filter_grade || $filter_type) ? ' matching these filters' : '' ?>.</div>
<?php else: ?>
  <?php foreach ($cards as $i => $c):
    $grade_class = 'other';
    if (strpos($c['grade'], 'A+') !== false) $grade_class = 'aplus';
    elseif (strpos($c['grade'], 'B Standard') !== false) $grade_class = 'bstd';
    elseif (strpos($c['grade'], 'RRE') !== false) $grade_class = 'rre';
  ?>
  <div class="card-item">
    <div class="card-item-header" onclick="toggle('card-<?= $i ?>')">
      <span class="ci-num">#<?= htmlspecialchars($c['card_num'] ?: '—') ?></span>
      <span class="ci-ts"><?= htmlspecialchars(substr($c['timestamp'], 0, 16)) ?></span>
      <span class="ci-pair"><?= htmlspecialchars($c['pair']) ?></span>
      <span class="ci-play"><?= htmlspecialchars($c['playbook']) ?> · <?= htmlspecialchars($c['size']) ?></span>
      <span class="ci-grade <?= $grade_class ?>"><?= htmlspecialchars($c['grade']) ?></span>
    </div>
    <div class="card-item-body" id="card-<?= $i ?>">
      <div class="detail-grid">
        <div class="detail-field"><div class="df-label">Type</div><div class="df-value"><?= htmlspecialchars($c['card_type']) ?></div></div>
        <div class="detail-field"><div class="df-label">Direction</div><div class="df-value"><?= htmlspecialchars($c['direction']) ?></div></div>
        <div class="detail-field"><div class="df-label">SL Structure</div><div class="df-value"><?= htmlspecialchars($c['sl_name'] ?: '—') ?></div></div>
        <div class="detail-field"><div class="df-label">SL / TP (pips)</div><div class="df-value"><?= htmlspecialchars($c['sl_pips'] ?: '—') ?> / <?= htmlspecialchars($c['tp_pips'] ?: '—') ?></div></div>
        <?php if ($c['h4_dir']): ?>
        <div class="detail-field"><div class="df-label">H4 Direction</div><div class="df-value"><?= htmlspecialchars($c['h4_dir']) ?></div></div>
        <?php endif; ?>
        <?php if ($c['rre_mech']): ?>
        <div class="detail-field full"><div class="df-label">RRE Mechanism</div><div class="df-value"><?= htmlspecialchars($c['rre_mech']) ?></div></div>
        <?php endif; ?>
      </div>
      <?php if ($c['concern'] && $c['concern'] !== 'none'): ?>
      <div class="concern-block">
        <div class="pm-label">Concern Field</div>
        <div class="pm-text"><?= htmlspecialchars($c['concern']) ?></div>
      </div>
      <?php endif; ?>
      <div class="pm-block">
        <div class="pm-label">1. Invalidation Trigger</div>
        <div class="pm-text"><?= htmlspecialchars($c['pm1'] ?: '—') ?></div>
      </div>
      <div class="pm-block">
        <div class="pm-label">2. Opposite Model</div>
        <div class="pm-text"><?= htmlspecialchars($c['pm2'] ?: '—') ?></div>
      </div>
      <div class="pm-block">
        <div class="pm-label">3. <?= strpos($c['card_type'], 'RRE') !== false ? 'Reaction at -0.5R' : 'Hold-to-SL' ?></div>
        <div class="pm-text"><?= htmlspecialchars($c['pm3'] ?: '—') ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function toggle(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
}
</script>
</body>
</html>
