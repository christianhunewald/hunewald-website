<?php
$file  = __DIR__ . '/cards.json';
$all_cards = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

$filter_pair  = isset($_GET['pair'])  ? strtoupper(trim($_GET['pair']))  : '';
$filter_grade = isset($_GET['grade']) ? trim($_GET['grade'])             : '';

$cards = $all_cards;
if ($filter_pair)  $cards = array_filter($cards, fn($c) => strpos($c['pair'],  $filter_pair)  !== false);
if ($filter_grade) $cards = array_filter($cards, fn($c) => ($c['grade'] ?? '') === $filter_grade);

$total     = count($cards);
$total_all = count($all_cards);
$a_plus    = count(array_filter($all_cards, fn($c) => ($c['grade'] ?? '') === 'A+'));
$b_std     = count(array_filter($all_cards, fn($c) => ($c['grade'] ?? '') === 'B'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gate Card Log</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&display=swap');
:root {
  --page-bg: oklch(0.985 0.004 80);
  --outer-bg: oklch(0.93 0.005 80);
  --text: oklch(0.22 0.012 260);
  --label-light: oklch(0.5 0.01 260);
  --border-mid: oklch(0.6 0.01 260);
  --border-dark: oklch(0.22 0.012 260);
  --accent: oklch(0.4 0.14 55);
  --green: #27ae60;
  --mono: 'IBM Plex Mono', ui-monospace, monospace;
  --sans: -apple-system, 'Helvetica Neue', Helvetica, Arial, sans-serif;
}
*,*::before,*::as{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--outer-bg);font-family:var(--sans);color:var(--text);padding:24px 12px 60px;-webkit-font-smoothing:antialiased;}
.page-header{max-width:694px;margin:0 auto 12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.page-title{font-size:20px;font-weight:700;color:var(--border-dark);}
.page-sub{font-size:10px;color:var(--label-light);margin-top:2px;font-family:var(--mono);}
.btn-new{display:inline-block;padding:7px 14px;background:var(--border-dark);color:white;font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;text-decoration:none;}
.filters{max-width:694px;margin:0 auto 12px;display:flex;gap:6px;flex-wrap:wrap;}
.filters input,.filters select{border:1px solid var(--border-mid);padding:6px 9px;font-family:var(--mono);font-size:11px;background:white;outline:none;}
.filters button{padding:6px 12px;background:var(--border-dark);color:white;border:none;font-family:var(--mono);font-size:10px;font-weight:700;letter-spacing:0.08em;cursor:pointer;}
.filters a{padding:6px 8px;font-family:var(--mono);font-size:10px;color:var(--label-light);text-decoration:none;}
.stats-row{max-width:694px;margin:0 auto 14px;display:flex;gap:8px;flex-wrap:wrap;}
.stat-box{background:white;border:1px solid var(--border-mid);padding:7px 12px;font-family:var(--mono);}
.stat-box .val{font-size:18px;font-weight:700;}
.stat-box .lbl{font-size:8px;color:var(--label-light);letter-spacing:0.1em;text-transform:uppercase;margin-top:1px;}
.stat-box.green .val{color:var(--green);}
.card-list{max-width:694px;margin:0 auto;display:flex;flex-direction:column;gap:14px;}
.empty-state{max-width:694px;margin:40px auto;text-align:center;color:var(--label-light);font-family:var(--mono);font-size:11px;}
.filed-card{background:var(--page-bg);border:1px solid rgba(0,0,0,0.08);box-shadow:0 1px 3px rgba(0,0,0,0.1);}
.card-header{padding:12px 20px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;border-bottom:2.5px solid var(--border-dark);}
.card-header-left{font-size:15px;font-weight:700;}
.card-header-left span{font-family:var(--mono);font-weight:400;font-size:10px;color:var(--label-light);margin-left:8px;}
.grade-badge{font-family:var(--mono);font-size:11px;font-weight:700;padding:3px 9px;border:1px solid var(--border-dark);}
.grade-badge.aplus{color:#1a7a1a;border-color:#1a7a1a;}
.toggle-hint{font-family:var(--mono);font-size:9px;color:var(--label-light);margin-left:10px;}
.card-body{display:none;padding:16px 20px;}
.card-body.open{display:block;}
.meta-line{font-family:var(--mono);font-size:11px;color:var(--text);margin-bottom:12px;display:flex;gap:16px;flex-wrap:wrap;}
.field-block{margin-bottom:12px;}
.field-label{font-size:12px;font-weight:600;letter-spacing:0.06em;margin-bottom:6px;border-top:1px solid var(--border-dark);padding-top:8px;}
.field-value{font-size:12.5px;color:var(--text);line-height:1.5;padding:8px 10px;background:white;border:1px solid var(--border-mid);}
.pse-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px;margin-bottom:12px;}
.pse-col{border-right:1px solid #ddd;padding-right:16px;}
.pse-col:last-child{border-right:none;padding-right:0;}
.pse-heading{display:flex;align-items:baseline;gap:5px;margin-bottom:4px;}
.pse-letter{font-size:15px;font-weight:700;color:var(--accent);}
.pse-label{font-family:var(--mono);font-size:9px;font-weight:600;color:var(--label-light);}
.pse-text{font-size:11px;line-height:1.4;color:var(--text);min-height:20px;}
.tick-row{display:flex;align-items:center;gap:8px;padding:3px 0;font-size:11.5px;}
.tick-mark{width:13px;height:13px;background:var(--green);flex-shrink:0;display:flex;align-items:center;justify-content:center;}
.tick-mark::after{content:'';width:4px;height:7px;border:1.5px solid white;border-top:none;border-left:none;transform:rotate(45deg) translate(-1px,-1px);}
.footer-row{display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:10px;border-top:1px solid var(--border-mid);}
.footer-ts{font-size:9px;color:var(--label-light);font-family:var(--mono);}
</style>
</head>
<body>

<div class="page-header">
  <div>
    <div class="page-title">Gate Card Log</div>
    <div class="page-sub"><?= $total ?> card<?= $total !== 1 ? 's' : '' ?><?= ($filter_pair||$filter_grade) ? ' (filtered)' : ' filed' ?> &middot; v9</div>
  </div>
  <a class="btn-new" href="gate-card.html">+ New Card</a>
</div>

<form class="filters" method="GET">
  <input type="text" name="pair" placeholder="Pair" value="<?= htmlspecialchars($filter_pair) ?>">
  <select name="grade">
    <option value="">All grades</option>
    <option value="A+" <?= $filter_grade==='A+' ? 'selected':'' ?>>A+</option>
    <option value="B" <?= $filter_grade==='B' ? 'selected':'' ?>>B</option>
    <option value="C" <?= $filter_grade==='C' ? 'selected':'' ?>>C</option>
  </select>
  <button type="submit">Filter</button>
  <?php if ($filter_pair||$filter_grade): ?>
    <a href="log.php">✕ Clear</a>
  <?php endif; ?>
</form>

<div class="stats-row">
  <div class="stat-box"><div class="val"><?= $total_all ?></div><div class="lbl">Total</div></div>
  <div class="stat-box green"><div class="val"><?= $a_plus ?></div><div class="lbl">A+</div></div>
  <div class="stat-box"><div class="val"><?= $b_std ?></div><div class="lbl">B</div></div>
</div>

<div class="card-list">
<?php if (empty($cards)): ?>
  <div class="empty-state">No cards filed yet<?= ($filter_pair||$filter_grade) ? ' matching these filters':'' ?>.</div>
<?php else: foreach ($cards as $i => $c):
  $grade = $c['grade'] ?? '';
  $req_ticks = [
    'Middle section identified','Base identified',
    'Drawings hidden — structure re-confirmed clean without your lines',
    'Notion forecast URL exists for this pair','H4 and Daily aligned',
    "H4 setup and it's not a Friday",'No high-impact news within 60 min (NFP / FOMC / CPI / BoE / ECB)',
  ];
?>
<div class="filed-card">
  <div class="card-header" onclick="toggle('body-<?= $i ?>')">
    <div class="card-header-left"><?= htmlspecialchars($c['pair'] ?? '—') ?><span>#<?= $i+1 ?> &middot; <?= htmlspecialchars(substr($c['timestamp'] ?? '', 0, 16)) ?></span></div>
    <div style="display:flex;align-items:center;">
      <span class="grade-badge <?= $grade==='A+'?'aplus':'' ?>"><?= htmlspecialchars($grade ?: '—') ?></span>
      <span class="toggle-hint" id="hint-<?= $i ?>">▼</span>
    </div>
  </div>
  <div class="card-body" id="body-<?= $i ?>">
    <div class="meta-line">
      <span><b>Playbook:</b> <?= htmlspecialchars($c['playbook'] ?? '—') ?></span>
      <span><b>Risk:</b> <?= htmlspecialchars($c['size'] ?? '—') ?></span>
    </div>

    <div class="field-block">
      <div class="field-label">Concerns</div>
      <div class="field-value"><?= htmlspecialchars($c['concern'] ?: 'none') ?></div>
    </div>

    <div class="pse-grid">
      <div class="pse-col">
        <div class="pse-heading"><span class="pse-letter">P</span><span class="pse-label">POSITION</span></div>
        <div class="pse-text"><?= htmlspecialchars($c['position_notes'] ?? '') ?: '—' ?></div>
      </div>
      <div class="pse-col">
        <div class="pse-heading"><span class="pse-letter">S</span><span class="pse-label">SEQUENCE</span></div>
        <div class="pse-text"><?= htmlspecialchars($c['sequence_notes'] ?? '') ?: '—' ?></div>
      </div>
      <div class="pse-col">
        <div class="pse-heading"><span class="pse-letter">E</span><span class="pse-label">ENTRY</span></div>
        <div class="pse-text"><?= htmlspecialchars($c['entry_notes'] ?? '') ?: '—' ?></div>
      </div>
    </div>

    <div class="field-block">
      <div class="field-label">Additional Confluence</div>
      <?php foreach (['Entry below/above previous structure','Internal 1-2-3 present','Last touch into last previous structure'] as $bt): ?>
      <div class="tick-row"><div class="tick-mark" style="background:var(--accent);"></div><?= htmlspecialchars($bt) ?></div>
      <?php endforeach; ?>
    </div>

    <div class="field-block">
      <div class="field-label">Structure &amp; Various</div>
      <?php foreach ($req_ticks as $rt): ?>
      <div class="tick-row"><div class="tick-mark"></div><?= htmlspecialchars($rt) ?></div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($c['comments'])): ?>
    <div class="field-block">
      <div class="field-label">Comments</div>
      <div class="field-value"><?= htmlspecialchars($c['comments']) ?></div>
    </div>
    <?php endif; ?>

    <div class="footer-row">
      <span class="footer-ts">Filed <?= htmlspecialchars($c['timestamp'] ?? '') ?></span>
      <button onclick="deleteCard('<?= htmlspecialchars($c['id'] ?? '') ?>', this)"
        style="padding:5px 12px;background:transparent;border:1px solid #c0392b;color:#c0392b;font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;">
        ✕ Delete
      </button>
    </div>
  </div>
</div>
<?php endforeach; endif; ?>
</div>

<script>
function toggle(id) {
  const body = document.getElementById(id);
  const idx  = id.replace('body-','');
  const hint = document.getElementById('hint-'+idx);
  const open = body.classList.toggle('open');
  if (hint) hint.textContent = open ? '▲' : '▼';
}
function deleteCard(id, btn) {
  if (!confirm('Delete this card? This cannot be undone.')) return;
  btn.textContent = '...'; btn.disabled = true;
  fetch('delete.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:id})})
    .then(r=>r.json())
    .then(d=>{
      if (d.ok) {
        const card = btn.closest('.filed-card');
        card.style.transition='opacity 0.3s'; card.style.opacity='0';
        setTimeout(()=>card.remove(),300);
      } else { btn.textContent='✕ Delete'; btn.disabled=false; alert('Error: '+(d.error||'unknown')); }
    })
    .catch(()=>{ btn.textContent='✕ Delete'; btn.disabled=false; alert('Network error — try again.'); });
}
</script>
</body>
</html>
