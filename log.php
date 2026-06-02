<?php
$file  = __DIR__ . '/cards.json';
$all_cards = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

$filter_pair  = isset($_GET['pair'])  ? strtoupper(trim($_GET['pair']))  : '';
$filter_grade = isset($_GET['grade']) ? trim($_GET['grade'])             : '';
$filter_type  = isset($_GET['type'])  ? trim($_GET['type'])              : '';

$cards = $all_cards;
if ($filter_pair)  $cards = array_filter($cards, fn($c) => strpos($c['pair'],  $filter_pair)  !== false);
if ($filter_grade) $cards = array_filter($cards, fn($c) => strpos($c['grade'], $filter_grade) !== false);
if ($filter_type)  $cards = array_filter($cards, fn($c) => strpos($c['card_type'], $filter_type) !== false);

$total     = count($cards);
$total_all = count($all_cards);
$a_plus    = count(array_filter($all_cards, fn($c) => strpos($c['grade'], 'A+') !== false));
$b_std     = count(array_filter($all_cards, fn($c) => strpos($c['grade'], 'B Standard') !== false));
$rre_count = count(array_filter($all_cards, fn($c) => strpos($c['card_type'], 'RRE') !== false));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gate Card Log</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap');
:root {
  --black:#0d0d0d; --white:#fff; --off-white:#f7f7f5; --border:#d0d0cc;
  --row-alt:#f2f2ef; --concern-bg:#fff0f0; --concern-bar:#c0392b;
  --premortem-bar:#1a1a2e; --required-bar:#1a1a1a; --bonus-bar:#2c5f2e;
  --context-bar:#7a0000; --rre-bar:#1a2a4a; --green:#27ae60;
  --text:#0d0d0d; --text-mid:#444; --text-light:#888;
  --mono:'JetBrains Mono',monospace; --sans:'Inter',sans-serif;
  --bg:#e8e8e4;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);font-family:var(--sans);font-size:13px;color:var(--text);padding:20px 12px 60px;-webkit-font-smoothing:antialiased;}

/* PAGE HEADER */
.page-header{max-width:520px;margin:0 auto 12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.page-title{font-family:var(--mono);font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--black);}
.page-sub{font-size:10px;color:var(--text-light);margin-top:2px;}
.btn-new{display:inline-block;padding:7px 14px;background:var(--black);color:white;font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;text-decoration:none;border-radius:3px;}

/* FILTERS */
.filters{max-width:520px;margin:0 auto 12px;display:flex;gap:6px;flex-wrap:wrap;}
.filters input,.filters select{border:1px solid var(--border);border-radius:3px;padding:6px 9px;font-family:var(--mono);font-size:11px;background:white;outline:none;}
.filters input:focus,.filters select:focus{border-color:var(--black);}
.filters button{padding:6px 12px;background:var(--black);color:white;border:none;border-radius:3px;font-family:var(--mono);font-size:10px;font-weight:700;letter-spacing:0.08em;cursor:pointer;}
.filters a{padding:6px 8px;font-family:var(--mono);font-size:10px;color:var(--text-light);text-decoration:none;}

/* STATS */
.stats-row{max-width:520px;margin:0 auto 14px;display:flex;gap:8px;flex-wrap:wrap;}
.stat-box{background:white;border:1px solid var(--border);border-radius:4px;padding:7px 12px;font-family:var(--mono);}
.stat-box .val{font-size:18px;font-weight:700;}
.stat-box .lbl{font-size:8px;color:var(--text-light);letter-spacing:0.1em;text-transform:uppercase;margin-top:1px;}
.stat-box.green .val{color:var(--green);}

/* CARD LIST */
.card-list{max-width:520px;margin:0 auto;display:flex;flex-direction:column;gap:16px;}
.empty-state{max-width:520px;margin:40px auto;text-align:center;color:var(--text-light);font-family:var(--mono);font-size:11px;}

/* CARD SHELL — mirrors gate-card.html */
.filed-card{background:var(--white);border:1.5px solid var(--black);box-shadow:3px 3px 0 rgba(0,0,0,0.12);}

/* CARD HEADER */
.card-header{background:var(--black);color:white;padding:9px 12px 7px;display:flex;align-items:flex-start;justify-content:space-between;cursor:pointer;user-select:none;}
.card-header-left h2{font-family:var(--mono);font-size:11px;font-weight:700;letter-spacing:0.08em;}
.card-header-left p{font-size:9px;color:#aaa;margin-top:2px;letter-spacing:0.04em;}
.card-header-right{display:flex;flex-direction:column;align-items:flex-end;gap:3px;}
.card-num-badge{font-family:var(--mono);font-size:13px;font-weight:700;border:1px solid #555;padding:2px 8px;color:white;}
.card-ts{font-family:var(--mono);font-size:9px;color:#888;}
.toggle-hint{font-family:var(--mono);font-size:9px;color:#666;margin-top:4px;}

/* META GRID */
.meta-grid{display:grid;grid-template-columns:1fr 1fr 1fr;border-bottom:1px solid var(--border);}
.meta-cell{padding:7px 10px;border-right:1px solid var(--border);}
.meta-cell:last-child{border-right:none;}
.meta-label{font-family:var(--mono);font-size:8px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-light);margin-bottom:3px;}
.meta-value{font-family:var(--sans);font-size:12px;font-weight:600;color:var(--text);}
.grade-val{font-family:var(--mono);font-size:11px;font-weight:700;}
.grade-val.aplus{color:#1a7a1a;}
.grade-val.bstd{color:var(--black);}
.grade-val.rre{color:#1a2a6a;}
.grade-val.other{color:var(--text-light);}
.playbook-row{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border);}
.playbook-row .meta-cell{border-right:1px solid var(--border);}
.playbook-row .meta-cell:last-child{border-right:none;}

/* SECTION BAR */
.section-bar{padding:5px 10px;color:white;font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;}
.section-bar .bar-sub{font-size:8px;font-weight:400;opacity:0.7;letter-spacing:0.06em;text-transform:none;}
.section-bar.concern{background:var(--concern-bar);}
.section-bar.premortem{background:var(--premortem-bar);}
.section-bar.required{background:var(--required-bar);}
.section-bar.bonus{background:var(--bonus-bar);}
.section-bar.context{background:var(--context-bar);}
.section-bar.rre-spec{background:var(--rre-bar);}
.section-bar.sl-tp-bar{background:#333;}
.section-bar.counter-bar{background:#444;}

/* CONCERN */
.concern-body{background:var(--concern-bg);padding:8px 10px;border-bottom:1px solid #f0c0c0;}
.concern-prompt{font-size:10px;font-weight:600;color:var(--concern-bar);margin-bottom:4px;font-style:italic;}
.concern-text{font-size:12px;color:var(--text);line-height:1.5;padding:6px 8px;background:rgba(255,255,255,0.7);border:1px solid #f0c0c0;border-radius:2px;}

/* PRE-MORTEM */
.pm-body{border-bottom:1px solid var(--border);}
.pm-item{padding:7px 10px;border-bottom:1px solid #eee;}
.pm-item:last-child{border-bottom:none;}
.pm-num{font-family:var(--mono);font-size:9px;font-weight:700;color:var(--text-light);letter-spacing:0.08em;margin-bottom:3px;text-transform:uppercase;}
.pm-question{font-size:10px;font-weight:600;color:var(--text-mid);margin-bottom:5px;font-style:italic;}
.pm-answer{font-size:12px;color:var(--text);line-height:1.5;padding:5px 8px;background:var(--off-white);border-radius:2px;border:1px solid var(--border);}

/* TICK ROWS */
.tick-row{display:flex;align-items:flex-start;gap:0;padding:0;border-bottom:1px solid #eee;}
.tick-row:last-child{border-bottom:none;}
.tick-row:nth-child(odd){background:var(--white);}
.tick-row:nth-child(even){background:var(--row-alt);}
.tick-box-wrap{padding:8px 10px;display:flex;align-items:center;flex-shrink:0;}
.tick-box{width:16px;height:16px;border:1.5px solid #999;border-radius:2px;background:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.tick-box.checked{background:var(--green);border-color:var(--green);}
.tick-box.checked::after{content:'';display:block;width:4px;height:7px;border:1.5px solid white;border-top:none;border-left:none;transform:rotate(45deg) translate(-1px,-1px);}
.tick-text{flex:1;padding:7px 10px 7px 0;font-size:12px;font-weight:500;color:var(--text);line-height:1.35;}
.tick-sub{font-size:9px;color:var(--text-light);margin-top:1px;font-style:italic;}
.tick-writein{font-family:var(--mono);font-size:11px;color:var(--text);margin-top:3px;padding:3px 6px;background:var(--white);border:1px solid var(--border);border-radius:2px;display:inline-block;}

/* RRE WRITEIN */
.rre-writein-body{padding:8px 10px;background:#f0f4ff;border-bottom:1px solid var(--border);}
.rre-writein-label{font-size:10px;font-weight:600;color:#1a2a4a;margin-bottom:4px;}
.rre-writein-value{font-size:12px;color:var(--text);line-height:1.5;padding:5px 8px;background:white;border:1px solid #b0b8d8;border-radius:2px;}

/* SL/TP */
.sl-tp-body{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border);}
.sl-tp-cell{padding:7px 10px;border-right:1px solid var(--border);}
.sl-tp-cell:last-child{border-right:none;}
.sl-tp-label{font-family:var(--mono);font-size:8px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-light);margin-bottom:4px;}
.sl-tp-value{font-family:var(--mono);font-size:15px;font-weight:700;color:var(--text);}

/* RULE LINE */
.rule-line{padding:5px 10px;font-size:9px;font-weight:600;color:var(--text-mid);background:#f7f7f5;border-bottom:1px solid var(--border);text-align:center;font-style:italic;}

/* COUNTER */
.counter-body{padding:6px 10px;background:#fafafa;border-bottom:1px solid var(--border);font-family:var(--mono);font-size:9px;color:var(--text-light);}

/* CARD BODY toggle */
.card-body{display:none;}
.card-body.open{display:block;}

/* H4 badge */
.h4-badge{display:inline-block;margin-top:3px;padding:2px 7px;background:#fff8e8;border:1px solid #f0d080;border-radius:2px;font-family:var(--mono);font-size:10px;font-weight:700;color:#a06010;}
</style>
</head>
<body>

<div class="page-header">
  <div>
    <div class="page-title">Gate Card Log</div>
    <div class="page-sub"><?= $total ?> card<?= $total !== 1 ? 's' : '' ?><?= ($filter_pair||$filter_grade||$filter_type) ? ' (filtered)' : ' filed' ?></div>
  </div>
  <a class="btn-new" href="gate-card.html">+ New Card</a>
</div>

<form class="filters" method="GET">
  <input type="text" name="pair" placeholder="Pair" value="<?= htmlspecialchars($filter_pair) ?>">
  <select name="grade">
    <option value="">All grades</option>
    <option value="A+" <?= $filter_grade==='A+' ? 'selected':'' ?>>A+</option>
    <option value="B Standard" <?= $filter_grade==='B Standard' ? 'selected':'' ?>>B Standard</option>
    <option value="RRE" <?= $filter_grade==='RRE' ? 'selected':'' ?>>RRE</option>
  </select>
  <select name="type">
    <option value="">All types</option>
    <option value="Standard" <?= $filter_type==='Standard' ? 'selected':'' ?>>Standard</option>
    <option value="RRE" <?= $filter_type==='RRE' ? 'selected':'' ?>>RRE</option>
  </select>
  <button type="submit">Filter</button>
  <?php if ($filter_pair||$filter_grade||$filter_type): ?>
    <a href="log.php">✕ Clear</a>
  <?php endif; ?>
</form>

<div class="stats-row">
  <div class="stat-box"><div class="val"><?= $total_all ?></div><div class="lbl">Total</div></div>
  <div class="stat-box green"><div class="val"><?= $a_plus ?></div><div class="lbl">A+</div></div>
  <div class="stat-box"><div class="val"><?= $b_std ?></div><div class="lbl">B Std</div></div>
  <div class="stat-box"><div class="val"><?= $rre_count ?></div><div class="lbl">RRE</div></div>
</div>

<div class="card-list">
<?php if (empty($cards)): ?>
  <div class="empty-state">No cards filed yet<?= ($filter_pair||$filter_grade||$filter_type) ? ' matching these filters':'' ?>.</div>
<?php else: foreach ($cards as $i => $c):
  $is_rre = strpos($c['card_type'] ?? '', 'RRE') !== false;
  $grade = $c['grade'] ?? '';
  $gc = 'other';
  if (strpos($grade,'A+')!==false) $gc='aplus';
  elseif (strpos($grade,'B Standard')!==false) $gc='bstd';
  elseif (strpos($grade,'RRE')!==false) $gc='rre';
  $rule_line = $is_rre
    ? 'RULE: SL → BE at +1R. Mechanical. No deliberation.'
    : 'RULE: Sleep = close flat, regardless of P&L.  SL → BE at +1R. Mechanical. No deliberation.';
?>

<div class="filed-card">

  <!-- HEADER (clickable to toggle) -->
  <div class="card-header" onclick="toggle('body-<?= $i ?>')">
    <div class="card-header-left">
      <h2><?= $is_rre ? 'PRE-ENTRY GATE — RRE' : 'PRE-ENTRY GATE' ?> &nbsp;·&nbsp; <?= htmlspecialchars($c['pair'] ?? '—') ?></h2>
      <p><?= htmlspecialchars($c['card_type'] ?? '') ?> &nbsp;·&nbsp; <?= htmlspecialchars($c['playbook'] ?? '') ?></p>
    </div>
    <div class="card-header-right">
      <span class="card-num-badge">#<?= htmlspecialchars($c['card_num'] ?? '—') ?></span>
      <span class="card-ts"><?= htmlspecialchars(substr($c['timestamp'] ?? '', 0, 16)) ?></span>
      <span class="toggle-hint" id="hint-<?= $i ?>">▼ expand</span>
    </div>
  </div>

  <!-- BODY -->
  <div class="card-body" id="body-<?= $i ?>">

    <!-- META -->
    <div class="meta-grid">
      <div class="meta-cell">
        <div class="meta-label">Date</div>
        <div class="meta-value"><?= htmlspecialchars(substr($c['timestamp'] ?? '', 0, 10)) ?></div>
      </div>
      <div class="meta-cell">
        <div class="meta-label">Pair</div>
        <div class="meta-value"><?= htmlspecialchars($c['pair'] ?? '—') ?></div>
      </div>
      <div class="meta-cell">
        <div class="meta-label">Grade</div>
        <div class="grade-val <?= $gc ?>"><?= htmlspecialchars($grade ?: '—') ?></div>
      </div>
    </div>
    <div class="playbook-row">
      <div class="meta-cell">
        <div class="meta-label">Playbook</div>
        <div class="meta-value"><?= htmlspecialchars($c['playbook'] ?? '—') ?></div>
      </div>
      <div class="meta-cell">
        <div class="meta-label">Size</div>
        <div class="meta-value"><?= htmlspecialchars($c['size'] ?? '—') ?></div>
      </div>
    </div>
    <?php if (!empty($c['h4_dir']) && $c['h4_dir'] !== ''): ?>
    <div style="padding:6px 10px; background:#fff8e8; border-bottom:1px solid #f0d080;">
      <span style="font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.1em;color:#a06010;text-transform:uppercase;">H4 Direction: </span>
      <span class="h4-badge"><?= htmlspecialchars($c['h4_dir']) ?></span>
    </div>
    <?php endif; ?>

    <!-- CONCERN FIELD -->
    <div class="section-bar concern">Concern Field — fill first in pen | read aloud + scan back</div>
    <div class="concern-body">
      <div class="concern-prompt">What concerns me about this trade right now?</div>
      <div class="concern-text"><?= htmlspecialchars($c['concern'] ?: 'none') ?></div>
    </div>

    <!-- PRE-MORTEM -->
    <div class="section-bar premortem">Pre-Mortem Trio — fill second | any blank = RIP card</div>
    <div class="pm-body">
      <div class="pm-item">
        <div class="pm-num">1. Invalidation Trigger</div>
        <div class="pm-question"><?= $is_rre ? 'What price action proves this wrong BEFORE SL is hit?' : 'What price action proves this wrong BEFORE SL is hit? (not \'if SL hit\')' ?></div>
        <div class="pm-answer"><?= htmlspecialchars($c['pm1'] ?: '—') ?></div>
      </div>
      <div class="pm-item">
        <div class="pm-num">2. Opposite Model</div>
        <div class="pm-question">Flip the trade — equally clean setup? If yes → walk away.</div>
        <div class="pm-answer"><?= htmlspecialchars($c['pm2'] ?: '—') ?></div>
      </div>
      <div class="pm-item">
        <div class="pm-num">3. <?= $is_rre ? 'Reaction at -0.5R' : 'Hold-to-SL Acknowledgment' ?></div>
        <div class="pm-question"><?= $is_rre ? 'What is the plan if price reaches -0.5R?' : 'Write \'Hold to SL\' on the line below honestly.' ?></div>
        <div class="pm-answer"><?= htmlspecialchars($c['pm3'] ?: '—') ?></div>
      </div>
    </div>

    <!-- REQUIRED TICKS -->
    <div class="section-bar required">
      <span><?= $is_rre ? 'Required — all 7 must tick' : 'Required — all 9 must tick' ?></span>
      <span class="bar-sub">Any blank = no trade</span>
    </div>
    <div>
      <?php
      $std_ticks = [
        ['Base identified', ''],
        ['Middle section identified', ''],
        ['Entry clean and defined', ''],
        ['Position at value extreme, not midpoint', ''],
        ['Sequence valid', ''],
        ['SL backed by structure — NAME IT', $c['sl_name'] ?? ''],
        ['TP (3R) written on this card BEFORE order placed', ''],
        ['Concern Field has NO auto-void keywords (read aloud, scan back)', ''],
        ['Drawings hidden — re-confirm structure clean without lines', ''],
      ];
      $rre_ticks = [
        ['Entry clean and defined', ''],
        ['Position at value extreme, not midpoint', ''],
        ['Sequence valid', ''],
        ['SL backed by structure — NAME IT', $c['sl_name'] ?? ''],
        ['TP (3R) written on this card BEFORE order placed', ''],
        ['Concern Field has NO auto-void keywords (read aloud, scan back)', ''],
        ['Drawings hidden — re-confirm structure clean without lines', ''],
      ];
      $ticks = $is_rre ? $rre_ticks : $std_ticks;
      foreach ($ticks as $idx => $tick): ?>
      <div class="tick-row">
        <div class="tick-box-wrap"><div class="tick-box checked"></div></div>
        <div class="tick-text">
          <?= htmlspecialchars($tick[0]) ?>
          <?php if ($tick[1]): ?>
            <div class="tick-writein"><?= htmlspecialchars($tick[1]) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- RRE SPECIFIC -->
    <?php if ($is_rre && !empty($c['rre_mech'])): ?>
    <div class="section-bar rre-spec">RRE-Specific — required, cannot be blank</div>
    <div class="rre-writein-body">
      <div class="rre-writein-label">Risk-reduction mechanism:</div>
      <div class="rre-writein-value"><?= htmlspecialchars($c['rre_mech']) ?></div>
    </div>
    <?php endif; ?>

    <!-- BONUS (Standard only) -->
    <?php if (!$is_rre): ?>
    <div class="section-bar bonus">
      <span>Perfect Bonus (0–3)</span>
      <span class="bar-sub">3/3 = A+ full size · 0–2 = B standard</span>
    </div>
    <div>
      <?php $bonus_ticks = [
        'Entry sits directly below/above previous structure',
        'Internal 1-2-3 present',
        'Last touch into last previous structure',
      ];
      $bonus_checked = strpos($grade, 'A+') !== false;
      foreach ($bonus_ticks as $bt): ?>
      <div class="tick-row">
        <div class="tick-box-wrap"><div class="tick-box <?= $bonus_checked ? 'checked' : '' ?>"></div></div>
        <div class="tick-text"><?= htmlspecialchars($bt) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CONTEXT -->
    <div class="section-bar context">Context — any fail = walk away</div>
    <div>
      <?php
      $ctx_ticks = $is_rre ? [
        'Notion forecast URL exists for THIS pair (no URL = no trade)',
        'H4 + Daily alignment confirmed',
        'No high-impact news within 60 min (NFP, FOMC, CPI, BoE/ECB)',
        'Analysis separate from desire — no FOMO, no revenge',
        'Pre-Mortem Trio answers clean — none vague or aspirational',
        '[RRE] Risk-reduction mechanism can be articulated in one sentence',
        '[RRE] RRE chosen for its mechanism — NOT because Standard structure is missing',
      ] : [
        'Notion forecast URL exists for THIS pair (no URL = no trade)',
        'H4 + Daily alignment confirmed',
        'Not Friday + H4 + slow pair (unless fast + weekend hold)',
        'No high-impact news within 60 min (NFP, FOMC, CPI, BoE/ECB)',
        'Analysis separate from desire — no FOMO, no revenge',
        'Pre-Mortem Trio answers clean — none vague or aspirational',
      ];
      foreach ($ctx_ticks as $ct): ?>
      <div class="tick-row">
        <div class="tick-box-wrap"><div class="tick-box checked"></div></div>
        <div class="tick-text"><?= htmlspecialchars($ct) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- SL / TP -->
    <div class="section-bar sl-tp-bar">SL / TP</div>
    <div class="sl-tp-body">
      <div class="sl-tp-cell">
        <div class="sl-tp-label">SL (pips)</div>
        <div class="sl-tp-value"><?= htmlspecialchars($c['sl_pips'] ?: '—') ?></div>
      </div>
      <div class="sl-tp-cell">
        <div class="sl-tp-label">TP 3R (pips)</div>
        <div class="sl-tp-value"><?= htmlspecialchars($c['tp_pips'] ?: '—') ?></div>
      </div>
    </div>

    <!-- RULE LINE -->
    <div class="rule-line"><?= $rule_line ?></div>

    <!-- FOOTER -->
    <div style="background:#f0f0ec;border-top:1px solid var(--border);padding:6px 10px;display:flex;align-items:center;justify-content:space-between;">
      <span style="font-size:8px;color:var(--text-light);font-family:var(--mono);">Filed <?= htmlspecialchars($c['timestamp'] ?? '') ?></span>
      <button onclick="deleteCard('<?= htmlspecialchars($c['id'] ?? '') ?>', this)"
        style="padding:4px 12px;background:transparent;border:1px solid #c0392b;border-radius:2px;color:#c0392b;font-family:var(--mono);font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;">
        ✕ Delete
      </button>
    </div>

  </div><!-- /card-body -->
</div><!-- /filed-card -->

<?php endforeach; endif; ?>
</div>

<script>
function toggle(id) {
  const body = document.getElementById(id);
  const idx  = id.replace('body-','');
  const hint = document.getElementById('hint-'+idx);
  const open = body.classList.toggle('open');
  if (hint) hint.textContent = open ? '▲ collapse' : '▼ expand';
}

function deleteCard(id, btn) {
  if (!confirm('Delete this card? This cannot be undone.')) return;
  btn.textContent = '...';
  btn.disabled = true;
  fetch('delete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const card = btn.closest('.filed-card');
      card.style.transition = 'opacity 0.3s';
      card.style.opacity = '0';
      setTimeout(() => { card.remove(); }, 300);
    } else {
      btn.textContent = '✕ Delete';
      btn.disabled = false;
      alert('Error: ' + (d.error || 'unknown'));
    }
  })
  .catch(() => {
    btn.textContent = '✕ Delete';
    btn.disabled = false;
    alert('Network error — try again.');
  });
}
</script>
</body>
</html>
