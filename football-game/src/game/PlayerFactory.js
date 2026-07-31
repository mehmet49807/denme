import * as THREE from 'three';

/**
 * FTS tarzı stilize 3D oyuncu — baş, gövde, kollar, bacaklar.
 */
export function createPlayerMesh(colors, isKeeper = false) {
  const root = new THREE.Group();
  root.userData.isPlayer = true;

  const skin = new THREE.MeshStandardMaterial({
    color: 0xc68642,
    roughness: 0.75,
    metalness: 0.05,
  });
  const hairMat = new THREE.MeshStandardMaterial({
    color: 0x1a120c,
    roughness: 0.9,
  });
  const jerseyTop = new THREE.MeshStandardMaterial({
    color: new THREE.Color(colors.primary),
    roughness: 0.55,
    metalness: 0.08,
  });
  const jerseyBottom = new THREE.MeshStandardMaterial({
    color: new THREE.Color(colors.secondary),
    roughness: 0.55,
    metalness: 0.08,
  });
  const shortsMat = new THREE.MeshStandardMaterial({
    color: isKeeper ? 0x222222 : new THREE.Color(colors.secondary).multiplyScalar(0.55),
    roughness: 0.65,
  });
  const sockMat = new THREE.MeshStandardMaterial({
    color: new THREE.Color(colors.primary),
    roughness: 0.7,
  });
  const bootMat = new THREE.MeshStandardMaterial({
    color: 0xc8ff00,
    roughness: 0.4,
    metalness: 0.2,
  });

  // Head
  const head = new THREE.Mesh(new THREE.SphereGeometry(0.13, 16, 12), skin);
  head.position.y = 1.72;
  head.castShadow = true;
  root.add(head);

  const hair = new THREE.Mesh(new THREE.SphereGeometry(0.135, 12, 10, 0, Math.PI * 2, 0, Math.PI / 2), hairMat);
  hair.position.y = 1.74;
  hair.rotation.x = -0.15;
  root.add(hair);

  // Torso (two-tone training / kit look)
  const torsoTop = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.18, 0.28, 12), jerseyTop);
  torsoTop.position.y = 1.42;
  torsoTop.castShadow = true;
  root.add(torsoTop);

  const torsoBot = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.17, 0.26, 12), jerseyBottom);
  torsoBot.position.y = 1.16;
  torsoBot.castShadow = true;
  root.add(torsoBot);

  // Arms
  const armGeo = new THREE.CapsuleGeometry(0.045, 0.32, 4, 8);
  const leftArm = new THREE.Mesh(armGeo, jerseyTop);
  leftArm.position.set(-0.24, 1.32, 0);
  leftArm.rotation.z = 0.25;
  leftArm.castShadow = true;
  root.add(leftArm);

  const rightArm = new THREE.Mesh(armGeo, jerseyTop);
  rightArm.position.set(0.24, 1.32, 0);
  rightArm.rotation.z = -0.25;
  rightArm.castShadow = true;
  root.add(rightArm);

  // Shorts
  const shorts = new THREE.Mesh(new THREE.CylinderGeometry(0.17, 0.15, 0.28, 12), shortsMat);
  shorts.position.y = 0.9;
  shorts.castShadow = true;
  root.add(shorts);

  // Legs
  const legGeo = new THREE.CapsuleGeometry(0.05, 0.42, 4, 8);
  const leftLeg = new THREE.Mesh(legGeo, sockMat);
  leftLeg.position.set(-0.08, 0.48, 0);
  leftLeg.castShadow = true;
  root.add(leftLeg);

  const rightLeg = new THREE.Mesh(legGeo, sockMat);
  rightLeg.position.set(0.08, 0.48, 0);
  rightLeg.castShadow = true;
  root.add(rightLeg);

  // Boots
  const bootGeo = new THREE.BoxGeometry(0.1, 0.07, 0.18);
  const leftBoot = new THREE.Mesh(bootGeo, bootMat);
  leftBoot.position.set(-0.08, 0.05, 0.03);
  leftBoot.castShadow = true;
  root.add(leftBoot);

  const rightBoot = new THREE.Mesh(bootGeo, bootMat);
  rightBoot.position.set(0.08, 0.05, 0.03);
  rightBoot.castShadow = true;
  root.add(rightBoot);

  // Number plate on back (simple)
  const numberPlate = new THREE.Mesh(
    new THREE.PlaneGeometry(0.14, 0.18),
    new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.85 })
  );
  numberPlate.position.set(0, 1.35, -0.185);
  numberPlate.rotation.y = Math.PI;
  root.add(numberPlate);

  root.userData.parts = { leftLeg, rightLeg, leftArm, rightArm, leftBoot, rightBoot };

  return root;
}

export function animatePlayerWalk(mesh, time, speed) {
  const parts = mesh.userData.parts;
  if (!parts) return;
  const amp = Math.min(0.55, speed * 0.35);
  const swing = Math.sin(time * 10 * Math.max(0.4, speed)) * amp;
  parts.leftLeg.rotation.x = swing;
  parts.rightLeg.rotation.x = -swing;
  parts.leftArm.rotation.x = -swing * 0.7;
  parts.rightArm.rotation.x = swing * 0.7;
}
