import * as THREE from 'three';
import { TEAMS, getTeam } from './data/teams.js';
import { createPitchScene } from './game/Pitch.js';
import { Match } from './game/Match.js';
import { TouchControls } from './game/Controls.js';

const canvas = document.getElementById('game-canvas');
const menu = document.getElementById('menu');
const hud = document.getElementById('hud');
const touchControls = document.getElementById('touch-controls');
const pauseOverlay = document.getElementById('pause-overlay');
const goalBanner = document.getElementById('goal-banner');
const resultScreen = document.getElementById('result-screen');

const renderer = new THREE.WebGLRenderer({
  canvas,
  antialias: true,
  powerPreference: 'high-performance',
});
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.outputColorSpace = THREE.SRGBColorSpace;

const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 250);
camera.position.set(0, 22, -28);

let scene = createPitchScene();
let match = null;
let selectedHomeId = null;
let selectedAwayId = TEAMS[1].id;
let raf = 0;
let lastT = 0;

const controls = new TouchControls({
  joystickZone: document.getElementById('joystick-zone'),
  knob: document.getElementById('joystick-knob'),
  base: document.getElementById('joystick-base'),
  buttons: {
    pass: document.getElementById('btn-pass'),
    shoot: document.getElementById('btn-shoot'),
    sprint: document.getElementById('btn-sprint'),
  },
});

// —— Menu setup ——
const teamGrid = document.getElementById('team-grid');
const opponentSelect = document.getElementById('opponent-select');
const btnStart = document.getElementById('btn-start');

function teamOvr(team) {
  const vals = team.players.map((p) => (p.pace + p.shooting + p.passing + p.defending + p.physical) / 5);
  return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
}

function renderTeamGrid() {
  teamGrid.innerHTML = '';
  for (const team of TEAMS) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'team-card' + (selectedHomeId === team.id ? ' selected' : '');
    btn.innerHTML = `
      <span class="badge" style="background:${team.colors.primary};color:${contrastText(team.colors.primary)}">${team.short}</span>
      <span class="label">${team.name}</span>
      <span class="stars">Güç ${teamOvr(team)} · ${team.players[10]?.name ?? ''}</span>
    `;
    btn.addEventListener('click', () => {
      selectedHomeId = team.id;
      if (selectedAwayId === selectedHomeId) {
        selectedAwayId = TEAMS.find((t) => t.id !== selectedHomeId)?.id;
      }
      renderTeamGrid();
      fillOpponents();
      btnStart.disabled = !selectedHomeId;
    });
    teamGrid.appendChild(btn);
  }
}

function fillOpponents() {
  opponentSelect.innerHTML = '';
  for (const team of TEAMS) {
    if (team.id === selectedHomeId) continue;
    const opt = document.createElement('option');
    opt.value = team.id;
    opt.textContent = `${team.name} (${teamOvr(team)})`;
    if (team.id === selectedAwayId) opt.selected = true;
    opponentSelect.appendChild(opt);
  }
  selectedAwayId = opponentSelect.value;
}

opponentSelect.addEventListener('change', () => {
  selectedAwayId = opponentSelect.value;
});

btnStart.addEventListener('click', () => startMatch());
document.getElementById('btn-resume').addEventListener('click', () => setPaused(false));
document.getElementById('btn-quit').addEventListener('click', () => backToMenu());
document.getElementById('btn-rematch').addEventListener('click', () => startMatch());
document.getElementById('btn-menu').addEventListener('click', () => backToMenu());

function contrastText(hex) {
  const c = new THREE.Color(hex);
  const lum = c.r * 0.299 + c.g * 0.587 + c.b * 0.114;
  return lum > 0.6 ? '#111' : '#fff';
}

function startMatch() {
  if (match) match.dispose();
  // Fresh pitch each match keeps scene clean
  scene = createPitchScene();

  const home = getTeam(selectedHomeId);
  const away = getTeam(selectedAwayId);

  document.getElementById('home-name').textContent = home.name;
  document.getElementById('away-name').textContent = away.name;
  document.getElementById('home-badge').textContent = home.short;
  document.getElementById('away-badge').textContent = away.short;
  document.getElementById('home-badge').style.background = home.colors.primary;
  document.getElementById('away-badge').style.background = away.colors.primary;
  document.getElementById('home-score').textContent = '0';
  document.getElementById('away-score').textContent = '0';
  document.getElementById('match-minute').textContent = "0'";

  match = new Match({
    scene,
    homeTeam: home,
    awayTeam: away,
    onGoal: (score, scorer) => {
      document.getElementById('home-score').textContent = String(score.home);
      document.getElementById('away-score').textContent = String(score.away);
      document.getElementById('goal-scorer').textContent = scorer;
      goalBanner.classList.remove('hidden');
      clearTimeout(goalBanner._t);
      goalBanner._t = setTimeout(() => goalBanner.classList.add('hidden'), 1600);
      vibrate(40);
    },
    onEnd: (score) => {
      resultScreen.classList.remove('hidden');
      touchControls.classList.add('hidden');
      const title =
        score.home > score.away ? 'Galibiyet!' : score.home < score.away ? 'Mağlubiyet' : 'Beraberlik';
      document.getElementById('result-title').textContent = title;
      document.getElementById('result-score').textContent = `${score.home} - ${score.away}`;
    },
    onPossession: (name) => {
      document.getElementById('possession-player').textContent = name;
    },
  });

  match.resetKickoff(1);
  menu.classList.add('hidden');
  resultScreen.classList.add('hidden');
  pauseOverlay.classList.add('hidden');
  hud.classList.remove('hidden');
  touchControls.classList.remove('hidden');
  lastT = performance.now();
  cancelAnimationFrame(raf);
  raf = requestAnimationFrame(loop);
}

function backToMenu() {
  if (match) {
    match.dispose();
    match = null;
  }
  pauseOverlay.classList.add('hidden');
  resultScreen.classList.add('hidden');
  goalBanner.classList.add('hidden');
  hud.classList.add('hidden');
  touchControls.classList.add('hidden');
  menu.classList.remove('hidden');
  scene = createPitchScene();
}

function setPaused(v) {
  if (!match || match.ended) return;
  match.paused = v;
  pauseOverlay.classList.toggle('hidden', !v);
}

function vibrate(ms) {
  try {
    navigator.vibrate?.(ms);
  } catch {
    /* ignore */
  }
}

// Pause on back / visibility
document.addEventListener('visibilitychange', () => {
  if (document.hidden && match && !match.ended) setPaused(true);
});
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && match && !match.ended) setPaused(!match.paused);
});

const camOffset = new THREE.Vector3(0, 16, -18);
const camLook = new THREE.Vector3();
const camPos = new THREE.Vector3();

function loop(t) {
  raf = requestAnimationFrame(loop);
  const dt = Math.min(0.05, (t - lastT) / 1000);
  lastT = t;

  // Idle camera on menu
  if (!match) {
    const a = t * 0.00015;
    camera.position.set(Math.sin(a) * 40, 18, Math.cos(a) * 40);
    camera.lookAt(0, 0, 0);
    renderer.render(scene, camera);
    return;
  }

  const input = controls.sample();
  match.update(dt, input);
  document.getElementById('match-minute').textContent = `${match.minute}'`;

  const target = match.getCameraTarget();
  camLook.lerp(target, 1 - Math.pow(0.001, dt));
  const facing = match.controlled
    ? new THREE.Vector3(Math.sin(match.controlled.mesh.rotation.y), 0, Math.cos(match.controlled.mesh.rotation.y))
    : new THREE.Vector3(0, 0, 1);
  const back = facing.clone().multiplyScalar(-14).add(new THREE.Vector3(0, 12, 0));
  camPos.copy(camLook).add(back);
  // Blend with classic broadcast angle
  camPos.lerp(camLook.clone().add(camOffset), 0.35);
  camera.position.lerp(camPos, 1 - Math.pow(0.0008, dt));
  camera.lookAt(camLook.x, 0.8, camLook.z);

  renderer.render(scene, camera);
}

function onResize() {
  const w = window.innerWidth;
  const h = window.innerHeight;
  camera.aspect = w / h;
  camera.updateProjectionMatrix();
  renderer.setSize(w, h);
}
window.addEventListener('resize', onResize);

renderTeamGrid();
fillOpponents();
btnStart.disabled = true;

// Menu ambient render
lastT = performance.now();
raf = requestAnimationFrame(loop);

// Capacitor status bar (optional)
async function initNative() {
  try {
    const { StatusBar, Style } = await import('@capacitor/status-bar');
    await StatusBar.setStyle({ style: Style.Dark });
    await StatusBar.setBackgroundColor({ color: '#0a1628' });
  } catch {
    /* web */
  }
}
initNative();
