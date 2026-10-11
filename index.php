<?php
$initial = @file_get_contents(__DIR__ . '/datos_grupos.json');
$initialData = $initial ? json_decode($initial, true) : null;
if (!is_array($initialData)) {
    $initialData = ['groups' => [], 'weeks' => [], 'notes' => [], 'antecedents' => [], 'movements' => []];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Grupos</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        bg:      '#0a0a0f',
        surf:    '#12121a',
        surf2:   '#1a1a26',
        surf3:   '#232332',
        line:    '#1e1e2e',
        line2:   '#2a2a3d',
        t1:      '#f5f5fa',
        t2:      '#a0a0b8',
        t3:      '#6a6a85',
        t4:      '#45455c',
        gold:    '#e8b923',
        gold2:   '#f7cf57',
        ok:      '#3ecf8e',
        bad:     '#f04747',
        warn:    '#f5a623',
        info:    '#5b9dff',
        purp:    '#a78bfa',
      },
      fontFamily: {
        sans: ['Inter', 'SF Pro Display', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'Menlo', 'monospace'],
      },
      animation: {
        'fade-in':   'fadeIn .3s ease-out',
        'slide-up':  'slideUp .35s cubic-bezier(.16,1,.3,1)',
        'slide-in':  'slideIn .3s ease-out',
        'pulse-glow':'pulseGlow 2.5s ease-in-out infinite',
        'shimmer':   'shimmer 2.5s linear infinite',
      },
      keyframes: {
        fadeIn:    { from: {opacity:'0'}, to: {opacity:'1'} },
        slideUp:   { from: {opacity:'0', transform:'translateY(12px)'}, to: {opacity:'1', transform:'translateY(0)'} },
        slideIn:   { from: {opacity:'0', transform:'translateX(30px)'}, to: {opacity:'1', transform:'translateX(0)'} },
        pulseGlow: { '0%,100%': {boxShadow:'0 0 20px rgba(232,185,35,.15)'}, '50%': {boxShadow:'0 0 40px rgba(232,185,35,.35)'} },
        shimmer:   { '0%': {backgroundPosition:'-200% 0'}, '100%': {backgroundPosition:'200% 0'} },
      }
    }
  }
}
</script>
<style>
  html, body { background: #0a0a0f; color: #f5f5fa; }
  ::-webkit-scrollbar { width: 10px; height: 10px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #1a1a26; border-radius: 10px; border: 2px solid #0a0a0f; }
  ::-webkit-scrollbar-thumb:hover { background: #2a2a3d; }
  .glow-gold  { box-shadow: 0 0 30px -5px rgba(232,185,35,.35); }
  .glow-gold-strong { box-shadow: 0 0 50px -5px rgba(232,185,35,.55); }
  .glow-ok    { box-shadow: 0 0 25px -5px rgba(62,207,142,.35); }
  .glow-bad   { box-shadow: 0 0 25px -5px rgba(240,71,71,.35); }
  .glow-warn  { box-shadow: 0 0 25px -5px rgba(245,166,35,.35); }
  .glow-purp  { box-shadow: 0 0 25px -5px rgba(167,139,250,.35); }
  .card {
    background: #12121a;
    border: 1px solid rgba(255,255,255,.04);
    border-radius: 16px;
    transition: all .25s cubic-bezier(.16,1,.3,1);
  }
  .card:hover {
    border-color: rgba(232,185,35,.2);
    box-shadow: 0 12px 40px -12px rgba(0,0,0,.7), 0 0 30px -10px rgba(232,185,35,.15);
  }
  .stat-card:hover { transform: translateY(-3px); }
  .row {
    background: #12121a;
    border: 1px solid rgba(255,255,255,.04);
    border-radius: 14px;
    transition: all .2s cubic-bezier(.16,1,.3,1);
    cursor: pointer;
  }
  .row:hover {
    background: #1a1a26;
    border-color: rgba(232,185,35,.25);
    box-shadow: 0 8px 30px -10px rgba(0,0,0,.6), 0 0 25px -12px rgba(232,185,35,.25);
    transform: translateY(-1px);
  }
  .row.selected {
    background: #1a1a26;
    border-color: #e8b923;
    box-shadow: 0 0 30px -8px rgba(232,185,35,.5), inset 0 0 20px -10px rgba(232,185,35,.2);
  }
  .btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: 100px;
    font-weight: 600; font-size: 12px; cursor: pointer;
    transition: all .2s cubic-bezier(.16,1,.3,1);
    border: 1px solid transparent; user-select: none;
  }
  .btn-gold { background: linear-gradient(135deg, #e8b923, #f7cf57); color: #1a1408; }
  .btn-gold:hover { box-shadow: 0 0 30px -5px rgba(232,185,35,.7); transform: translateY(-1px); }
  .btn-ghost { background: #1a1a26; color: #f5f5fa; border-color: rgba(255,255,255,.06); }
  .btn-ghost:hover { background: #232332; border-color: rgba(232,185,35,.4); box-shadow: 0 0 25px -8px rgba(232,185,35,.4); transform: translateY(-1px); }
  .btn-ok { background: #3ecf8e; color: #0a1d14; }
  .btn-ok:hover { box-shadow: 0 0 35px -5px rgba(62,207,142,.7); transform: translateY(-1px); }
  .btn-bad { background: #f04747; color: #1e0707; }
  .btn-bad:hover { box-shadow: 0 0 35px -5px rgba(240,71,71,.7); transform: translateY(-1px); }
  .btn-warn { background: #f5a623; color: #1a1204; }
  .btn-warn:hover { box-shadow: 0 0 35px -5px rgba(245,166,35,.7); transform: translateY(-1px); }
  .btn-subtle { background: transparent; color: #a0a0b8; }
  .btn-subtle:hover { background: #1a1a26; color: #f5f5fa; }
  .chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 100px;
    font-size: 10px; font-weight: 700; letter-spacing: .02em;
  }
  .nav-item {
    display: flex; align-items: center; gap: 14px;
    padding: 11px 14px; border-radius: 10px;
    font-size: 13px; cursor: pointer; user-select: none;
    transition: all .18s cubic-bezier(.16,1,.3,1);
    color: #a0a0b8;
  }
  .nav-item:hover { background: #1a1a26; color: #f5f5fa; }
  .nav-item.active {
    background: #1a1a26; color: #f5f5fa;
    font-weight: 700; box-shadow: inset 0 0 20px -12px rgba(232,185,35,.35);
  }
  .nav-item.active svg { color: #e8b923; filter: drop-shadow(0 0 6px rgba(232,185,35,.6)); }
  .day-pill {
    width: 34px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 100px;
    border: 1px solid #2a2a3d;
    font-size: 11px; font-weight: 700; color: #a0a0b8;
    cursor: pointer; transition: all .18s cubic-bezier(.16,1,.3,1);
    user-select: none;
  }
  .day-pill:hover {
    border-color: rgba(232,185,35,.5);
    color: #e8b923;
    box-shadow: 0 0 20px -5px rgba(232,185,35,.4);
    transform: translateY(-1px);
  }
  .day-pill.today { border-color: #e8b923; color: #e8b923; }
  .day-pill.active { background: linear-gradient(135deg, #e8b923, #f7cf57); color: #1a1408; border-color: transparent; box-shadow: 0 0 25px -3px rgba(232,185,35,.7); }
  .mult-pill {
    width: 38px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 100px;
    border: 1px solid #2a2a3d;
    font-size: 11px; font-weight: 700; color: #a0a0b8;
    cursor: pointer; transition: all .18s cubic-bezier(.16,1,.3,1);
    user-select: none;
  }
  .mult-pill:hover {
    border-color: rgba(232,185,35,.5);
    color: #e8b923;
    box-shadow: 0 0 20px -5px rgba(232,185,35,.4);
    transform: translateY(-1px);
  }
  .mult-pill.active { background: linear-gradient(135deg, #e8b923, #f7cf57); color: #1a1408; border-color: transparent; box-shadow: 0 0 25px -3px rgba(232,185,35,.7); }
  .filter-pill {
    padding: 7px 14px; border-radius: 100px;
    font-size: 11px; font-weight: 700;
    border: 1px solid; cursor: pointer;
    transition: all .18s cubic-bezier(.16,1,.3,1);
    user-select: none;
  }
  .filter-pill:hover { transform: translateY(-1px); box-shadow: 0 0 20px -5px currentColor; }
  .modal-bg {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.7);
    backdrop-filter: blur(10px);
    z-index: 100;
    display: flex; align-items: center; justify-content: center;
    animation: fadeIn .2s ease-out;
  }
  .modal {
    background: #12121a;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 20px;
    max-height: 90vh; overflow: auto;
    box-shadow: 0 30px 80px rgba(0,0,0,.8), 0 0 60px -20px rgba(232,185,35,.2);
    animation: slideUp .3s cubic-bezier(.16,1,.3,1);
  }
  .field {
    width: 100%;
    background: #1a1a26;
    border: 1px solid #2a2a3d;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 13px;
    color: #f5f5fa;
    outline: none;
    transition: all .18s;
    font-family: inherit;
  }
  .field:focus {
    border-color: #e8b923;
    box-shadow: 0 0 0 3px rgba(232,185,35,.15), 0 0 25px -5px rgba(232,185,35,.3);
  }
  .field::placeholder { color: #45455c; }
  select.field {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23e8b923' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
  }
  .progress-track {
    height: 6px; background: #1e1e2e;
    border-radius: 100px; overflow: hidden;
    position: relative;
  }
  .progress-fill {
    height: 100%; border-radius: 100px;
    background: linear-gradient(90deg, #e8b923, #f7cf57);
    box-shadow: 0 0 15px rgba(232,185,35,.5);
    transition: width .6s cubic-bezier(.16,1,.3,1);
    position: relative; overflow: hidden;
  }
  .progress-fill::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.4), transparent);
    background-size: 200% 100%;
    animation: shimmer 2.5s linear infinite;
  }
  .avatar {
    width: 44px; height: 44px; border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 17px;
    transition: all .2s;
  }
  .row:hover .avatar { transform: scale(1.08); }
  .day-dot {
    width: 10px; height: 10px; border-radius: 5px;
    border: 1px solid #2a2a3d;
    transition: all .18s;
  }
  .day-dot.done { background: #3ecf8e; border-color: #3ecf8e; box-shadow: 0 0 8px rgba(62,207,142,.6); }
  .day-dot.today { border-color: #e8b923; box-shadow: 0 0 8px rgba(232,185,35,.6); }
  .toast {
    position: fixed; top: 20px; right: 20px; z-index: 200;
    padding: 12px 18px; border-radius: 12px;
    font-size: 13px; font-weight: 600;
    backdrop-filter: blur(20px);
    animation: slideIn .3s cubic-bezier(.16,1,.3,1);
    box-shadow: 0 15px 40px rgba(0,0,0,.6);
  }
  .toast.ok  { background: rgba(62,207,142,.15); border: 1px solid rgba(62,207,142,.4); color: #3ecf8e; }
  .toast.err { background: rgba(240,71,71,.15); border: 1px solid rgba(240,71,71,.4); color: #f04747; }
  .checkbox {
    width: 20px; height: 20px; border-radius: 6px;
    border: 2px solid #2a2a3d;
    transition: all .18s;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .row:hover .checkbox { border-color: rgba(232,185,35,.5); }
  .row.selected .checkbox { background: linear-gradient(135deg, #e8b923, #f7cf57); border-color: transparent; box-shadow: 0 0 15px -3px rgba(232,185,35,.7); }
  .stat-value {
    font-size: 34px; font-weight: 800; line-height: 1;
    letter-spacing: -.03em;
    font-variant-numeric: tabular-nums;
  }
  .floating-bar {
    position: sticky; bottom: 20px;
    background: rgba(18,18,26,.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(232,185,35,.3);
    border-radius: 16px;
    padding: 12px 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,.7), 0 0 40px -10px rgba(232,185,35,.3);
    animation: slideUp .3s cubic-bezier(.16,1,.3,1);
    z-index: 30;
  }
  @media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { display: block !important; position: absolute; left: 0; top: 0; width: 100%; padding: 40px; }
    body { background: white !important; color: black !important; }
    .no-print { display: none !important; }
  }
</style>
</head>
<body class="font-sans antialiased overflow-hidden">

<div id="app"></div>
<div id="modal-root"></div>
<div id="toast-root"></div>
<div id="print-area" style="display:none"></div>

<script>
// ═══════════════════════════════════════════════════════════════
//  STATE & HELPERS
// ═══════════════════════════════════════════════════════════════
const DIRECCION = 'Dirección';
const ROLES = { regular:'Usuario Regular', delegado:'Delegado', administrador:'Administrador', invitado:'Invitado' };
const ROLES_ORDER = ['regular','delegado','administrador','invitado'];
const ROLES_OBLIG = ['regular','delegado'];
const ROLES_LIBRES = ['administrador','invitado'];
const ROLE_COLORS = { regular:'info', delegado:'purp', administrador:'warn', invitado:'t3' };
const DAYS_SHORT = ['L','M','X','J','V','S','D'];
const DAYS_FULL  = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

const state = {
  data: <?= json_encode($initialData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  view: 'panel',
  group: '',
  week: '',
  selected: new Set(),
  dayFilter: null,   // null = all, else 0-6
  markMult: 1,
  search: '',
  statusFilter: 'todos',
  currentNoteId: null,
  detailGroup: null,
  selectedMemberId: null,
};

// ── Date helpers ───────────────────────────────────────────────
function pad(n){ return n.toString().padStart(2,'0'); }
function formatISO(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate()); }
function weekKey(d = new Date()){
  const day = d.getDay();            // 0=Sun..6=Sat
  const s = new Date(d);
  s.setDate(s.getDate() - day);       // go back to Sunday
  return formatISO(s);
}
function weekRange(key){
  const s = new Date(key + 'T00:00:00');
  const e = new Date(s); e.setDate(s.getDate() + 6);
  return [s, e];
}
function weekLabel(key){
  const [s,e] = weekRange(key);
  const fmt = d => pad(d.getDate())+' '+['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'][d.getMonth()]+' '+d.getFullYear();
  return fmt(s) + ' — ' + fmt(e);
}
function todayIdx(){ return (new Date().getDay() + 6) % 7; }  // 0=Mon..6=Sun

// ── Participation helpers ──────────────────────────────────────
function partOf(mid, week, group){
  try { return state.data.weeks[week][group].participation[mid] || null; }
  catch(e){ return null; }
}
function partStatus(mid, week, group){
  const v = partOf(mid, week, group);
  if (!v) return null;
  if (typeof v === 'string') return v;
  return v.status || 'si';
}
function partDays(mid, week, group){
  const v = partOf(mid, week, group);
  if (!v || typeof v === 'string') return [];
  return v.days || [];
}
function partMult(mid, week, group){
  const v = partOf(mid, week, group);
  if (!v || typeof v === 'string') return v ? 1 : 0;
  return v.mult || 1;
}
function weekPoints(week, group){
  try {
    const p = state.data.weeks[week][group].participation || {};
    return Object.values(p).reduce((sum, v) => {
      if (!v) return sum;
      if (typeof v === 'string') return sum + (v === 'si' ? 1 : 0);
      return sum + (v.status === 'si' ? (v.mult || 1) : 0);
    }, 0);
  } catch(e){ return 0; }
}

function groupList(){
  return Object.keys(state.data.groups).sort();
}
function operativeGroups(){
  return groupList().filter(g => g !== DIRECCION);
}
function membersOf(g){
  try { return state.data.groups[g].members || []; } catch(e){ return []; }
}
function allMembers(){
  const out = [];
  for (const g of groupList()) for (const m of membersOf(g)) out.push({...m, _group: g});
  return out;
}

// ── API ────────────────────────────────────────────────────────
async function api(action, payload = {}){
  const r = await fetch('api.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action, payload})
  });
  const j = await r.json();
  if (!j.ok) { toast(j.error || 'Error', 'err'); throw new Error(j.error); }
  if (j.data) state.data = j.data;
  return j;
}

// ── Toast ──────────────────────────────────────────────────────
function toast(msg, type='ok'){
  const el = document.createElement('div');
  el.className = 'toast ' + type;
  el.textContent = msg;
  document.getElementById('toast-root').appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .3s, transform .3s';
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    setTimeout(() => el.remove(), 300);
  }, 2400);
}

// ── Escape ─────────────────────────────────────────────────────
function esc(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

// ── Animated counter ───────────────────────────────────────────
function animCount(el, target){
  const start = parseInt(el.textContent) || 0;
  if (start === target){ el.textContent = target; return; }
  const t0 = performance.now(), dur = 500;
  const delta = target - start;
  function tick(now){
    const p = Math.min((now - t0) / dur, 1);
    const eased = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.round(start + delta * eased);
    if (p < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · SIDEBAR
// ═══════════════════════════════════════════════════════════════
const VIEWS = [
  ['panel',    'Panel semanal',    'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z'],
  ['miembros', 'Miembros',         'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm14 14v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
  ['grupos',   'Grupos',           'M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'],
  ['destac',   'Destacados',       'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z'],
  ['movs',     'Movimientos',      'M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5'],
  ['notas',    'Notas',            'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z'],
  ['ante',     'Antecedentes',     'M3 3v5h5M3.05 13A9 9 0 1 0 6 5.3L3 8M12 7v5l4 2'],
  ['export',   'Exportar',         'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3'],
];

function renderSidebar(){
  const g = state.group;
  const totalMembers = allMembers().length;
  const totalPoints = groupList().reduce((sum, gname) => {
    let pts = 0;
    for (const wk of Object.keys(state.data.weeks)) {
      pts += weekPoints(wk, gname);
    }
    return sum + pts;
  }, 0);

  return `
    <aside class="w-[236px] flex-shrink-0 bg-surf border-r border-line flex flex-col">
      <div class="p-6 pb-2">
        <div class="flex items-center gap-2.5 mb-1">
          <span class="text-2xl text-gold drop-shadow-[0_0_12px_rgba(232,185,35,.7)]">◆</span>
          <span class="text-lg font-bold tracking-tight">Gestión</span>
        </div>
        <p class="text-[9px] font-bold tracking-[.25em] text-t4">GRUPOS</p>
      </div>

      <nav class="px-3 py-4 flex flex-col gap-1 overflow-y-auto">
        ${VIEWS.map(([k,label,path]) => `
          <div class="nav-item ${state.view===k?'active':''}" data-nav="${k}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="${path}"/>
            </svg>
            <span>${label}</span>
          </div>
        `).join('')}
      </nav>

      <div class="mt-auto p-6 border-t border-line">
        <div class="mb-4">
          <p class="text-[9px] font-bold tracking-[.25em] text-t4 mb-1">SEMANA ACTUAL</p>
          <p class="text-[11px] font-bold text-t2">${weekLabel(state.week)}</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-surf2 rounded-lg p-2.5 border border-line">
            <p class="text-[10px] text-t4 font-bold tracking-wider">MIEMBROS</p>
            <p class="text-[18px] font-bold text-gold">${totalMembers}</p>
          </div>
          <div class="bg-surf2 rounded-lg p-2.5 border border-line">
            <p class="text-[10px] text-t4 font-bold tracking-wider">PUNTOS</p>
            <p class="text-[18px] font-bold text-purp">${totalPoints}</p>
          </div>
        </div>
        <p class="text-[10px] text-t4 font-mono mt-4">${new Date().toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit', second:'2-digit'})}</p>
      </div>
    </aside>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · HEADER
// ═══════════════════════════════════════════════════════════════
const TITLES = {
  panel:    ['Panel semanal',  'Participación y puntos de la semana'],
  miembros: ['Miembros',       'Registrar, editar y mover integrantes'],
  grupos:   ['Grupos',         'Nombre, descripción, admin y delegados'],
  destac:   ['Destacados',     'Ranking por puntos acumulados'],
  movs:     ['Movimientos',    'Traslados y castigos registrados'],
  notas:    ['Notas',          'Notas de la comunidad'],
  ante:     ['Antecedentes',   'Historial por miembro'],
  export:   ['Exportar',       'Reporte semanal en PDF'],
};

function renderHeader(){
  const [t, s] = TITLES[state.view] || ['', ''];
  const groups = operativeGroups();
  const weeks = new Set(Object.keys(state.data.weeks));
  weeks.add(weekKey());
  weeks.add(state.week);
  const sortedWeeks = Array.from(weeks).sort().reverse();

  return `
    <header class="flex items-start justify-between px-9 pt-7 pb-6 flex-shrink-0">
      <div>
        <h1 class="text-[26px] font-bold tracking-tight leading-none mb-1.5">${t}</h1>
        <p class="text-[12px] text-t3">${s}</p>
      </div>
      <div class="flex items-center gap-2.5">
        <select class="field !py-2 !text-[12px] !w-auto" data-sel="group">
          ${groups.map(g => `<option value="${esc(g)}" ${g===state.group?'selected':''}>${esc(g)}</option>`).join('')}
        </select>
        <select class="field !py-2 !text-[12px] !w-auto" data-sel="week">
          ${sortedWeeks.map(w => `<option value="${w}" ${w===state.week?'selected':''}>${weekLabel(w)}${w===weekKey()?' · actual':''}</option>`).join('')}
        </select>
        <button class="btn btn-ghost" data-action="goto-hoy">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Hoy
        </button>
      </div>
    </header>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · PANEL
// ═══════════════════════════════════════════════════════════════
function memberRowHTML(m){
  const mid = m.id;
  const g = state.group;
  const week = state.week;
  const status = partStatus(mid, week, g);
  const days = partDays(mid, week, g);
  const mult = partMult(mid, week, g);
  const isSel = state.selected.has(mid);
  const isCastig = m.castigado || g === DIRECCION;
  const today = todayIdx();
  const roleColor = { regular:'info', delegado:'purp', administrador:'warn', invitado:'t3' }[m.role] || 't3';

  let hist = {tot:0, si:0};
  for (const wk of Object.keys(state.data.weeks)) {
    const st = partStatus(mid, wk, g);
    if (st === 'si' || st === 'no') { hist.tot++; if (st==='si') hist.si++; }
  }
  const histStr = hist.tot ? `${hist.si}/${hist.tot} semanas (${Math.round(hist.si/hist.tot*100)}%)` : 'Sin historial';

  let chipTxt, chipColor, chipBg, chipBorder;
  if (isCastig) { chipTxt='CASTIGADO'; chipColor='bad'; }
  else if (status === 'si') { chipTxt = mult>1 ? `PARTICIPÓ ×${mult}` : 'PARTICIPÓ'; chipColor='ok'; }
  else if (status === 'no') { chipTxt='NO PARTICIPÓ'; chipColor='bad'; }
  else { chipTxt='PENDIENTE'; chipColor='t3'; }

  const chipClasses = {
    ok:  'bg-ok/10 text-ok border border-ok/30 shadow-[0_0_15px_-3px_rgba(62,207,142,.4)]',
    bad: 'bg-bad/10 text-bad border border-bad/30 shadow-[0_0_15px_-3px_rgba(240,71,71,.4)]',
    t3:  'bg-surf3 text-t3 border border-line2',
  }[chipColor];

  return `
    <div class="row ${isSel?'selected':''} p-4 flex items-center gap-4" data-member="${mid}">
      <div class="checkbox ${isSel?'':''}">${isSel?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#1a1408" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>':''}</div>
      <div class="avatar bg-${roleColor}/15 text-${roleColor}">${esc(m.name[0]?.toUpperCase() || '?')}</div>
      <div class="flex-1 min-w-0">
        <p class="text-[14px] font-bold truncate">${esc(m.name)}</p>
        <p class="text-[11px] text-t3 truncate">${esc(ROLES[m.role] || m.role)} · ${histStr}</p>
        <div class="flex gap-1 mt-1.5">
          ${[0,1,2,3,4,5,6].map(i => {
            const done = days.includes(i);
            const isToday = i === today;
            return `<div class="day-dot ${done?'done':''} ${isToday?'today':''}" title="${DAYS_FULL[i]}${isToday?' · HOY':''}"></div>`;
          }).join('')}
        </div>
      </div>
      <span class="chip ${chipClasses}">${chipTxt}</span>
    </div>
  `;
}

function renderPanel(){
  const g = state.group;
  const week = state.week;
  const members = membersOf(g);
  const wg = (state.data.weeks[week] && state.data.weeks[week][g]) || {participation:{}, incidents:[]};
  const part = wg.participation || {};
  const inc = wg.incidents || [];

  let si=0, no=0;
  for (const m of members) {
    const st = partStatus(m.id, week, g);
    if (st === 'si') si++; else if (st === 'no') no++;
  }
  const pts = weekPoints(week, g);
  const oblig = members.filter(m => ROLES_OBLIG.includes(m.role));
  const cump = oblig.filter(m => partStatus(m.id, week, g) === 'si').length;
  const pct = oblig.length ? Math.round(cump / oblig.length * 100) : 0;
  const exentos = members.filter(m => ROLES_LIBRES.includes(m.role)).length;

  // Aplicar filtros
  let lista = members.slice().sort((a,b) => {
    const aLib = ROLES_LIBRES.includes(a.role);
    const bLib = ROLES_LIBRES.includes(b.role);
    if (aLib !== bLib) return aLib ? 1 : -1;
    return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
  });
  const q = state.search.toLowerCase().trim();
  if (q) lista = lista.filter(m => m.name.toLowerCase().includes(q));
  const st = state.statusFilter;
  if (st === 'pendiente') lista = lista.filter(m => !['si','no'].includes(partStatus(m.id, week, g)) && !(m.castigado || g === DIRECCION));
  else if (st === 'marcado') lista = lista.filter(m => ['si','no'].includes(partStatus(m.id, week, g)));
  else if (st === 'castigo') lista = lista.filter(m => m.castigado || g === DIRECCION);
  if (state.dayFilter !== null) lista = lista.filter(m => partDays(m.id, week, g).includes(state.dayFilter));

  const today = todayIdx();
  const dayPillsHTML = `
    <div class="flex items-center gap-1.5">
      <div class="day-pill ${state.dayFilter===null?'active':''}" data-day="all">Todos</div>
      ${[0,1,2,3,4,5,6].map(i => `
        <div class="day-pill ${state.dayFilter===i?'active':''} ${i===today?'today':''}" data-day="${i}">${DAYS_SHORT[i]}</div>
      `).join('')}
    </div>
  `;

  const multPillsHTML = `
    <div class="flex items-center gap-1.5">
      ${[1,2,3,4,5].map(m => `<div class="mult-pill ${state.markMult===m?'active':''}" data-mult="${m}">×${m}</div>`).join('')}
    </div>
  `;

  const filterPill = (label, key, color) => {
    const active = state.statusFilter === key;
    const cls = active
      ? `bg-${color}/20 border-${color} text-${color} shadow-[0_0_20px_-5px_currentColor]`
      : `border-line2 text-${color} hover:bg-${color}/10`;
    return `<div class="filter-pill ${cls}" data-filter="${key}">${label}</div>`;
  };

  const activeFilters = [];
  if (q) activeFilters.push(`nombre "${q}"`);
  if (st !== 'todos') activeFilters.push(`estado=${st}`);
  if (state.dayFilter !== null) activeFilters.push(`día=${DAYS_FULL[state.dayFilter]}`);
  const filterInfo = activeFilters.length
    ? `${lista.length} de ${members.length} · ${activeFilters.join(' · ')}`
    : '';

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9">
      <!-- STATS -->
      <div class="grid grid-cols-4 gap-3.5 mb-4">
        ${statCardHTML('Miembros totales', 'people', 'gold', members.length, 'total')}
        ${statCardHTML('Participaron', 'check-circle', 'ok', si, 'si')}
        ${statCardHTML('No participaron', 'x-circle', 'bad', no, 'no')}
        ${statCardHTML('Puntos totales', 'star', 'purp', pts, 'pts')}
      </div>

      <!-- PROGRESO -->
      <div class="card p-5 mb-4 stat-card">
        <div class="flex items-center justify-between mb-3">
          <p class="text-[9px] font-bold tracking-[.2em] text-t4">PARTICIPACIÓN DE OBLIGADOS</p>
          <p class="text-[18px] font-bold text-gold drop-shadow-[0_0_15px_rgba(232,185,35,.5)]">${pct}%</p>
        </div>
        <div class="progress-track">
          <div class="progress-fill" style="width: ${pct}%"></div>
        </div>
        <p class="text-[11px] text-t3 mt-3">${cump} de ${oblig.length} obligados participaron · ${pts} puntos · ${exentos} exentos</p>
      </div>

      <!-- DÍA + MULT -->
      <div class="card p-4 mb-4 flex items-center gap-4 flex-wrap">
        <span class="text-[9px] font-bold tracking-[.2em] text-t4">DÍA</span>
        ${dayPillsHTML}
        <span class="w-px h-6 bg-line2"></span>
        <span class="text-[9px] font-bold tracking-[.2em] text-t4">MULT</span>
        ${multPillsHTML}
        <div class="flex-1 text-right">
          <span class="text-[10px] text-t4">Los puntos se suman por multiplicador</span>
        </div>
      </div>

      <!-- ACCIONES -->
      <div class="flex items-center gap-2.5 mb-4 flex-wrap">
        <button class="btn btn-ok" data-action="mark-si">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Participó
        </button>
        <button class="btn btn-bad" data-action="mark-no">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          No participó
        </button>
        <button class="btn btn-warn" data-action="add-incident">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Incidente
        </button>
        <button class="btn btn-subtle" data-action="mark-clear">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
          Limpiar
        </button>
        <div class="flex-1"></div>
        <span class="text-[11px] text-t3">${filterInfo}</span>
      </div>

      <!-- FILTROS -->
      <div class="flex items-center gap-2 mb-4 flex-wrap">
        <div class="relative flex-1 max-w-md">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6a6a85" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" class="field !pl-10 !py-2.5" placeholder="Buscar miembro por nombre..." data-search value="${esc(state.search)}">
        </div>
        ${filterPill('Todos', 'todos', 't2')}
        ${filterPill('Pendientes', 'pendiente', 'warn')}
        ${filterPill('Ya marcados', 'marcado', 'ok')}
        ${filterPill('Castigados', 'castigo', 'bad')}
      </div>

      <!-- LISTA -->
      <div class="card p-2 flex flex-col gap-2" id="members-container">
        ${lista.length ? lista.map(m => memberRowHTML(m)).join('') : `
          <div class="text-center py-16">
            <p class="text-t4 text-[13px]">${q || st !== 'todos' || state.dayFilter !== null ? 'Sin resultados con los filtros activos' : 'Sin miembros en este grupo'}</p>
          </div>
        `}
      </div>
    </div>

    ${state.selected.size > 0 ? `
      <div class="floating-bar">
        <div class="flex items-center gap-4">
          <span class="chip bg-gold/20 text-gold border border-gold/40 shadow-[0_0_20px_-5px_rgba(232,185,35,.5)]">
            ${state.selected.size} seleccionado${state.selected.size>1?'s':''}
          </span>
          <div class="h-6 w-px bg-line2"></div>
          <button class="btn btn-ok" data-action="mark-si">Participó ×${state.markMult}</button>
          <button class="btn btn-bad" data-action="mark-no">No participó</button>
          <button class="btn btn-subtle" data-action="mark-clear">Limpiar</button>
          <div class="h-6 w-px bg-line2"></div>
          <button class="btn btn-subtle" data-action="unselect-all">Deseleccionar</button>
        </div>
      </div>
    ` : ''}
  `;
}

function statCardHTML(label, iconPath, color, value, id){
  const icons = {
    'people': 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm14 14v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
    'check-circle': 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3',
    'x-circle': 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM15 9l-6 6M9 9l6 6',
    'star': 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z',
  };
  return `
    <div class="card stat-card p-5">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-${color} drop-shadow-[0_0_10px_currentColor] mb-3">
        <path d="${icons[iconPath]}"/>
      </svg>
      <p class="stat-value text-t1" data-stat="${id}">${value}</p>
      <p class="text-[9px] font-bold tracking-[.2em] text-t4 mt-1">${label.toUpperCase()}</p>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · MIEMBROS
// ═══════════════════════════════════════════════════════════════
function renderMiembros(){
  const all = allMembers();
  const rows = all.map(m => {
    const isCastig = m.castigado || m._group === DIRECCION;
    const rolColor = { regular:'info', delegado:'purp', administrador:'warn', invitado:'t3' }[m.role] || 't3';
    const isSel = state.selectedMemberId === m.id;
    return `
      <tr class="border-b border-line hover:bg-surf2 transition-colors cursor-pointer ${isSel?'bg-surf2':''}" data-member-row="${m.id}">
        <td class="py-3.5 px-4"><div class="flex items-center gap-3">
          <div class="avatar !w-9 !h-9 !text-[14px] !rounded-lg bg-${rolColor}/15 text-${rolColor}">${esc(m.name[0]?.toUpperCase() || '?')}</div>
          <span class="font-semibold text-[13px]">${esc(m.name)}</span>
        </div></td>
        <td class="py-3.5 px-4 text-[12px] text-t2">${esc(m._group)}</td>
        <td class="py-3.5 px-4"><span class="text-[12px] font-semibold text-${rolColor}">${esc(ROLES[m.role] || m.role)}</span></td>
        <td class="py-3.5 px-4 text-[12px] text-t3">${ROLES_OBLIG.includes(m.role)?'Obligado':'Sin obligación'}</td>
        <td class="py-3.5 px-4"><span class="chip ${isCastig?'bg-bad/10 text-bad border border-bad/30':'bg-ok/10 text-ok border border-ok/30'}">${isCastig?'⚠ CASTIGADO':'ACTIVO'}</span></td>
        <td class="py-3.5 px-4 text-[11px] text-t3 font-mono">${esc(m.created || '—')}</td>
      </tr>
    `;
  }).join('');

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9">
      <div class="flex items-center gap-2.5 mb-4">
        <button class="btn btn-gold" data-action="new-member">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo miembro
        </button>
        <button class="btn btn-ghost" data-action="edit-member">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
          Editar
        </button>
        <button class="btn btn-ghost" data-action="move-member">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
          Mover / Castigar
        </button>
        <button class="btn btn-subtle" data-action="delete-member">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Eliminar
        </button>
        <div class="flex-1"></div>
        <span class="text-[11px] text-t3">${all.length} miembros · ${groupList().length} grupos</span>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-line text-left text-[9px] font-bold tracking-[.2em] text-t4">
              <th class="py-3.5 px-4">NOMBRE</th>
              <th class="py-3.5 px-4">GRUPO</th>
              <th class="py-3.5 px-4">ROL</th>
              <th class="py-3.5 px-4">OBLIGACIÓN</th>
              <th class="py-3.5 px-4">ESTADO</th>
              <th class="py-3.5 px-4">REGISTRO</th>
            </tr>
          </thead>
          <tbody>${rows || '<tr><td colspan="6" class="text-center py-12 text-t4">Sin miembros registrados</td></tr>'}</tbody>
        </table>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · GRUPOS
// ═══════════════════════════════════════════════════════════════
function memberName(mid){
  for (const m of allMembers()) if (m.id === mid) return m.name;
  return '—';
}

function renderGrupos(){
  const rows = groupList().map(g => {
    const gg = state.data.groups[g];
    const admin = gg.admin ? memberName(gg.admin) : '—';
    const delegados = (gg.delegados || []).map(memberName).join(', ') || '—';
    const isSel = state.detailGroup === g;
    const isDir = g === DIRECCION;
    return `
      <tr class="border-b border-line hover:bg-surf2 transition-colors ${isSel?'bg-surf2':''}">
        <td class="py-4 px-4 cursor-pointer" data-group-select="${esc(g)}">
          <span class="font-semibold text-[13px] ${isSel?'text-gold drop-shadow-[0_0_10px_rgba(232,185,35,.5)]':''}">${isDir?'⚠ ':''}${esc(g)}</span>
        </td>
        <td class="py-4 px-4 text-[12px] text-t3">${esc(gg.description || '—').slice(0,42)}</td>
        <td class="py-4 px-4 text-[12px] ${admin!=='—'?'text-warn':'text-t4'}">${esc(admin)}</td>
        <td class="py-4 px-4 text-[12px] ${delegados!=='—'?'text-purp':'text-t4'}">${esc(delegados).slice(0,42)}</td>
        <td class="py-4 px-4"><span class="chip bg-surf3 text-t2">${gg.members.length}</span></td>
      </tr>
    `;
  }).join('');

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9">
      <div class="flex items-center gap-2.5 mb-4">
        <button class="btn btn-gold" data-action="new-group">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo grupo
        </button>
        <button class="btn btn-ghost" data-action="edit-group">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
          Editar
        </button>
        <button class="btn btn-subtle" data-action="delete-group">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
          Eliminar
        </button>
        <div class="flex-1"></div>
        <span class="text-[11px] text-t3">${groupList().length} grupos</span>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-line text-left text-[9px] font-bold tracking-[.2em] text-t4">
              <th class="py-3.5 px-4">NOMBRE</th>
              <th class="py-3.5 px-4">DESCRIPCIÓN</th>
              <th class="py-3.5 px-4">ADMIN</th>
              <th class="py-3.5 px-4">DELEGADOS</th>
              <th class="py-3.5 px-4">MIEMBROS</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · DESTACADOS
// ═══════════════════════════════════════════════════════════════
function renderDestacados(){
  const ranking = [];
  for (const g of groupList()) {
    for (const m of membersOf(g)) {
      let tot=0, si=0, pts=0;
      for (const wk of Object.keys(state.data.weeks)) {
        const st = partStatus(m.id, wk, g);
        if (st === 'si' || st === 'no') {
          tot++;
          if (st === 'si') { si++; pts += partMult(m.id, wk, g); }
        }
      }
      ranking.push({ name: m.name, group: g, role: ROLES[m.role] || m.role, semanas: tot, si, pts, pct: tot ? Math.round(si/tot*100) : 0 });
    }
  }
  ranking.sort((a,b) => b.pts - a.pts || b.pct - a.pct || a.name.localeCompare(b.name));

  const totalPts = ranking.reduce((s,r) => s+r.pts, 0);
  const activos = ranking.filter(r => r.pts >= 8).length;

  const rows = ranking.map((r,i) => {
    let ins='—', col='t4', glow='';
    if (r.pts === 0) { ins = '— Sin registro'; col='t4'; }
    else if (r.pts >= 15) { ins = '🏆 Destacado'; col='gold'; glow='drop-shadow-[0_0_8px_rgba(232,185,35,.6)]'; }
    else if (r.pts >= 8) { ins = '🥇 Muy activo'; col='warn'; }
    else if (r.pts >= 4) { ins = '🥈 Activo'; col='t1'; }
    else { ins = '🥉 En progreso'; col='t3'; }
    return `
      <tr class="border-b border-line hover:bg-surf2 transition-colors">
        <td class="py-3.5 px-4 text-[11px] text-t4 font-mono">#${i+1}</td>
        <td class="py-3.5 px-4 font-semibold text-[13px]">${esc(r.name)}</td>
        <td class="py-3.5 px-4 text-[12px] text-t2">${esc(r.group)}</td>
        <td class="py-3.5 px-4 text-[11px] text-t3">${esc(r.role)}</td>
        <td class="py-3.5 px-4 text-[12px] text-t2">${r.semanas}</td>
        <td class="py-3.5 px-4 text-[14px] font-bold text-purp drop-shadow-[0_0_10px_rgba(167,139,250,.5)]">${r.pts}</td>
        <td class="py-3.5 px-4 text-[12px]">${r.pct}%</td>
        <td class="py-3.5 px-4 text-[12px] font-semibold text-${col} ${glow}">${ins}</td>
      </tr>
    `;
  }).join('');

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9">
      <div class="flex items-center justify-between mb-4">
        <span class="text-[11px] text-t3">${ranking.length} evaluados · ${activos} con alta constancia · ${totalPts} puntos globales</span>
        <span class="text-[10px] text-t3">🏆 ≥15  ·  🥇 ≥8  ·  🥈 ≥4  ·  🥉 ≥1</span>
      </div>
      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-line text-left text-[9px] font-bold tracking-[.2em] text-t4">
              <th class="py-3.5 px-4">#</th>
              <th class="py-3.5 px-4">MIEMBRO</th>
              <th class="py-3.5 px-4">GRUPO</th>
              <th class="py-3.5 px-4">ROL</th>
              <th class="py-3.5 px-4">SEMANAS</th>
              <th class="py-3.5 px-4">PUNTOS</th>
              <th class="py-3.5 px-4">%</th>
              <th class="py-3.5 px-4">INSIGNIA</th>
            </tr>
          </thead>
          <tbody>${rows || '<tr><td colspan="8" class="text-center py-12 text-t4">Sin datos aún</td></tr>'}</tbody>
        </table>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · MOVIMIENTOS
// ═══════════════════════════════════════════════════════════════
function renderMovimientos(){
  const movs = state.data.movements || [];
  const castigos = movs.filter(m => m.type === 'castigo').length;
  const rows = movs.map(mv => `
    <tr class="border-b border-line hover:bg-surf2 transition-colors">
      <td class="py-3.5 px-4 text-[11px] text-t3 font-mono">${esc(mv.date)}</td>
      <td class="py-3.5 px-4 font-semibold text-[13px]">${esc(mv.member_name)}</td>
      <td class="py-3.5 px-4"><span class="chip ${mv.type==='castigo'?'bg-bad/10 text-bad border border-bad/30':'bg-info/10 text-info border border-info/30'}">${mv.type==='castigo'?'⚠ CASTIGO':'→ TRASLADO'}</span></td>
      <td class="py-3.5 px-4 text-[12px] text-t2">${esc(mv.from)}</td>
      <td class="py-3.5 px-4 text-[12px] text-t2">${esc(mv.to)}</td>
      <td class="py-3.5 px-4 text-[12px] text-t3">${esc(mv.reason).slice(0,60)}</td>
    </tr>
  `).join('');

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9">
      <div class="flex items-center justify-between mb-4">
        <span class="text-[11px] text-t3">${movs.length} movimientos · ${castigos} castigos</span>
        <button class="btn btn-subtle" data-action="clear-movs">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
          Borrar historial
        </button>
      </div>
      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-line text-left text-[9px] font-bold tracking-[.2em] text-t4">
              <th class="py-3.5 px-4">FECHA</th>
              <th class="py-3.5 px-4">MIEMBRO</th>
              <th class="py-3.5 px-4">TIPO</th>
              <th class="py-3.5 px-4">DESDE</th>
              <th class="py-3.5 px-4">HACIA</th>
              <th class="py-3.5 px-4">MOTIVO</th>
            </tr>
          </thead>
          <tbody>${rows || '<tr><td colspan="6" class="text-center py-12 text-t4">Sin movimientos registrados</td></tr>'}</tbody>
        </table>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · NOTAS
// ═══════════════════════════════════════════════════════════════
function renderNotas(){
  const notes = state.data.notes || [];
  const cur = notes.find(n => n.id === state.currentNoteId) || null;

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9 flex flex-col">
      <div class="flex items-center gap-2.5 mb-4">
        <button class="btn btn-gold" data-action="new-note">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nueva nota
        </button>
        <button class="btn btn-ghost" data-action="save-note" ${!cur?'disabled style="opacity:.4"':''}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Guardar
        </button>
        <button class="btn btn-subtle" data-action="delete-note" ${!cur?'disabled style="opacity:.4"':''}>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
          Eliminar
        </button>
        <div class="flex-1"></div>
        <span class="text-[11px] text-t3">${notes.length} notas</span>
      </div>

      <div class="flex gap-4 flex-1 min-h-0">
        <div class="card p-4 w-[360px] flex-shrink-0 overflow-y-auto">
          ${notes.length ? notes.map(n => {
            const isSel = n.id === state.currentNoteId;
            return `
              <div class="p-3 rounded-lg cursor-pointer transition-all mb-1 ${isSel?'bg-surf2 border border-gold/40 shadow-[0_0_20px_-8px_rgba(232,185,35,.5)]':'hover:bg-surf2 border border-transparent'}" data-note="${esc(n.id)}">
                <p class="text-[13px] font-bold truncate">${esc(n.title)}</p>
                <p class="text-[10px] text-t3 mt-0.5">${esc(n.date)} · ${esc(n.group || '—')}</p>
              </div>
            `;
          }).join('') : '<p class="text-center text-t4 text-[12px] py-8">Sin notas</p>'}
        </div>

        <div class="card p-6 flex-1 flex flex-col min-w-0">
          ${cur ? `
            <p class="text-[20px] font-bold">${esc(cur.title)}</p>
            <p class="text-[11px] text-t4 mt-1 mb-5">${esc(cur.date)} · ${esc(cur.group || '—')}</p>
            <textarea class="field flex-1 !py-3 font-sans resize-none" data-note-body>${esc(cur.body)}</textarea>
          ` : `
            <div class="flex-1 flex items-center justify-center text-center">
              <p class="text-t4 text-[13px]">Selecciona una nota o crea una nueva</p>
            </div>
          `}
        </div>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · ANTECEDENTES
// ═══════════════════════════════════════════════════════════════
function renderAntecedentes(){
  const groups = groupList();
  const g = state.anteGroup || state.group;
  const ms = membersOf(g);
  const mid = state.anteMember || (ms[0]?.id || '');
  const items = mid ? (state.data.antecedents || []).filter(a => a.member_id === mid).sort((a,b) => b.date.localeCompare(a.date)) : [];

  const tipos = ['Observación','Llamado de atención','Reconocimiento','Falta','Logro','Acuerdo','Otro'];

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9 flex flex-col">
      <div class="flex items-center gap-2.5 mb-4">
        <select class="field !py-2 !w-auto" data-ante-group>
          ${groups.map(gg => `<option value="${esc(gg)}" ${gg===g?'selected':''}>${esc(gg)}</option>`).join('')}
        </select>
        <select class="field !py-2 !w-auto" data-ante-member>
          ${ms.map(m => `<option value="${esc(m.id)}" ${m.id===mid?'selected':''}>${esc(m.name)}</option>`).join('')}
        </select>
      </div>

      <div class="flex gap-4 flex-1 min-h-0">
        <div class="card p-6 flex-1 flex flex-col">
          <p class="text-[9px] font-bold tracking-[.2em] text-t4 mb-3">HISTORIAL</p>
          <div class="overflow-y-auto flex-1">
            ${items.length ? items.map(a => `
              <div class="mb-4 pb-4 border-b border-line last:border-0">
                <p class="text-[11px] text-t3 font-mono mb-1">▸ ${esc(a.date)} · <span class="text-gold">${esc(a.tipo)}</span></p>
                <p class="text-[12px] text-t1">${esc(a.text)}</p>
              </div>
            `).join('') : '<p class="text-t4 text-[12px]">Sin antecedentes registrados</p>'}
          </div>
        </div>

        <div class="card p-6 flex-1 flex flex-col">
          <p class="text-[18px] font-bold mb-4">Nuevo antecedente</p>
          <label class="text-[9px] font-bold tracking-[.2em] text-t4 mb-2">TIPO</label>
          <select class="field mb-4" data-ante-tipo>
            ${tipos.map(t => `<option>${t}</option>`).join('')}
          </select>
          <label class="text-[9px] font-bold tracking-[.2em] text-t4 mb-2">DESCRIPCIÓN</label>
          <textarea class="field flex-1 resize-none mb-4" data-ante-text placeholder="Escribe el detalle..."></textarea>
          <button class="btn btn-gold self-start" data-action="add-ante">Añadir antecedente</button>
        </div>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · EXPORTAR
// ═══════════════════════════════════════════════════════════════
function renderExportar(){
  const g = state.expGroup || state.group;
  const weeks = Array.from(new Set([...Object.keys(state.data.weeks), weekKey(), state.week])).sort().reverse();
  const w = state.expWeek || state.week;

  const ms = membersOf(g);
  const wg = (state.data.weeks[w] && state.data.weeks[w][g]) || {participation:{}, incidents:[]};
  const part = wg.participation || {};
  const inc = wg.incidents || [];
  const si = ms.filter(m => partStatus(m.id, w, g) === 'si').length;
  const no = ms.filter(m => partStatus(m.id, w, g) === 'no').length;
  const pts = weekPoints(w, g);

  return `
    <div class="flex-1 overflow-y-auto px-9 pb-9 flex flex-col">
      <div class="card p-6 mb-4">
        <div class="flex items-center gap-4 flex-wrap">
          <div>
            <label class="text-[9px] font-bold tracking-[.2em] text-t4 block mb-1.5">GRUPO</label>
            <select class="field !py-2" data-exp-group>
              ${groupList().map(gg => `<option value="${esc(gg)}" ${gg===g?'selected':''}>${esc(gg)}</option>`).join('')}
            </select>
          </div>
          <div>
            <label class="text-[9px] font-bold tracking-[.2em] text-t4 block mb-1.5">SEMANA</label>
            <select class="field !py-2" data-exp-week>
              ${weeks.map(ww => `<option value="${ww}" ${ww===w?'selected':''}>${weekLabel(ww)}${ww===weekKey()?' · actual':''}</option>`).join('')}
            </select>
          </div>
          <div class="flex-1"></div>
          <button class="btn btn-gold" data-action="export-pdf">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Generar PDF
          </button>
        </div>
      </div>

      <div class="card p-6 flex-1 flex flex-col overflow-hidden">
        <p class="text-[9px] font-bold tracking-[.2em] text-t4 mb-3">VISTA PREVIA</p>
        <pre class="font-mono text-[11px] text-t2 leading-relaxed overflow-auto flex-1">${esc(
`════════════════════════════════════════════════════════════════════════
  REPORTE SEMANAL — ${g}
  ${weekLabel(w)}
════════════════════════════════════════════════════════════════════════

  Participación ............ ${si}
  No participación ......... ${no}
  Puntos totales ........... ${pts}
  Incidentes ............... ${inc.length}

────────────────────────────────────────────────────────────────────────
  ${'NOMBRE'.padEnd(28)} ${'ROL'.padEnd(14)} ${'ESTADO'.padEnd(16)} PTS  DÍAS
────────────────────────────────────────────────────────────────────────
${ms.slice().sort((a,b) => a.name.localeCompare(b.name)).map(m => {
  const st = partStatus(m.id, w, g);
  const mt = partMult(m.id, w, g);
  const ds = partDays(m.id, w, g);
  let et = {si:'SÍ PARTICIPÓ', no:'NO PARTICIPÓ'}[st] || 'SIN REGISTRO';
  if (mt > 1) et += ` ×${mt}`;
  const dsStr = ds.length ? ds.sort().map(d => DAYS_SHORT[d]).join(' ') : '—';
  return `  ${m.name.slice(0,27).padEnd(28)} ${(ROLES[m.role]||'').slice(0,13).padEnd(14)} ${et.padEnd(16)} ${st==='si'?mt:'—'}    ${dsStr}`;
}).join('\n')}
${inc.length ? '\n────────────────────────────────────────────────────────────────────────\n  INCIDENTES\n────────────────────────────────────────────────────────────────────────\n' +
  inc.map(i => `  • ${memberName(i.member_id) || 'General'}: ${i.text}`).join('\n') : ''}
`)}</pre>
      </div>
    </div>
  `;
}

// ═══════════════════════════════════════════════════════════════
//  RENDER · MAIN
// ═══════════════════════════════════════════════════════════════
function render(){
  const view = state.view;
  let content = '';
  if (view === 'panel') content = renderPanel();
  else if (view === 'miembros') content = renderMiembros();
  else if (view === 'grupos') content = renderGrupos();
  else if (view === 'destac') content = renderDestacados();
  else if (view === 'movs') content = renderMovimientos();
  else if (view === 'notas') content = renderNotas();
  else if (view === 'ante') content = renderAntecedentes();
  else if (view === 'export') content = renderExportar();

  document.getElementById('app').innerHTML = `
    <div class="flex h-screen overflow-hidden">
      ${renderSidebar()}
      <main class="flex-1 flex flex-col min-w-0">
        ${renderHeader()}
        <div class="flex-1 flex flex-col min-h-0 animate-fade-in">
          ${content}
        </div>
      </main>
    </div>
  `;
  bindEvents();
}

// ═══════════════════════════════════════════════════════════════
//  EVENTS
// ═══════════════════════════════════════════════════════════════
function bindEvents(){
  // Nav
  document.querySelectorAll('[data-nav]').forEach(el => {
    el.addEventListener('click', () => {
      state.view = el.dataset.nav;
      if (state.view !== 'panel') state.selected.clear();
      render();
    });
  });

  // Selectores header
  document.querySelector('[data-sel="group"]')?.addEventListener('change', e => {
    state.group = e.target.value;
    state.selected.clear();
    render();
  });
  document.querySelector('[data-sel="week"]')?.addEventListener('change', e => {
    state.week = e.target.value;
    state.selected.clear();
    render();
  });
  document.querySelector('[data-action="goto-hoy"]')?.addEventListener('click', () => {
    state.week = weekKey();
    state.selected.clear();
    render();
  });

  // Panel - rows
  document.querySelectorAll('[data-member]').forEach(row => {
    row.addEventListener('click', e => {
      const mid = row.dataset.member;
      if (state.selected.has(mid)) state.selected.delete(mid);
      else state.selected.add(mid);
      render();
    });
  });

  // Panel - day pills
  document.querySelectorAll('[data-day]').forEach(el => {
    el.addEventListener('click', () => {
      if (el.dataset.day === 'all') state.dayFilter = null;
      else state.dayFilter = parseInt(el.dataset.day);
      render();
    });
  });

  // Panel - mult pills
  document.querySelectorAll('[data-mult]').forEach(el => {
    el.addEventListener('click', () => {
      state.markMult = parseInt(el.dataset.mult);
      render();
    });
  });

  // Panel - filter pills
  document.querySelectorAll('[data-filter]').forEach(el => {
    el.addEventListener('click', () => {
      state.statusFilter = el.dataset.filter;
      render();
    });
  });

  // Panel - search (preserve focus)
  const searchInput = document.querySelector('[data-search]');
  if (searchInput) {
    searchInput.addEventListener('input', e => {
      state.search = e.target.value;
      clearTimeout(window.__searchT);
      window.__searchT = setTimeout(render, 200);
    });
  }

  // Actions
  document.querySelectorAll('[data-action]').forEach(el => {
    el.addEventListener('click', e => {
      e.stopPropagation();
      handleAction(el.dataset.action);
    });
  });

  // Miembros - row select
  document.querySelectorAll('[data-member-row]').forEach(row => {
    row.addEventListener('click', () => {
      state.selectedMemberId = row.dataset.memberRow;
      render();
    });
  });

  // Grupos - select
  document.querySelectorAll('[data-group-select]').forEach(el => {
    el.addEventListener('click', () => {
      state.detailGroup = el.dataset.groupSelect;
      render();
    });
  });

  // Notas - select
  document.querySelectorAll('[data-note]').forEach(el => {
    el.addEventListener('click', async () => {
      // Guardar la actual si hay cambios
      const bodyEl = document.querySelector('[data-note-body]');
      if (bodyEl && state.currentNoteId) {
        const cur = (state.data.notes||[]).find(n => n.id === state.currentNoteId);
        if (cur && cur.body !== bodyEl.value) {
          await api('update_note', {id: state.currentNoteId, body: bodyEl.value});
        }
      }
      state.currentNoteId = el.dataset.note;
      render();
    });
  });

  // Antecedentes
  document.querySelector('[data-ante-group]')?.addEventListener('change', e => {
    state.anteGroup = e.target.value;
    state.anteMember = null;
    render();
  });
  document.querySelector('[data-ante-member]')?.addEventListener('change', e => {
    state.anteMember = e.target.value;
    render();
  });

  // Export
  document.querySelector('[data-exp-group]')?.addEventListener('change', e => {
    state.expGroup = e.target.value;
    render();
  });
  document.querySelector('[data-exp-week]')?.addEventListener('change', e => {
    state.expWeek = e.target.value;
    render();
  });
}

async function handleAction(action){
  try {
    if (action === 'mark-si' || action === 'mark-no' || action === 'mark-clear') {
      if (state.selected.size === 0) { toast('Selecciona al menos un miembro', 'err'); return; }
      const estado = { 'mark-si':'si', 'mark-no':'no', 'mark-clear':null }[action];
      const day = state.dayFilter !== null ? state.dayFilter : todayIdx();
      await api('mark', {
        week: state.week, group: state.group,
        mids: [...state.selected], estado, day, mult: state.markMult,
      });
      state.selected.clear();
      render();
      toast(estado === 'si' ? 'Participación registrada' : estado === 'no' ? 'Marcado como no participó' : 'Registro limpiado');
    }
    else if (action === 'unselect-all') {
      state.selected.clear(); render();
    }
    else if (action === 'add-incident') { openIncidentModal(); }
    else if (action === 'new-member') { openMemberModal(); }
    else if (action === 'edit-member') {
      if (!state.selectedMemberId) { toast('Selecciona un miembro primero', 'err'); return; }
      openMemberModal(state.selectedMemberId);
    }
    else if (action === 'delete-member') {
      if (!state.selectedMemberId) { toast('Selecciona un miembro primero', 'err'); return; }
      const m = allMembers().find(x => x.id === state.selectedMemberId);
      if (confirm(`¿Eliminar a ${m?.name || ''}?`)) {
        await api('delete_member', {id: state.selectedMemberId});
        state.selectedMemberId = null;
        render(); toast('Miembro eliminado');
      }
    }
    else if (action === 'move-member') {
      if (!state.selectedMemberId) { toast('Selecciona un miembro primero', 'err'); return; }
      openMoveModal(state.selectedMemberId);
    }
    else if (action === 'new-group') { openGroupModal(); }
    else if (action === 'edit-group') {
      if (!state.detailGroup) { toast('Clic en el nombre de un grupo primero', 'err'); return; }
      openGroupModal(state.detailGroup);
    }
    else if (action === 'delete-group') {
      if (!state.detailGroup || state.detailGroup === DIRECCION) return;
      if (confirm(`¿Eliminar '${state.detailGroup}'? Sus miembros van a Dirección.`)) {
        await api('delete_group', {name: state.detailGroup});
        state.detailGroup = null;
        render(); toast('Grupo eliminado');
      }
    }
    else if (action === 'clear-movs') {
      if (confirm('¿Borrar todo el historial de movimientos?')) {
        await api('clear_movements', {});
        render(); toast('Historial borrado');
      }
    }
    else if (action === 'new-note') { openNoteModal(); }
    else if (action === 'save-note') {
      const bodyEl = document.querySelector('[data-note-body]');
      if (!bodyEl || !state.currentNoteId) return;
      await api('update_note', {id: state.currentNoteId, body: bodyEl.value});
      render(); toast('Nota guardada');
    }
    else if (action === 'delete-note') {
      if (!state.currentNoteId) return;
      if (confirm('¿Eliminar esta nota?')) {
        await api('delete_note', {id: state.currentNoteId});
        state.currentNoteId = null;
        render(); toast('Nota eliminada');
      }
    }
    else if (action === 'add-ante') {
      const tipoEl = document.querySelector('[data-ante-tipo]');
      const textEl = document.querySelector('[data-ante-text]');
      if (!textEl.value.trim()) { toast('Escribe una descripción', 'err'); return; }
      if (!state.anteMember) { toast('Selecciona un miembro', 'err'); return; }
      await api('add_antecedent', {member_id: state.anteMember, tipo: tipoEl.value, text: textEl.value.trim()});
      render(); toast('Antecedente añadido');
    }
    else if (action === 'export-pdf') { exportPDF(); }
  } catch(err){ console.error(err); }
}

// ═══════════════════════════════════════════════════════════════
//  MODALES
// ═══════════════════════════════════════════════════════════════
function openModal(title, contentHTML, actionsHTML){
  const root = document.getElementById('modal-root');
  root.innerHTML = `
    <div class="modal-bg">
      <div class="modal w-[520px] p-7">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-[18px] font-bold">${title}</h2>
          <button class="text-t3 hover:text-t1 transition-colors" data-close>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="flex flex-col gap-4 mb-6">${contentHTML}</div>
        <div class="flex justify-end gap-2">${actionsHTML}</div>
      </div>
    </div>
  `;
  root.querySelector('[data-close]').addEventListener('click', closeModal);
  root.querySelector('.modal-bg').addEventListener('click', e => {
    if (e.target.classList.contains('modal-bg')) closeModal();
  });
}
function closeModal(){ document.getElementById('modal-root').innerHTML = ''; }

function fieldHTML(label, inner){ return `<div><label class="text-[9px] font-bold tracking-[.2em] text-t4 block mb-1.5">${label}</label>${inner}</div>`; }

function openMemberModal(mid = null){
  const editing = !!mid;
  let m = null, curGroup = state.group;
  if (editing) {
    for (const g of groupList()) {
      const f = membersOf(g).find(x => x.id === mid);
      if (f) { m = f; curGroup = g; break; }
    }
  }
  const groups = groupList();
  const html = `
    ${fieldHTML('NOMBRE COMPLETO', `<input class="field" data-f="name" value="${esc(m?.name || '')}" placeholder="Nombre del miembro">`)}
    ${fieldHTML('GRUPO', `<select class="field" data-f="group">${groups.map(g => `<option value="${esc(g)}" ${g===curGroup?'selected':''}>${esc(g)}</option>`).join('')}</select>`)}
    ${fieldHTML('ROL', `<select class="field" data-f="role">${ROLES_ORDER.map(k => `<option value="${k}" ${m?.role===k?'selected':''}>${ROLES[k]}</option>`).join('')}</select>`)}
  `;
  const actions = `
    <button class="btn btn-subtle" data-close2>Cancelar</button>
    <button class="btn btn-gold" data-ok>${editing?'Guardar':'Registrar'}</button>
  `;
  openModal(editing?'Editar miembro':'Nuevo miembro', html, actions);
  document.querySelector('[data-close2]').addEventListener('click', closeModal);
  document.querySelector('[data-ok]').addEventListener('click', async () => {
    const name = document.querySelector('[data-f="name"]').value.trim();
    const group = document.querySelector('[data-f="group"]').value;
    const role = document.querySelector('[data-f="role"]').value;
    if (!name) { toast('El nombre es obligatorio', 'err'); return; }
    if (editing) await api('edit_member', {id: mid, name, group, role});
    else await api('add_member', {name, group, role});
    closeModal(); render(); toast(editing?'Miembro actualizado':'Miembro registrado');
  });
}

function openMoveModal(mid){
  let m = null, from = null;
  for (const g of groupList()) {
    const f = membersOf(g).find(x => x.id === mid);
    if (f) { m = f; from = g; break; }
  }
  if (!m) return;
  const dests = groupList().filter(g => g !== from);
  const html = `
    <p class="text-[11px] text-t3">${esc(m.name)} · en ${esc(from)}</p>
    ${fieldHTML('TIPO', `<select class="field" data-f="type">
      <option value="traslado">Traslado normal</option>
      <option value="castigo">Castigo · Dirección</option>
    </select>`)}
    ${fieldHTML('DESTINO', `<select class="field" data-f="to">${dests.map(g => `<option value="${esc(g)}">${esc(g)}</option>`).join('')}</select>`)}
    ${fieldHTML('MOTIVO', `<textarea class="field" data-f="reason" rows="4" placeholder="Motivo..."></textarea>`)}
  `;
  const actions = `
    <button class="btn btn-subtle" data-close2>Cancelar</button>
    <button class="btn btn-gold" data-ok>Aplicar</button>
  `;
  openModal('Mover / Castigar', html, actions);
  const typeSel = document.querySelector('[data-f="type"]');
  const destSel = document.querySelector('[data-f="to"]');
  typeSel.addEventListener('change', () => {
    if (typeSel.value === 'castigo') {
      destSel.value = DIRECCION;
      destSel.disabled = true;
    } else {
      destSel.disabled = false;
      const first = dests.find(g => g !== DIRECCION);
      if (first) destSel.value = first;
    }
  });
  document.querySelector('[data-close2]').addEventListener('click', closeModal);
  document.querySelector('[data-ok]').addEventListener('click', async () => {
    const type = typeSel.value;
    const to = type === 'castigo' ? DIRECCION : destSel.value;
    const reason = document.querySelector('[data-f="reason"]').value.trim()
      || (type === 'castigo' ? 'Castigo disciplinario' : 'Traslado de grupo');
    if (to === from) { toast('Selecciona un destino diferente', 'err'); return; }
    await api('move_member', {id: mid, from, to, type, reason});
    closeModal(); render(); toast(type==='castigo'?'Castigo aplicado':'Traslado aplicado');
  });
}

function openIncidentModal(){
  const members = membersOf(state.group);
  const html = `
    ${fieldHTML('MIEMBRO IMPLICADO', `<select class="field" data-f="mid">
      <option value="">— General / grupal —</option>
      ${members.map(m => `<option value="${esc(m.id)}">${esc(m.name)}</option>`).join('')}
    </select>`)}
    ${fieldHTML('DESCRIPCIÓN', `<textarea class="field" data-f="text" rows="5" placeholder="Describe el incidente..."></textarea>`)}
  `;
  const actions = `
    <button class="btn btn-subtle" data-close2>Cancelar</button>
    <button class="btn btn-gold" data-ok>Guardar</button>
  `;
  openModal('Registrar incidente', html, actions);
  document.querySelector('[data-close2]').addEventListener('click', closeModal);
  document.querySelector('[data-ok]').addEventListener('click', async () => {
    const text = document.querySelector('[data-f="text"]').value.trim();
    if (!text) { toast('Escribe una descripción', 'err'); return; }
    await api('add_incident', {
      week: state.week, group: state.group,
      member_id: document.querySelector('[data-f="mid"]').value || null,
      text,
    });
    closeModal(); render(); toast('Incidente registrado');
  });
}

function openGroupModal(name = null){
  const editing = !!name;
  const g = editing ? state.data.groups[name] : null;
  const todos = allMembers();
  const html = `
    ${fieldHTML('NOMBRE', `<input class="field" data-f="name" value="${esc(name || '')}" ${editing && name===DIRECCION?'disabled':''}>`)}
    ${fieldHTML('DESCRIPCIÓN', `<textarea class="field" data-f="desc" rows="3">${esc(g?.description || '')}</textarea>`)}
    ${editing ? fieldHTML('ADMIN', `<select class="field" data-f="admin">
      <option value="">— Sin asignar —</option>
      ${todos.map(m => `<option value="${esc(m.id)}" ${g?.admin===m.id?'selected':''}>${esc(m.name)} (${esc(m._group)})</option>`).join('')}
    </select>`) : ''}
    ${editing ? `<div>
      <label class="text-[9px] font-bold tracking-[.2em] text-t4 block mb-1.5">DELEGADOS</label>
      <div class="max-h-40 overflow-y-auto bg-surf2 rounded-lg p-3 border border-line2">
        ${todos.map(m => `<label class="flex items-center gap-2 py-1.5 cursor-pointer hover:text-gold transition-colors">
          <input type="checkbox" data-deleg="${esc(m.id)}" ${g?.delegados?.includes(m.id)?'checked':''} class="accent-gold">
          <span class="text-[12px]">${esc(m.name)} <span class="text-t4">(${esc(m._group)})</span></span>
        </label>`).join('') || '<p class="text-t4 text-[12px]">Sin miembros registrados</p>'}
      </div>
    </div>` : ''}
  `;
  const actions = `
    <button class="btn btn-subtle" data-close2>Cancelar</button>
    <button class="btn btn-gold" data-ok>${editing?'Guardar':'Crear'}</button>
  `;
  openModal(editing?'Editar grupo':'Nuevo grupo', html, actions);
  document.querySelector('[data-close2]').addEventListener('click', closeModal);
  document.querySelector('[data-ok]').addEventListener('click', async () => {
    const newName = document.querySelector('[data-f="name"]').value.trim();
    const desc = document.querySelector('[data-f="desc"]').value.trim();
    if (!newName) { toast('El nombre es obligatorio', 'err'); return; }
    if (editing) {
      const admin = document.querySelector('[data-f="admin"]').value;
      const delegados = [...document.querySelectorAll('[data-deleg]')].filter(c => c.checked).map(c => c.dataset.deleg);
      await api('edit_group', {old_name: name, name: newName, description: desc, admin, delegados});
    } else {
      await api('add_group', {name: newName, description: desc});
    }
    closeModal(); render(); toast(editing?'Grupo actualizado':'Grupo creado');
  });
}

function openNoteModal(){
  const html = `
    ${fieldHTML('TÍTULO', `<input class="field" data-f="title" placeholder="Título de la nota">`)}
    ${fieldHTML('GRUPO', `<select class="field" data-f="group">
      <option>— General —</option>
      ${groupList().map(g => `<option ${g===state.group?'selected':''}>${esc(g)}</option>`).join('')}
    </select>`)}
    ${fieldHTML('CONTENIDO', `<textarea class="field" data-f="body" rows="8" placeholder="Escribe la nota..."></textarea>`)}
  `;
  const actions = `
    <button class="btn btn-subtle" data-close2>Cancelar</button>
    <button class="btn btn-gold" data-ok>Guardar</button>
  `;
  openModal('Nueva nota', html, actions);
  document.querySelector('[data-close2]').addEventListener('click', closeModal);
  document.querySelector('[data-ok]').addEventListener('click', async () => {
    await api('add_note', {
      title: document.querySelector('[data-f="title"]').value.trim() || 'Sin título',
      group: document.querySelector('[data-f="group"]').value,
      body: document.querySelector('[data-f="body"]').value,
    });
    closeModal(); render(); toast('Nota creada');
  });
}

// ═══════════════════════════════════════════════════════════════
//  PDF · Vía window.print()
// ═══════════════════════════════════════════════════════════════
function exportPDF(){
  const g = state.expGroup || state.group;
  const w = state.expWeek || state.week;
  const ms = membersOf(g);
  const wg = (state.data.weeks[w] && state.data.weeks[w][g]) || {participation:{}, incidents:[]};
  const part = wg.participation || {};
  const inc = wg.incidents || [];
  const [s, e] = weekRange(w);

  const si = ms.filter(m => partStatus(m.id, w, g) === 'si').length;
  const no = ms.filter(m => partStatus(m.id, w, g) === 'no').length;
  const pts = weekPoints(w, g);

  const rows = ms.slice().sort((a,b) => a.name.localeCompare(b.name)).map(m => {
    const st = partStatus(m.id, w, g);
    const mt = partMult(m.id, w, g);
    const ds = partDays(m.id, w, g);
    let et = {si:'SÍ PARTICIPÓ', no:'NO PARTICIPÓ'}[st] || 'SIN REGISTRO';
    if (mt > 1) et += ` ×${mt}`;
    const dsStr = ds.length ? ds.sort().map(d => DAYS_SHORT[d]).join(' ') : '—';
    const col = st==='si' ? '#3ecf8e' : st==='no' ? '#f04747' : '#a0a0b8';
    return `<tr>
      <td style="font-weight:600">${esc(m.name)}</td>
      <td>${esc(ROLES[m.role]||m.role)}</td>
      <td style="color:${col};font-weight:700">${et}</td>
      <td style="text-align:center;font-weight:700">${st==='si'?mt:'—'}</td>
      <td style="font-family:monospace">${dsStr}</td>
    </tr>`;
  }).join('');

  document.getElementById('print-area').innerHTML = `
    <div style="font-family:Inter,-apple-system,sans-serif;color:#0a0a0f;background:white;max-width:720px;margin:0 auto">
      <div style="background:#0a0a0f;color:white;padding:32px 40px;border-radius:12px 12px 0 0;position:relative;overflow:hidden">
        <div style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#e8b923"></div>
        <h1 style="font-size:32px;margin:0 0 6px 0;color:#e8b923;letter-spacing:-.02em">${esc(g)}</h1>
        <p style="margin:0;color:#dcdff0;font-size:13px">Semana del ${s.getDate()}/${s.getMonth()+1}/${s.getFullYear()} al ${e.getDate()}/${e.getMonth()+1}/${e.getFullYear()}</p>
        <p style="margin:8px 0 0 0;color:#b4bee0;font-size:10px">Generado: ${new Date().toLocaleString('es-MX')}</p>
        <div style="position:absolute;right:40px;top:32px;text-align:right">
          <p style="margin:0;font-size:18px;font-weight:800;color:#fff">REPORTE</p>
          <p style="margin:0;font-size:18px;font-weight:800;color:#e8b923">SEMANAL</p>
        </div>
      </div>
      <div style="background:#e8b923;height:5px"></div>

      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:24px 0">
        ${[
          ['PARTICIPARON', si, '#3ecf8e'],
          ['NO PARTICIPARON', no, '#f04747'],
          ['PUNTOS', pts, '#f5a623'],
          ['INCIDENTES', inc.length, '#a78bfa'],
        ].map(([lbl,val,col]) => `
          <div style="border:1px solid #e4e7ee;border-radius:10px;padding:14px 16px;background:white">
            <div style="height:3px;background:${col};margin:-14px -16px 12px -16px;border-radius:10px 10px 0 0"></div>
            <p style="margin:0;font-size:28px;font-weight:800;color:${col};letter-spacing:-.03em">${val}</p>
            <p style="margin:4px 0 0 0;font-size:8px;font-weight:700;letter-spacing:.15em;color:#8a91a3">${lbl}</p>
          </div>
        `).join('')}
      </div>

      <h2 style="font-size:15px;margin:0 0 10px 0;color:#0a0a0f">Detalle de miembros</h2>
      <table style="width:100%;border-collapse:collapse;font-size:11px">
        <thead>
          <tr style="background:#0a0a0f;color:#e8b923">
            <th style="text-align:left;padding:8px 10px;font-size:9px;letter-spacing:.1em">NOMBRE</th>
            <th style="text-align:left;padding:8px 10px;font-size:9px;letter-spacing:.1em">ROL</th>
            <th style="text-align:left;padding:8px 10px;font-size:9px;letter-spacing:.1em">PARTICIPACIÓN</th>
            <th style="text-align:center;padding:8px 10px;font-size:9px;letter-spacing:.1em">PTS</th>
            <th style="text-align:left;padding:8px 10px;font-size:9px;letter-spacing:.1em">DÍAS</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>

      ${inc.length ? `
        <div style="margin-top:24px">
          <div style="background:#e8b923;color:#0a0a0f;padding:8px 14px;font-size:11px;font-weight:800;letter-spacing:.1em;border-radius:6px 6px 0 0">INCIDENTES</div>
          ${inc.map(i => `<p style="margin:8px 0;padding:8px 14px;background:#faf7ed;border-radius:6px;font-size:11px"><b>• ${esc(memberName(i.member_id)||'General')}:</b> ${esc(i.text)}</p>`).join('')}
        </div>
      ` : ''}

      <p style="text-align:center;color:#8a91a3;font-size:9px;margin-top:40px;padding-top:16px;border-top:1px solid #e8b923">Sistema de Gestión de Grupos · Semana ${esc(w)}</p>
    </div>
  `;
  document.getElementById('print-area').style.display = 'block';
  setTimeout(() => {
    window.print();
    document.getElementById('print-area').style.display = 'none';
  }, 100);
}

// ═══════════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════════
(function init(){
  // Defaults
  const groups = Object.keys(state.data.groups || {}).filter(g => g !== DIRECCION).sort();
  state.group = groups[0] || DIRECCION;
  state.week = weekKey();
  state.anteGroup = state.group;

  // Recargar datos frescos desde el server
  api('load').then(() => {
    const groups2 = Object.keys(state.data.groups || {}).filter(g => g !== DIRECCION).sort();
    if (!groups2.includes(state.group)) state.group = groups2[0] || DIRECCION;
    render();
  }).catch(() => render());

  render();

  // Refresco automático cada 30s (por si cambió la semana)
  setInterval(() => {
    const wk = weekKey();
    if (wk !== state.week && state.week === weekKey()) {
      // nada
    }
  }, 30000);

  // Shortcut: Escape cierra modales
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });
})();
</script>
</body>
</html>