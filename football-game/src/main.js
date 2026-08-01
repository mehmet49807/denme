import * as THREE from 'three';
import { createPitchScene } from './game/Pitch.js';
import { Match } from './game/Match.js';
import { TouchControls } from './game/Controls.js';
import { createPlayerMesh, animatePlayerWalk, applyKickPose } from './game/PlayerFactory.js';
import { createBall } from './game/Ball.js';
import { signIn } from './auth/socialAuth.js';
import {
  loadSave,
  saveState,
  clearAll,
  validateTeamName,
  validateShortName,
  validateManagerName,
} from './data/storage.js';
import { createUserTeam, buildAiOpponents, teamOvr } from './data/teamFactory.js';
import { createLogoEditor } from './ui/logoEditor.js';

const canvas = document.getElementById('game-canvas');
const hud = document.getElementById('hud');
const touchControlsEl = document.getElementById('touch-controls');
const pauseOverlay = document.getElementById('pause-overlay');
const goalBanner = document.getElementById('goal-banner');
const resultScreen = document.getElementById('result-screen');

const screenLogin = document.getElementById('screen-login');
const screenTeam = document.getElementById('screen-team');
const screenLobby = document.getElementById('screen-lobby');

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

const camera = new THREE.PerspectiveCamera(42, window.innerWidth / window.innerHeight, 0.1, 250);
camera.position.set(0, 14, -22);

async function lockLandscape() {
  try {
    const orient = screen.orientation || screen.mozOrientation || screen.msOrientation;
    if (orient?.lock) await orient.lock('landscape');
  } catch {
    /* tarayıcı / izin */
  }
}
lockLandscape();
document.addEventListener('click', () => lockLandscape(), { once: true });

let scene = createPitchScene();
let previewPlayer = null;
let match = null;

let previewBall = null;
let previewKick = false;

function spawnMenuPreview() {
  if (previewPlayer) {
    scene.remove(previewPlayer);
    previewPlayer = null;
  }
  if (previewBall) {
    scene.remove(previewBall.mesh);
    previewBall = null;
  }

  // Referans 1: beyaz/mavi antrenman — veya dönüşümlü kırmızı/sarı
  const kits = [
    { primary: '#6ec1e4', secondary: '#ffffff', accent: '#1a3a6e', label: 'GA' },
    { primary: '#e63946', secondary: '#ffd60a', accent: '#ffd60a', label: 'FTG' },
  ];
  const kit = kits[Math.floor(Date.now() / 8000) % 2];

  previewPlayer = createPlayerMesh(
    { primary: kit.primary, secondary: kit.secondary, accent: kit.accent },
    false,
    {
      skin: 0xd4a06a,
      hair: 0x1a120c,
      beard: true,
      number: 19,
      shortLabel: kit.label,
      kitStyle: kit.label === 'FTG' ? 'match' : 'training',
    }
  );
  previewPlayer.position.set(1.2, 0, 0.15);
  previewPlayer.rotation.y = -0.35;
  scene.add(previewPlayer);

  previewBall = createBall();
  previewBall.mesh.position.set(1.45, 0.22, 0.85);
  scene.add(previewBall.mesh);

  const coneMat = new THREE.MeshStandardMaterial({ color: 0xff6a00, roughness: 0.45 });
  for (const [x, z] of [
    [0.55, 0.85],
    [1.85, 1.05],
    [2.15, 0.35],
    [0.9, 1.35],
  ]) {
    const cone = new THREE.Mesh(new THREE.SphereGeometry(0.12, 10, 8), coneMat);
    cone.scale.set(1, 0.55, 1);
    cone.position.set(x, 0.06, z);
    cone.castShadow = true;
    scene.add(cone);
  }
  previewKick = kit.label === 'FTG';
  if (previewKick) applyKickPose(previewPlayer, 1);
}
spawnMenuPreview();
let userTeam = null;
let aiOpponents = [];
let selectedAwayId = null;
let raf = 0;
let lastT = 0;
let logoDataUrl = '';

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

// —— Logo editor ——
const logoCanvas = document.getElementById('logo-canvas');
const logoEditor = createLogoEditor({
  canvas: logoCanvas,
  onChange: (url) => {
    logoDataUrl = url;
  },
});

const shapeRow = document.getElementById('shape-row');
const shapeLabels = { circle: 'Daire', shield: 'Kalkan', hex: 'Altıgen', diamond: 'Baklava' };
for (const shape of logoEditor.shapes) {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'shape-btn' + (shape === 'shield' ? ' active' : '');
  btn.textContent = shapeLabels[shape] || shape;
  btn.addEventListener('click', () => {
    logoEditor.set({ shape });
    shapeRow.querySelectorAll('.shape-btn').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
  });
  shapeRow.appendChild(btn);
}

document.getElementById('logo-primary').addEventListener('input', (e) => {
  logoEditor.set({ primary: e.target.value });
});
document.getElementById('logo-secondary').addEventListener('input', (e) => {
  logoEditor.set({ secondary: e.target.value });
});
document.getElementById('logo-accent').addEventListener('input', (e) => {
  logoEditor.set({ accent: e.target.value });
});
document.getElementById('btn-logo-random').addEventListener('click', () => {
  logoEditor.randomize();
  document.getElementById('logo-primary').value = logoEditor.state.primary;
  document.getElementById('logo-secondary').value = logoEditor.state.secondary;
  document.getElementById('logo-accent').value = logoEditor.state.accent;
  syncShapeButtons();
});

document.getElementById('input-team-short').addEventListener('input', (e) => {
  const v = e.target.value.toUpperCase().replace(/[^A-ZÇĞİÖŞÜ0-9]/gi, '').slice(0, 4);
  e.target.value = v;
  logoEditor.set({ short: v || 'GA' });
});

function syncShapeButtons() {
  const cur = logoEditor.state.shape;
  shapeRow.querySelectorAll('.shape-btn').forEach((b, i) => {
    b.classList.toggle('active', logoEditor.shapes[i] === cur);
  });
}

// —— Screens ——
function showScreen(name) {
  screenLogin.classList.toggle('hidden', name !== 'login');
  screenTeam.classList.toggle('hidden', name !== 'team');
  screenLobby.classList.toggle('hidden', name !== 'lobby');
}

function showLoginError(msg) {
  const el = document.getElementById('login-error');
  if (!msg) {
    el.classList.add('hidden');
    el.textContent = '';
    return;
  }
  el.textContent = msg;
  el.classList.remove('hidden');
}

function showTeamError(msg) {
  const el = document.getElementById('team-error');
  if (!msg) {
    el.classList.add('hidden');
    el.textContent = '';
    return;
  }
  el.textContent = msg;
  el.classList.remove('hidden');
}

async function handleAuth(provider) {
  showLoginError('');
  const managerName = document.getElementById('input-manager').value;
  const err = validateManagerName(managerName);
  if (err) {
    showLoginError(err);
    return;
  }
  try {
    const auth = await signIn(provider, managerName);
    saveState({ auth });
    document.getElementById('coach-label').textContent = `TD · ${auth.managerName}`;
    const save = loadSave();
    if (save.team?.players?.length) {
      userTeam = save.team;
      enterLobby();
    } else {
      showScreen('team');
    }
  } catch (e) {
    showLoginError(e.message || 'Giriş başarısız');
  }
}

document.getElementById('btn-google').addEventListener('click', () => handleAuth('google'));
document.getElementById('btn-facebook').addEventListener('click', () => handleAuth('facebook'));
document.getElementById('btn-guest').addEventListener('click', () => handleAuth('guest'));

document.getElementById('btn-create-team').addEventListener('click', () => {
  showTeamError('');
  const name = document.getElementById('input-team-name').value;
  const short = document.getElementById('input-team-short').value;
  const nameErr = validateTeamName(name);
  const shortErr = validateShortName(short);
  if (nameErr || shortErr) {
    showTeamError(nameErr || shortErr);
    return;
  }
  if (!logoDataUrl) {
    showTeamError('Logo oluşturman gerekiyor.');
    return;
  }

  const auth = loadSave().auth;
  const team = createUserTeam({
    name,
    short,
    colors: {
      primary: logoEditor.state.primary,
      secondary: logoEditor.state.secondary,
      accent: logoEditor.state.accent,
    },
    logoDataUrl,
    managerName: auth?.managerName || 'Teknik Direktör',
  });
  userTeam = team;
  saveState({ team });
  enterLobby();
});

function logout() {
  clearAll();
  userTeam = null;
  aiOpponents = [];
  match?.dispose();
  match = null;
  document.getElementById('input-manager').value = '';
  showScreen('login');
  hud.classList.add('hidden');
  touchControlsEl.classList.add('hidden');
  pauseOverlay.classList.add('hidden');
  resultScreen.classList.add('hidden');
}

document.getElementById('btn-logout').addEventListener('click', logout);
document.getElementById('btn-lobby-logout').addEventListener('click', logout);
document.getElementById('btn-edit-team').addEventListener('click', () => {
  const t = userTeam;
  if (t) {
    document.getElementById('input-team-name').value = t.name;
    document.getElementById('input-team-short').value = t.short;
    logoEditor.set({
      short: t.short,
      primary: t.colors.primary,
      secondary: t.colors.secondary,
      accent: t.colors.accent || '#ffffff',
    });
    document.getElementById('logo-primary').value = t.colors.primary;
    document.getElementById('logo-secondary').value = t.colors.secondary;
    document.getElementById('logo-accent').value = t.colors.accent || '#ffffff';
  }
  // Yeni kadro için kaydı temizle (yeniden oluşturunca değişir)
  saveState({ team: null });
  userTeam = null;
  showScreen('team');
});

function enterLobby() {
  if (!userTeam) {
    showScreen('team');
    return;
  }
  document.getElementById('lobby-logo').src = userTeam.logoDataUrl;
  document.getElementById('lobby-manager').textContent = `TD · ${userTeam.managerName}`;
  document.getElementById('lobby-team-name').textContent = userTeam.name;
  document.getElementById('lobby-meta').textContent = `${userTeam.short} · Güç ${userTeam.ovr ?? teamOvr(userTeam.players)}`;

  const list = document.getElementById('squad-list');
  list.innerHTML = '';
  for (const p of userTeam.players) {
    const li = document.createElement('li');
    li.innerHTML = `<span><span class="pos">${p.pos}</span> ${p.name}</span><span class="num">#${p.number}</span>`;
    list.appendChild(li);
  }

  aiOpponents = buildAiOpponents(userTeam.short);
  const sel = document.getElementById('opponent-select');
  sel.innerHTML = '';
  for (const t of aiOpponents) {
    const opt = document.createElement('option');
    opt.value = t.id;
    opt.textContent = `${t.name} (${t.short}) · Güç ${t.ovr}`;
    sel.appendChild(opt);
  }
  selectedAwayId = sel.value;
  sel.onchange = () => {
    selectedAwayId = sel.value;
  };

  showScreen('lobby');
}

document.getElementById('btn-start').addEventListener('click', () => startMatch());
document.getElementById('btn-resume').addEventListener('click', () => setPaused(false));
document.getElementById('btn-quit').addEventListener('click', () => backToLobby());
document.getElementById('btn-rematch').addEventListener('click', () => startMatch());
document.getElementById('btn-menu').addEventListener('click', () => backToLobby());

function setLogoEl(img, url) {
  if (url) {
    img.src = url;
    img.style.display = '';
  } else {
    img.removeAttribute('src');
    img.style.display = 'none';
  }
}

function startMatch() {
  if (!userTeam) return;
  const away = aiOpponents.find((t) => t.id === selectedAwayId) || aiOpponents[0];
  if (!away) return;

  if (match) match.dispose();
  previewPlayer = null;
  scene = createPitchScene();

  const home = userTeam;

  document.getElementById('home-name').textContent = home.name;
  document.getElementById('away-name').textContent = away.name;
  document.getElementById('home-badge').textContent = home.short;
  document.getElementById('away-badge').textContent = away.short;
  document.getElementById('home-badge').style.background = home.colors.primary;
  document.getElementById('away-badge').style.background = away.colors.primary;
  setLogoEl(document.getElementById('home-logo'), home.logoDataUrl);
  setLogoEl(document.getElementById('away-logo'), away.logoDataUrl);
  document.getElementById('home-score').textContent = '0';
  document.getElementById('away-score').textContent = '0';
  document.getElementById('match-minute').textContent = "0'";
  document.getElementById('manager-chip').textContent = home.managerName;

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
      touchControlsEl.classList.add('hidden');
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
  showScreen(null);
  screenLogin.classList.add('hidden');
  screenTeam.classList.add('hidden');
  screenLobby.classList.add('hidden');
  resultScreen.classList.add('hidden');
  pauseOverlay.classList.add('hidden');
  hud.classList.remove('hidden');
  touchControlsEl.classList.remove('hidden');
  lastT = performance.now();
  cancelAnimationFrame(raf);
  raf = requestAnimationFrame(loop);
}

function backToLobby() {
  if (match) {
    match.dispose();
    match = null;
  }
  pauseOverlay.classList.add('hidden');
  resultScreen.classList.add('hidden');
  goalBanner.classList.add('hidden');
  hud.classList.add('hidden');
  touchControlsEl.classList.add('hidden');
  scene = createPitchScene();
  spawnMenuPreview();
  enterLobby();
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

document.addEventListener('visibilitychange', () => {
  if (document.hidden && match && !match.ended) setPaused(true);
});
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && match && !match.ended) setPaused(!match.paused);
});

const camOffset = new THREE.Vector3(4.5, 5.5, -9);
const camLook = new THREE.Vector3();
const camPos = new THREE.Vector3();

function loop(t) {
  raf = requestAnimationFrame(loop);
  const dt = Math.min(0.05, (t - lastT) / 1000);
  lastT = t;

  if (!match) {
    if (!previewPlayer) spawnMenuPreview();
    const a = t * 0.0002;
    if (previewPlayer) {
      previewPlayer.rotation.y = -0.35 + Math.sin(a) * 0.12;
      if (!previewKick) animatePlayerWalk(previewPlayer, t / 1000, 0.2);
      else applyKickPose(previewPlayer, 0.85 + Math.sin(t * 0.004) * 0.08);
    }
    // Referans gibi yakın portre / 3-4 vücut
    camera.position.set(0.15 + Math.sin(a) * 0.1, 1.35, 2.55);
    camera.lookAt(1.15, 1.15, 0.2);
    renderer.render(scene, camera);
    return;
  }

  const input = controls.sample();
  match.update(dt, input);
  document.getElementById('match-minute').textContent = `${match.minute}'`;

  const target = match.getCameraTarget();
  camLook.lerp(target, 1 - Math.pow(0.001, dt));
  const facing = match.controlled
    ? new THREE.Vector3(
        Math.sin(match.controlled.mesh.rotation.y),
        0,
        Math.cos(match.controlled.mesh.rotation.y)
      )
    : new THREE.Vector3(0, 0, 1);
  // Yan-arkadan 3/4 açı — insan modeli daha iyi okunur
  const side = new THREE.Vector3(-facing.z, 0, facing.x).multiplyScalar(3.2);
  const back = facing.clone().multiplyScalar(-7.2).add(new THREE.Vector3(0, 2.8, 0)).add(side);
  camPos.copy(camLook).add(back);
  camPos.lerp(camLook.clone().add(camOffset), 0.2);
  camera.position.lerp(camPos, 1 - Math.pow(0.0008, dt));
  camera.lookAt(camLook.x, 1.2, camLook.z);

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

// Boot: restore session
(function boot() {
  const save = loadSave();
  if (save.auth?.managerName) {
    document.getElementById('input-manager').value = save.auth.managerName;
    document.getElementById('coach-label').textContent = `TD · ${save.auth.managerName}`;
    if (save.team?.players?.length) {
      userTeam = save.team;
      enterLobby();
    } else {
      showScreen('team');
    }
  } else {
    showScreen('login');
  }
})();

lastT = performance.now();
raf = requestAnimationFrame(loop);

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
