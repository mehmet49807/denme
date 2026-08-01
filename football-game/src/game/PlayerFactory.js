import * as THREE from 'three';

/**
 * Mobil futbol insan modeli (DLS / FTS tarzı).
 * Arkadan da insan okunur: saç, kulak, boyun, forma, neon krampon.
 */

const SKIN_TONES = [0xf1c27d, 0xe0ac69, 0xc68642, 0x8d5524, 0xd4a574];
const HAIR_TONES = [0x1a120c, 0x0d0907, 0x2b1d14, 0x111111];

function hex(n) {
  return `#${(n >>> 0).toString(16).padStart(6, '0')}`;
}

function mat(color, opts = {}) {
  return new THREE.MeshStandardMaterial({
    color,
    roughness: opts.roughness ?? 0.7,
    metalness: opts.metalness ?? 0.04,
    map: opts.map ?? null,
    ...opts,
  });
}

/** Kafa UV yüzeyi — önden yüz, arkadan saç. */
function makeHeadMap(skin, hair, beard) {
  const c = document.createElement('canvas');
  c.width = 512;
  c.height = 512;
  const ctx = c.getContext('2d');

  // Sphere UV: u wrap around, v top→bottom. Front ≈ u center.
  ctx.fillStyle = hex(skin);
  ctx.fillRect(0, 0, 512, 512);

  // Back of head hair (left/right edges of equirect)
  ctx.fillStyle = hex(hair);
  ctx.fillRect(0, 0, 90, 512);
  ctx.fillRect(422, 0, 90, 512);
  // Top hair
  ctx.fillRect(0, 0, 512, 140);
  ctx.beginPath();
  ctx.ellipse(256, 150, 200, 60, 0, 0, Math.PI * 2);
  ctx.fill();

  // Soft cheeks
  const g = ctx.createRadialGradient(256, 280, 20, 256, 280, 200);
  g.addColorStop(0, 'rgba(255,210,170,0.2)');
  g.addColorStop(1, 'rgba(80,40,20,0.15)');
  ctx.fillStyle = g;
  ctx.fillRect(100, 160, 312, 280);

  // Brows
  ctx.strokeStyle = hex(hair);
  ctx.lineWidth = 8;
  ctx.lineCap = 'round';
  ctx.beginPath();
  ctx.moveTo(175, 230);
  ctx.quadraticCurveTo(210, 218, 245, 230);
  ctx.moveTo(267, 230);
  ctx.quadraticCurveTo(302, 218, 337, 230);
  ctx.stroke();

  // Eyes
  ctx.fillStyle = '#fafafa';
  ctx.beginPath();
  ctx.ellipse(210, 265, 22, 13, 0, 0, Math.PI * 2);
  ctx.ellipse(302, 265, 22, 13, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#3a2510';
  ctx.beginPath();
  ctx.arc(210, 266, 9, 0, Math.PI * 2);
  ctx.arc(302, 266, 9, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#0a0a0a';
  ctx.beginPath();
  ctx.arc(210, 266, 4, 0, Math.PI * 2);
  ctx.arc(302, 266, 4, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#fff';
  ctx.beginPath();
  ctx.arc(207, 263, 2, 0, Math.PI * 2);
  ctx.arc(299, 263, 2, 0, Math.PI * 2);
  ctx.fill();

  // Nose / mouth
  ctx.strokeStyle = 'rgba(100,55,30,0.45)';
  ctx.lineWidth = 4;
  ctx.beginPath();
  ctx.moveTo(256, 270);
  ctx.lineTo(248, 320);
  ctx.lineTo(264, 320);
  ctx.stroke();
  ctx.strokeStyle = '#a05050';
  ctx.lineWidth = 4;
  ctx.beginPath();
  ctx.moveTo(228, 355);
  ctx.quadraticCurveTo(256, 372, 284, 355);
  ctx.stroke();

  if (beard) {
    ctx.globalAlpha = 0.78;
    ctx.fillStyle = hex(hair);
    ctx.beginPath();
    ctx.moveTo(190, 340);
    ctx.quadraticCurveTo(256, 320, 322, 340);
    ctx.quadraticCurveTo(340, 430, 256, 460);
    ctx.quadraticCurveTo(172, 430, 190, 340);
    ctx.fill();
    ctx.globalAlpha = 1;
  }

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  tex.anisotropy = 8;
  return tex;
}

function makeKitMap(topC, botC, accent, label) {
  const c = document.createElement('canvas');
  c.width = 512;
  c.height = 512;
  const ctx = c.getContext('2d');
  ctx.fillStyle = hex(topC);
  ctx.fillRect(0, 0, 512, 230);
  ctx.fillStyle = hex(botC);
  ctx.fillRect(0, 230, 512, 282);

  // zipper
  ctx.strokeStyle = 'rgba(0,0,0,0.2)';
  ctx.lineWidth = 4;
  ctx.beginPath();
  ctx.moveTo(256, 20);
  ctx.lineTo(256, 490);
  ctx.stroke();

  // chest logos
  ctx.fillStyle = hex(accent);
  ctx.beginPath();
  ctx.arc(160, 120, 36, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = '#fff';
  ctx.lineWidth = 4;
  ctx.stroke();
  ctx.fillStyle = '#111';
  ctx.font = 'bold 22px Arial';
  ctx.textAlign = 'center';
  ctx.fillText(String(label).slice(0, 2), 160, 128);

  ctx.fillStyle = hex(accent);
  ctx.font = 'bold 28px Arial';
  ctx.textAlign = 'left';
  ctx.fillText(`${String(label).slice(0, 3)}.`, 300, 130);

  // back number area hint
  ctx.fillStyle = 'rgba(255,255,255,0.15)';
  ctx.fillRect(200, 300, 112, 140);

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function makeShortsMap(color, crest, number) {
  const c = document.createElement('canvas');
  c.width = 256;
  c.height = 256;
  const ctx = c.getContext('2d');
  ctx.fillStyle = hex(color);
  ctx.fillRect(0, 0, 256, 256);
  ctx.beginPath();
  ctx.arc(70, 90, 28, 0, Math.PI * 2);
  ctx.fillStyle = hex(crest);
  ctx.fill();
  ctx.strokeStyle = '#fff';
  ctx.lineWidth = 3;
  ctx.stroke();
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 56px Arial';
  ctx.textAlign = 'center';
  ctx.fillText(String(number), 180, 110);
  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function add(parent, geo, material, x, y, z, rx = 0, ry = 0, rz = 0) {
  const m = new THREE.Mesh(geo, material);
  m.position.set(x, y, z);
  m.rotation.set(rx, ry, rz);
  m.castShadow = true;
  m.receiveShadow = true;
  parent.add(m);
  return m;
}

export function createPlayerMesh(colors, isKeeper = false, appearance = {}) {
  const root = new THREE.Group();
  root.userData.isPlayer = true;

  const skin = appearance.skin ?? SKIN_TONES[(appearance.number || 0) % SKIN_TONES.length];
  const hair = appearance.hair ?? HAIR_TONES[(appearance.number || 0) % HAIR_TONES.length];
  const beard = appearance.beard ?? true;
  const number = appearance.number ?? 9;
  const label = (appearance.shortLabel || 'GA').slice(0, 3);

  const primary = new THREE.Color(colors.primary).getHex();
  const secondary = new THREE.Color(colors.secondary).getHex();
  const accent = new THREE.Color(colors.accent || '#ffffff').getHex();

  const topC = 0xf5f7fa;
  const botC = primary;
  const shortsC = isKeeper ? 0x222222 : 0x151515;
  const sockC = 0xf5f7fa;

  const headMap = makeHeadMap(skin, hair, beard);
  const kitMap = makeKitMap(topC, botC, accent === 0xffffff ? primary : accent, label);
  const shortsMap = makeShortsMap(shortsC, accent === 0xffffff ? primary : accent, number);

  const skinMat = mat(skin, { roughness: 0.82 });
  const headMat = mat(0xffffff, { map: headMap, roughness: 0.72 });
  const hairMat = mat(hair, { roughness: 0.95 });
  const kitMat = mat(0xffffff, { map: kitMap, roughness: 0.48, metalness: 0.06 });
  const sleeveTop = mat(topC, { roughness: 0.5 });
  const sleeveBot = mat(botC, { roughness: 0.5 });
  const shortsMat = mat(0xffffff, { map: shortsMap, roughness: 0.58 });
  const sockMat = mat(sockC, { roughness: 0.72 });
  const bootMat = mat(0xc8ff00, { roughness: 0.3, metalness: 0.28 });
  const soleMat = mat(0x111111, { roughness: 0.6 });

  // —— HEAD (ten rengi + UV yüz; saç sadece üst/arka) ——
  const head = add(root, new THREE.SphereGeometry(0.122, 36, 28), headMat, 0, 1.7, 0.02);
  head.scale.set(0.96, 1.14, 0.96);

  // Yüz kartı — BasicMaterial ile her ışıkta okunur
  const facePlane = new THREE.Mesh(
    new THREE.PlaneGeometry(0.23, 0.27),
    new THREE.MeshBasicMaterial({
      map: headMap,
      transparent: false,
      depthTest: true,
    })
  );
  // Kafa ön yüzeyi ~0.14 — kart onun dışında olmalı
  facePlane.position.set(0, 1.7, 0.155);
  facePlane.renderOrder = 2;
  root.add(facePlane);

  // Saç: yalnızca tepe (phi kısa) — yüzü kapatmasın
  const hairCap = add(
    root,
    new THREE.SphereGeometry(0.13, 24, 12, 0, Math.PI * 2, 0, Math.PI * 0.42),
    hairMat,
    0,
    1.74,
    -0.015,
    -0.2,
    0,
    0
  );
  hairCap.scale.set(1.02, 1.0, 1.05);
  // Arka saç yastığı
  const hairBack = add(root, new THREE.SphereGeometry(0.095, 16, 12), hairMat, 0, 1.66, -0.095);
  hairBack.scale.set(1.2, 0.9, 0.85);

  // Ears
  add(root, new THREE.SphereGeometry(0.034, 12, 10), skinMat, -0.118, 1.68, 0.02).scale.set(0.65, 1.15, 0.9);
  add(root, new THREE.SphereGeometry(0.034, 12, 10), skinMat, 0.118, 1.68, 0.02).scale.set(0.65, 1.15, 0.9);

  // Neck
  add(root, new THREE.CylinderGeometry(0.052, 0.062, 0.11, 14), skinMat, 0, 1.53, 0.02);

  // —— TORSO ——
  const chest = add(root, new THREE.CapsuleGeometry(0.16, 0.34, 8, 18), kitMat, 0, 1.28, 0);
  chest.scale.set(1.28, 1, 0.82);

  // Omuzlar gövdeye gömülü (ayrı top eklem yok)
  add(root, new THREE.CapsuleGeometry(0.07, 0.06, 4, 10), sleeveTop, -0.22, 1.4, 0, 0, 0, 1.2);
  add(root, new THREE.CapsuleGeometry(0.07, 0.06, 4, 10), sleeveTop, 0.22, 1.4, 0, 0, 0, -1.2);

  // Tek parça kollar
  const leftArm = new THREE.Group();
  leftArm.position.set(-0.27, 1.38, 0);
  root.add(leftArm);
  add(leftArm, new THREE.CapsuleGeometry(0.045, 0.42, 6, 12), sleeveTop, 0, -0.22, 0).rotation.z = 0.12;
  // önkol rengi (alt yarı)
  add(leftArm, new THREE.CapsuleGeometry(0.042, 0.2, 6, 12), sleeveBot, -0.02, -0.42, 0.01);
  add(leftArm, new THREE.CapsuleGeometry(0.03, 0.06, 4, 8), skinMat, -0.02, -0.58, 0.02);

  const rightArm = new THREE.Group();
  rightArm.position.set(0.27, 1.38, 0);
  root.add(rightArm);
  add(rightArm, new THREE.CapsuleGeometry(0.045, 0.42, 6, 12), sleeveTop, 0, -0.22, 0).rotation.z = -0.12;
  add(rightArm, new THREE.CapsuleGeometry(0.042, 0.2, 6, 12), sleeveBot, 0.02, -0.42, 0.01);
  add(rightArm, new THREE.CapsuleGeometry(0.03, 0.06, 4, 8), skinMat, 0.02, -0.58, 0.02);

  // —— SHORTS + LEGS ——
  add(root, new THREE.CylinderGeometry(0.155, 0.135, 0.3, 16), shortsMat, 0, 0.96, 0).scale.set(1.12, 1, 0.92);

  const leftLeg = new THREE.Group();
  leftLeg.position.set(-0.085, 0.82, 0);
  root.add(leftLeg);
  add(leftLeg, new THREE.CapsuleGeometry(0.055, 0.22, 6, 12), skinMat, 0, -0.12, 0);
  add(leftLeg, new THREE.CapsuleGeometry(0.048, 0.32, 6, 12), sockMat, 0, -0.48, 0);
  const leftBoot = add(leftLeg, new THREE.BoxGeometry(0.1, 0.06, 0.22), bootMat, 0, -0.72, 0.05);
  add(leftLeg, new THREE.SphereGeometry(0.038, 10, 8), bootMat, 0, -0.72, 0.14);
  add(leftLeg, new THREE.BoxGeometry(0.105, 0.02, 0.23), soleMat, 0, -0.755, 0.04);

  const rightLeg = new THREE.Group();
  rightLeg.position.set(0.085, 0.82, 0);
  root.add(rightLeg);
  add(rightLeg, new THREE.CapsuleGeometry(0.055, 0.22, 6, 12), skinMat, 0, -0.12, 0);
  add(rightLeg, new THREE.CapsuleGeometry(0.048, 0.32, 6, 12), sockMat, 0, -0.48, 0);
  const rightBoot = add(rightLeg, new THREE.BoxGeometry(0.1, 0.06, 0.22), bootMat, 0, -0.72, 0.05);
  add(rightLeg, new THREE.SphereGeometry(0.038, 10, 8), bootMat, 0, -0.72, 0.14);
  add(rightLeg, new THREE.BoxGeometry(0.105, 0.02, 0.23), soleMat, 0, -0.755, 0.04);

  root.userData.parts = {
    leftLeg,
    rightLeg,
    leftArm,
    rightArm,
    leftBoot,
    rightBoot,
    leftFore,
    head,
    chest,
  };

  return root;
}

export function animatePlayerWalk(mesh, time, speed) {
  const p = mesh.userData.parts;
  if (!p) return;
  const amp = Math.min(0.55, speed * 0.36);
  const swing = Math.sin(time * 10 * Math.max(0.35, speed)) * amp;
  p.leftLeg.rotation.x = swing;
  p.rightLeg.rotation.x = -swing;
  p.leftArm.rotation.x = -swing * 0.65;
  p.rightArm.rotation.x = swing * 0.65;
}

export function applyKickPose(mesh, amount = 1) {
  const p = mesh.userData.parts;
  if (!p) return;
  p.rightLeg.rotation.x = -1.0 * amount;
  p.leftLeg.rotation.x = 0.2 * amount;
  p.rightArm.rotation.x = -0.35 * amount;
  p.leftArm.rotation.x = 0.4 * amount;
}
