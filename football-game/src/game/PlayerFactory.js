import * as THREE from 'three';

const SKIN_TONES = [0xe0ac69, 0xc68642, 0x8d5524, 0xd4a574, 0xb07d4f];
const HAIR_COLORS = [0x1a120c, 0x2c1810, 0x0d0d0d, 0x3b2f2f, 0x1c1c1c];

function makeFaceTexture(skinHex, hairHex, hasBeard) {
  const c = document.createElement('canvas');
  c.width = 256;
  c.height = 256;
  const ctx = c.getContext('2d');

  // Skin base
  const skin = `#${skinHex.toString(16).padStart(6, '0')}`;
  const hair = `#${hairHex.toString(16).padStart(6, '0')}`;
  ctx.fillStyle = skin;
  ctx.fillRect(0, 0, 256, 256);

  // Subtle cheek shading
  const grad = ctx.createRadialGradient(128, 140, 20, 128, 140, 120);
  grad.addColorStop(0, 'rgba(0,0,0,0)');
  grad.addColorStop(1, 'rgba(80,40,20,0.18)');
  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, 256, 256);

  // Hair (top / forehead wrap for equirect-ish sphere UV)
  ctx.fillStyle = hair;
  ctx.fillRect(0, 0, 256, 70);
  ctx.beginPath();
  ctx.ellipse(128, 78, 90, 28, 0, 0, Math.PI * 2);
  ctx.fill();

  // Eyes white
  ctx.fillStyle = '#f5f5f5';
  ctx.beginPath();
  ctx.ellipse(95, 125, 14, 9, 0, 0, Math.PI * 2);
  ctx.ellipse(161, 125, 14, 9, 0, 0, Math.PI * 2);
  ctx.fill();

  // Iris
  ctx.fillStyle = '#2a1f14';
  ctx.beginPath();
  ctx.arc(95, 125, 6, 0, Math.PI * 2);
  ctx.arc(161, 125, 6, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillStyle = '#111';
  ctx.beginPath();
  ctx.arc(95, 125, 3, 0, Math.PI * 2);
  ctx.arc(161, 125, 3, 0, Math.PI * 2);
  ctx.fill();

  // Brows
  ctx.strokeStyle = hair;
  ctx.lineWidth = 4;
  ctx.beginPath();
  ctx.moveTo(80, 108);
  ctx.quadraticCurveTo(95, 102, 110, 108);
  ctx.moveTo(146, 108);
  ctx.quadraticCurveTo(161, 102, 176, 108);
  ctx.stroke();

  // Nose
  ctx.strokeStyle = 'rgba(90,50,30,0.45)';
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.moveTo(128, 122);
  ctx.lineTo(122, 148);
  ctx.lineTo(134, 148);
  ctx.stroke();

  // Mouth
  ctx.strokeStyle = '#8a3a3a';
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.moveTo(112, 168);
  ctx.quadraticCurveTo(128, 178, 144, 168);
  ctx.stroke();

  // Ears hint on sides
  ctx.fillStyle = skin;
  ctx.beginPath();
  ctx.ellipse(28, 135, 12, 18, 0, 0, Math.PI * 2);
  ctx.ellipse(228, 135, 12, 18, 0, 0, Math.PI * 2);
  ctx.fill();

  if (hasBeard) {
    ctx.fillStyle = hair;
    ctx.globalAlpha = 0.75;
    ctx.beginPath();
    ctx.ellipse(128, 195, 42, 28, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1;
  }

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function makeKitTexture(topHex, botHex, accentHex) {
  const c = document.createElement('canvas');
  c.width = 128;
  c.height = 128;
  const ctx = c.getContext('2d');
  const top = `#${topHex.toString(16).padStart(6, '0')}`;
  const bot = `#${botHex.toString(16).padStart(6, '0')}`;
  const accent = `#${accentHex.toString(16).padStart(6, '0')}`;

  // Training jacket: white upper / colored lower (reference style)
  ctx.fillStyle = top;
  ctx.fillRect(0, 0, 128, 58);
  ctx.fillStyle = bot;
  ctx.fillRect(0, 58, 128, 70);

  // Zip line
  ctx.strokeStyle = accent;
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(64, 8);
  ctx.lineTo(64, 120);
  ctx.stroke();

  // Chest logos
  ctx.fillStyle = accent;
  ctx.beginPath();
  ctx.arc(40, 36, 6, 0, Math.PI * 2);
  ctx.fill();
  ctx.fillRect(78, 30, 14, 10);

  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

function mat(color, opts = {}) {
  return new THREE.MeshStandardMaterial({
    color,
    roughness: opts.roughness ?? 0.65,
    metalness: opts.metalness ?? 0.05,
    map: opts.map || null,
    ...opts,
  });
}

function add(root, geo, material, x, y, z, cast = true) {
  const m = new THREE.Mesh(geo, material);
  m.position.set(x, y, z);
  m.castShadow = cast;
  m.receiveShadow = true;
  root.add(m);
  return m;
}

/**
 * İnsan oranlarına yakın 3D futbolcu (yüz dokusu + antrenman forması).
 */
export function createPlayerMesh(colors, isKeeper = false, appearance = {}) {
  const root = new THREE.Group();
  root.userData.isPlayer = true;

  const skinTone = appearance.skin ?? SKIN_TONES[Math.floor(Math.random() * SKIN_TONES.length)];
  const hairColor = appearance.hair ?? HAIR_COLORS[Math.floor(Math.random() * HAIR_COLORS.length)];
  const hasBeard = appearance.beard ?? Math.random() > 0.45;

  const faceMap = makeFaceTexture(skinTone, hairColor, hasBeard);
  const bodySkin = mat(skinTone, { roughness: 0.78 });
  const headMat = mat(skinTone, { roughness: 0.72 });

  const primary = new THREE.Color(colors.primary);
  const secondary = new THREE.Color(colors.secondary);
  const accent = new THREE.Color(colors.accent || '#ffffff');

  // Training look: white upper + team lower (like reference)
  const topHex = 0xf2f4f7;
  const botHex = primary.getHex();
  const kitMap = makeKitTexture(topHex, botHex, accent.getHex());
  const kitMat = mat(0xffffff, { map: kitMap, roughness: 0.5, metalness: 0.08 });

  const sleeveMat = mat(0xf2f4f7, { roughness: 0.5 });
  const lowerSleeve = mat(botHex, { roughness: 0.5 });
  const shortsMat = mat(isKeeper ? 0x1a1a1a : 0x1c1c1c, { roughness: 0.6 });
  const sockMat = mat(0xf5f5f5, { roughness: 0.7 });
  const bootMat = mat(0xc8ff00, { roughness: 0.35, metalness: 0.25 });
  const hairMat = mat(hairColor, { roughness: 0.92 });

  // —— Head (egg-shaped) + front face card ——
  const head = add(root, new THREE.SphereGeometry(0.12, 32, 24), headMat, 0, 1.7, 0.02);
  head.scale.set(0.95, 1.1, 0.92);

  const face = new THREE.Mesh(
    new THREE.PlaneGeometry(0.19, 0.22),
    new THREE.MeshStandardMaterial({
      map: faceMap,
      roughness: 0.65,
      metalness: 0.02,
      transparent: true,
      depthWrite: true,
    })
  );
  face.position.set(0, 1.7, 0.108);
  face.castShadow = true;
  root.add(face);

  // Hair cap + sides
  const hair = add(
    root,
    new THREE.SphereGeometry(0.125, 22, 16, 0, Math.PI * 2, 0, Math.PI * 0.55),
    hairMat,
    0,
    1.72,
    0.0
  );
  hair.rotation.x = -0.18;
  add(root, new THREE.SphereGeometry(0.1, 12, 10), hairMat, 0, 1.64, -0.06);

  // Ears
  add(root, new THREE.SphereGeometry(0.032, 10, 8), bodySkin, -0.115, 1.69, 0);
  add(root, new THREE.SphereGeometry(0.032, 10, 8), bodySkin, 0.115, 1.69, 0);

  // Neck
  add(root, new THREE.CylinderGeometry(0.05, 0.06, 0.09, 12), bodySkin, 0, 1.55, 0);

  // —— Torso (training jacket) ——
  const chest = add(root, new THREE.CapsuleGeometry(0.17, 0.3, 8, 16), kitMat, 0, 1.32, 0);
  chest.scale.set(1.2, 1, 0.78);

  // Shoulders
  add(root, new THREE.SphereGeometry(0.08, 12, 10), sleeveMat, -0.22, 1.44, 0);
  add(root, new THREE.SphereGeometry(0.08, 12, 10), sleeveMat, 0.22, 1.44, 0);

  // Arms (upper white, lower team color — long sleeve training)
  const upperArmGeo = new THREE.CapsuleGeometry(0.05, 0.17, 5, 12);
  const foreArmGeo = new THREE.CapsuleGeometry(0.045, 0.17, 5, 12);

  const leftUpper = add(root, upperArmGeo, sleeveMat, -0.26, 1.3, 0);
  leftUpper.rotation.z = 0.22;
  const leftFore = add(root, foreArmGeo, lowerSleeve, -0.3, 1.08, 0.02);
  leftFore.rotation.z = 0.12;

  const rightUpper = add(root, upperArmGeo, sleeveMat, 0.26, 1.3, 0);
  rightUpper.rotation.z = -0.22;
  const rightFore = add(root, foreArmGeo, lowerSleeve, 0.3, 1.08, 0.02);
  rightFore.rotation.z = -0.12;

  // Hands
  add(root, new THREE.SphereGeometry(0.035, 10, 8), bodySkin, -0.32, 0.92, 0.04);
  add(root, new THREE.SphereGeometry(0.035, 10, 8), bodySkin, 0.32, 0.92, 0.04);

  // —— Shorts ——
  const shorts = add(root, new THREE.CylinderGeometry(0.15, 0.13, 0.26, 14), shortsMat, 0, 1.0, 0);
  shorts.scale.set(1.05, 1, 0.85);

  // Thighs (skin) + socks
  const thighGeo = new THREE.CapsuleGeometry(0.055, 0.2, 4, 10);
  const calfGeo = new THREE.CapsuleGeometry(0.045, 0.22, 4, 10);

  const leftThigh = add(root, thighGeo, bodySkin, -0.075, 0.78, 0);
  const rightThigh = add(root, thighGeo, bodySkin, 0.075, 0.78, 0);

  const leftCalf = add(root, calfGeo, sockMat, -0.075, 0.45, 0);
  const rightCalf = add(root, calfGeo, sockMat, 0.075, 0.45, 0);

  // Knees
  add(root, new THREE.SphereGeometry(0.04, 10, 8), bodySkin, -0.075, 0.62, 0.01);
  add(root, new THREE.SphereGeometry(0.04, 10, 8), bodySkin, 0.075, 0.62, 0.01);

  // Boots (neon) — elongated
  const bootGeo = new THREE.BoxGeometry(0.09, 0.06, 0.2);
  const leftBoot = add(root, bootGeo, bootMat, -0.075, 0.06, 0.04);
  const rightBoot = add(root, bootGeo, bootMat, 0.075, 0.06, 0.04);
  // toe caps
  add(root, new THREE.SphereGeometry(0.035, 10, 8), bootMat, -0.075, 0.055, 0.12, true);
  add(root, new THREE.SphereGeometry(0.035, 10, 8), bootMat, 0.075, 0.055, 0.12, true);

  // Shorts side logo dots
  const logoDot = mat(0xffffff, { roughness: 0.4 });
  add(root, new THREE.CircleGeometry(0.03, 12), logoDot, -0.16, 1.0, 0.12, false);
  add(root, new THREE.CircleGeometry(0.03, 12), logoDot, 0.16, 1.0, 0.12, false);

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
  };

  return root;
}

export function animatePlayerWalk(mesh, time, speed) {
  const parts = mesh.userData.parts;
  if (!parts) return;
  const amp = Math.min(0.5, speed * 0.32);
  const swing = Math.sin(time * 10 * Math.max(0.4, speed)) * amp;
  parts.leftLeg.rotation.x = swing;
  parts.rightLeg.rotation.x = -swing;
  if (parts.leftCalf) parts.leftCalf.rotation.x = Math.max(0, -swing * 0.4);
  if (parts.rightCalf) parts.rightCalf.rotation.x = Math.max(0, swing * 0.4);
  parts.leftArm.rotation.x = -swing * 0.65;
  parts.rightArm.rotation.x = swing * 0.65;
  if (parts.leftFore) parts.leftFore.rotation.x = -swing * 0.35;
  if (parts.rightFore) parts.rightFore.rotation.x = swing * 0.35;
}
