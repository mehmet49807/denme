import * as THREE from 'three';

/**
 * Mobil futbol estetiği (DLS / FTS referans):
 * renk bloklu uzun kollu forma, yüz, sakal, neon krampon, şort numarası.
 */

const SKIN_TONES = [0xe8b989, 0xd4a06a, 0xc68642, 0xb07d4f, 0x8d5524];
const HAIR_TONES = [0x1a120c, 0x0f0a08, 0x2a1c14, 0x111111];

function hex(n) {
  return `#${n.toString(16).padStart(6, '0')}`;
}

function std(color, extra = {}) {
  return new THREE.MeshStandardMaterial({
    color,
    roughness: 0.62,
    metalness: 0.04,
    ...extra,
  });
}

/** Yüz — sakal, kaş, göz (referans mobil oyuncu). */
function makeFaceMap(skin, hair, beard) {
  const c = document.createElement('canvas');
  c.width = 512;
  c.height = 512;
  const ctx = c.getContext('2d');
  ctx.fillStyle = hex(skin);
  ctx.fillRect(0, 0, 512, 512);

  // Soft shading
  const g = ctx.createRadialGradient(256, 280, 40, 256, 260, 280);
  g.addColorStop(0, 'rgba(255,220,180,0.15)');
  g.addColorStop(1, 'rgba(60,30,10,0.22)');
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, 512, 512);

  // Hair fringe
  ctx.fillStyle = hex(hair);
  ctx.beginPath();
  ctx.ellipse(256, 90, 200, 100, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillRect(0, 0, 512, 110);

  // Brows
  ctx.strokeStyle = hex(hair);
  ctx.lineWidth = 10;
  ctx.lineCap = 'round';
  ctx.beginPath();
  ctx.moveTo(150, 200);
  ctx.quadraticCurveTo(190, 185, 230, 200);
  ctx.moveTo(282, 200);
  ctx.quadraticCurveTo(322, 185, 362, 200);
  ctx.stroke();

  // Eyes
  ctx.fillStyle = '#fff';
  ctx.beginPath();
  ctx.ellipse(190, 240, 28, 16, 0, 0, Math.PI * 2);
  ctx.ellipse(322, 240, 28, 16, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#3d2914';
  ctx.beginPath();
  ctx.arc(190, 242, 11, 0, Math.PI * 2);
  ctx.arc(322, 242, 11, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#0a0a0a';
  ctx.beginPath();
  ctx.arc(190, 242, 5, 0, Math.PI * 2);
  ctx.arc(322, 242, 5, 0, Math.PI * 2);
  ctx.fill();
  // highlight
  ctx.fillStyle = '#fff';
  ctx.beginPath();
  ctx.arc(186, 238, 2.5, 0, Math.PI * 2);
  ctx.arc(318, 238, 2.5, 0, Math.PI * 2);
  ctx.fill();

  // Nose
  ctx.strokeStyle = 'rgba(90,50,25,0.4)';
  ctx.lineWidth = 5;
  ctx.beginPath();
  ctx.moveTo(256, 245);
  ctx.lineTo(246, 300);
  ctx.lineTo(266, 300);
  ctx.stroke();

  // Mouth
  ctx.strokeStyle = '#9a4a4a';
  ctx.lineWidth = 5;
  ctx.beginPath();
  ctx.moveTo(220, 340);
  ctx.quadraticCurveTo(256, 358, 292, 340);
  ctx.stroke();

  if (beard) {
    ctx.fillStyle = hex(hair);
    ctx.globalAlpha = 0.72;
    ctx.beginPath();
    ctx.moveTo(170, 320);
    ctx.quadraticCurveTo(256, 300, 342, 320);
    ctx.quadraticCurveTo(360, 420, 256, 455);
    ctx.quadraticCurveTo(152, 420, 170, 320);
    ctx.fill();
    ctx.globalAlpha = 1;
  }

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  tex.anisotropy = 4;
  return tex;
}

/** Forma: üst renk + alt renk + göğüs logoları (FTG tarzı). */
function makeKitMap(topColor, botColor, accent, label = 'GA') {
  const c = document.createElement('canvas');
  c.width = 256;
  c.height = 256;
  const ctx = c.getContext('2d');
  ctx.fillStyle = hex(topColor);
  ctx.fillRect(0, 0, 256, 118);
  ctx.fillStyle = hex(botColor);
  ctx.fillRect(0, 118, 256, 138);

  // zip
  ctx.strokeStyle = 'rgba(0,0,0,0.25)';
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.moveTo(128, 10);
  ctx.lineTo(128, 246);
  ctx.stroke();

  // right chest text logo
  ctx.fillStyle = hex(accent);
  ctx.font = 'bold 22px Arial';
  ctx.textAlign = 'left';
  ctx.fillText(`${label}.`, 148, 70);

  // left circular crest
  ctx.beginPath();
  ctx.arc(78, 62, 22, 0, Math.PI * 2);
  ctx.fillStyle = hex(accent);
  ctx.fill();
  ctx.strokeStyle = '#fff';
  ctx.lineWidth = 3;
  ctx.stroke();
  ctx.fillStyle = '#111';
  ctx.font = 'bold 14px Arial';
  ctx.textAlign = 'center';
  ctx.fillText(label.slice(0, 2), 78, 67);

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function makeShortsMap(color, crestAccent, number) {
  const c = document.createElement('canvas');
  c.width = 128;
  c.height = 128;
  const ctx = c.getContext('2d');
  ctx.fillStyle = hex(color);
  ctx.fillRect(0, 0, 128, 128);

  // crest
  ctx.beginPath();
  ctx.arc(36, 48, 16, 0, Math.PI * 2);
  ctx.fillStyle = hex(crestAccent);
  ctx.fill();
  ctx.strokeStyle = '#fff';
  ctx.lineWidth = 2;
  ctx.stroke();

  // number
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 36px Arial';
  ctx.textAlign = 'center';
  ctx.fillText(String(number), 90, 60);

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function part(parent, geo, mat, x, y, z, sx = 1, sy = 1, sz = 1) {
  const m = new THREE.Mesh(geo, mat);
  m.position.set(x, y, z);
  m.scale.set(sx, sy, sz);
  m.castShadow = true;
  m.receiveShadow = true;
  parent.add(m);
  return m;
}

/**
 * @param colors { primary, secondary, accent }
 * @param isKeeper boolean
 * @param appearance { skin, hair, beard, number, shortLabel, kitStyle }
 * kitStyle: 'training' (white/primary) | 'match' (primary/secondary)
 */
export function createPlayerMesh(colors, isKeeper = false, appearance = {}) {
  const root = new THREE.Group();
  root.userData.isPlayer = true;

  const skin = appearance.skin ?? SKIN_TONES[Math.floor(Math.random() * SKIN_TONES.length)];
  const hair = appearance.hair ?? HAIR_TONES[Math.floor(Math.random() * HAIR_TONES.length)];
  const beard = appearance.beard ?? Math.random() > 0.4;
  const number = appearance.number ?? 9;
  const label = (appearance.shortLabel || 'GA').slice(0, 3);

  const primary = new THREE.Color(colors.primary).getHex();
  const secondary = new THREE.Color(colors.secondary).getHex();
  const accent = new THREE.Color(colors.accent || '#ffffff').getHex();

  // Referans: üst açık (beyaz/kırmızı), alt takım rengi
  const kitStyle = appearance.kitStyle || 'training';
  let topC;
  let botC;
  let sockC;
  let shortsC;
  if (kitStyle === 'match') {
    topC = primary;
    botC = secondary;
    sockC = primary;
    shortsC = isKeeper ? 0x222222 : new THREE.Color(secondary).offsetHSL(0, 0, -0.25).getHex();
  } else {
    // training jacket like reference 1: white + light team blue
    topC = 0xf4f6f8;
    botC = primary;
    sockC = 0xf4f6f8;
    shortsC = 0x1a1a1a;
  }

  const faceMap = makeFaceMap(skin, hair, beard);
  const kitMap = makeKitMap(topC, botC, accent === 0xffffff ? primary : accent, label);
  const shortsMap = makeShortsMap(shortsC, accent === 0xffffff ? primary : accent, number);

  const skinMat = std(skin, { roughness: 0.78 });
  const headMat = std(skin, { roughness: 0.7 });
  const hairMat = std(hair, { roughness: 0.95 });
  const kitMat = std(0xffffff, { map: kitMap, roughness: 0.48, metalness: 0.06 });
  const sleeveTop = std(topC, { roughness: 0.5 });
  const sleeveBot = std(botC, { roughness: 0.5 });
  const shortsMat = std(0xffffff, { map: shortsMap, roughness: 0.55 });
  const sockMat = std(sockC, { roughness: 0.7 });
  const bootMat = std(0xc6ff00, { roughness: 0.32, metalness: 0.28 });
  const bootDark = std(0x111111, { roughness: 0.5 });

  // —— HEAD ——
  const head = part(root, new THREE.SphereGeometry(0.125, 28, 22), headMat, 0, 1.72, 0.015, 0.96, 1.12, 0.94);

  const face = new THREE.Mesh(
    new THREE.PlaneGeometry(0.2, 0.24),
    new THREE.MeshStandardMaterial({
      map: faceMap,
      roughness: 0.68,
      metalness: 0.02,
      transparent: true,
    })
  );
  face.position.set(0, 1.715, 0.112);
  face.castShadow = true;
  root.add(face);

  // Hair volume
  part(root, new THREE.SphereGeometry(0.13, 20, 14, 0, Math.PI * 2, 0, Math.PI * 0.58), hairMat, 0, 1.74, -0.01, 1, 1, 1).rotation.x = -0.22;
  part(root, new THREE.SphereGeometry(0.1, 14, 10), hairMat, 0, 1.66, -0.07, 1.05, 0.7, 0.85);

  // Ears / neck
  part(root, new THREE.SphereGeometry(0.034, 10, 8), skinMat, -0.118, 1.7, 0);
  part(root, new THREE.SphereGeometry(0.034, 10, 8), skinMat, 0.118, 1.7, 0);
  part(root, new THREE.CylinderGeometry(0.052, 0.062, 0.1, 12), skinMat, 0, 1.56, 0);

  // —— TORSO (bulky mobile-game chest) ——
  const chest = part(root, new THREE.CapsuleGeometry(0.175, 0.32, 8, 16), kitMat, 0, 1.3, 0, 1.25, 1, 0.8);

  // Shoulders
  part(root, new THREE.SphereGeometry(0.085, 12, 10), sleeveTop, -0.24, 1.42, 0);
  part(root, new THREE.SphereGeometry(0.085, 12, 10), sleeveTop, 0.24, 1.42, 0);

  // Long sleeves: white/top then colored cuff
  const uArm = new THREE.CapsuleGeometry(0.052, 0.18, 5, 10);
  const fArm = new THREE.CapsuleGeometry(0.046, 0.18, 5, 10);

  const leftUpper = part(root, uArm, sleeveTop, -0.29, 1.28, 0);
  leftUpper.rotation.z = 0.28;
  const leftFore = part(root, fArm, sleeveBot, -0.34, 1.04, 0.02);
  leftFore.rotation.z = 0.1;

  const rightUpper = part(root, uArm, sleeveTop, 0.29, 1.28, 0);
  rightUpper.rotation.z = -0.28;
  const rightFore = part(root, fArm, sleeveBot, 0.34, 1.04, 0.02);
  rightFore.rotation.z = -0.1;

  part(root, new THREE.SphereGeometry(0.038, 10, 8), skinMat, -0.36, 0.88, 0.04);
  part(root, new THREE.SphereGeometry(0.038, 10, 8), skinMat, 0.36, 0.88, 0.04);

  // —— SHORTS ——
  part(root, new THREE.CylinderGeometry(0.16, 0.14, 0.28, 14), shortsMat, 0, 0.98, 0, 1.1, 1, 0.9);

  // Legs: thigh skin + long socks
  const thighGeo = new THREE.CapsuleGeometry(0.06, 0.18, 5, 10);
  const calfGeo = new THREE.CapsuleGeometry(0.05, 0.28, 5, 10);

  const leftThigh = part(root, thighGeo, skinMat, -0.08, 0.76, 0);
  const rightThigh = part(root, thighGeo, skinMat, 0.08, 0.76, 0);
  const leftCalf = part(root, calfGeo, sockMat, -0.08, 0.42, 0);
  const rightCalf = part(root, calfGeo, sockMat, 0.08, 0.42, 0);

  part(root, new THREE.SphereGeometry(0.042, 10, 8), skinMat, -0.08, 0.6, 0.01);
  part(root, new THREE.SphereGeometry(0.042, 10, 8), skinMat, 0.08, 0.6, 0.01);

  // —— NEON BOOTS (referans) ——
  const leftBoot = part(root, new THREE.BoxGeometry(0.095, 0.055, 0.22), bootMat, -0.08, 0.055, 0.045);
  const rightBoot = part(root, new THREE.BoxGeometry(0.095, 0.055, 0.22), bootMat, 0.08, 0.055, 0.045);
  part(root, new THREE.SphereGeometry(0.038, 10, 8), bootMat, -0.08, 0.05, 0.14);
  part(root, new THREE.SphereGeometry(0.038, 10, 8), bootMat, 0.08, 0.05, 0.14);
  // black sole
  part(root, new THREE.BoxGeometry(0.1, 0.02, 0.23), bootDark, -0.08, 0.02, 0.04);
  part(root, new THREE.BoxGeometry(0.1, 0.02, 0.23), bootDark, 0.08, 0.02, 0.04);

  root.userData.parts = {
    leftLeg: leftThigh,
    rightLeg: rightThigh,
    leftCalf,
    rightCalf,
    leftArm: leftUpper,
    rightArm: rightUpper,
    leftFore,
    rightFore,
    leftBoot,
    rightBoot,
    head,
    chest,
  };

  // Idle stance slight lean
  root.userData.parts.leftArm.rotation.x = 0.08;
  root.userData.parts.rightArm.rotation.x = -0.05;

  return root;
}

export function animatePlayerWalk(mesh, time, speed) {
  const p = mesh.userData.parts;
  if (!p) return;
  const amp = Math.min(0.55, speed * 0.34);
  const swing = Math.sin(time * 10 * Math.max(0.35, speed)) * amp;
  p.leftLeg.rotation.x = swing;
  p.rightLeg.rotation.x = -swing;
  if (p.leftCalf) p.leftCalf.rotation.x = Math.max(0, -swing * 0.45);
  if (p.rightCalf) p.rightCalf.rotation.x = Math.max(0, swing * 0.45);
  p.leftArm.rotation.x = -swing * 0.7;
  p.rightArm.rotation.x = swing * 0.7;
}

/** Vitrin için şut pozu (2. referans görsel). */
export function applyKickPose(mesh, amount = 1) {
  const p = mesh.userData.parts;
  if (!p) return;
  p.rightLeg.rotation.x = -0.9 * amount;
  p.leftLeg.rotation.x = 0.25 * amount;
  if (p.rightCalf) p.rightCalf.rotation.x = 0.5 * amount;
  p.rightArm.rotation.x = -0.4 * amount;
  p.leftArm.rotation.x = 0.35 * amount;
  if (p.chest) p.chest.rotation.x = 0.08 * amount;
}
