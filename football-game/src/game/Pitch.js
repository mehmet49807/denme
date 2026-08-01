import * as THREE from 'three';

export const FIELD = {
  length: 68, // X (touchline to touchline) — rotated: length along Z
  width: 105, // Z goal to goal
  halfW: 52.5,
  halfL: 34,
};

export function createPitchScene() {
  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x7eb6d9);
  scene.fog = new THREE.Fog(0x9ec6e0, 55, 140);

  const hemi = new THREE.HemisphereLight(0xf0f6ff, 0x3d6b3a, 1.05);
  scene.add(hemi);

  const sun = new THREE.DirectionalLight(0xfff4e0, 1.7);
  sun.position.set(25, 55, 18);
  sun.castShadow = true;
  sun.shadow.mapSize.set(2048, 2048);
  sun.shadow.bias = -0.0002;
  sun.shadow.camera.near = 1;
  sun.shadow.camera.far = 140;
  sun.shadow.camera.left = -60;
  sun.shadow.camera.right = 60;
  sun.shadow.camera.top = 60;
  sun.shadow.camera.bottom = -60;
  scene.add(sun);

  // Grass with stripes
  const grassCanvas = document.createElement('canvas');
  grassCanvas.width = 512;
  grassCanvas.height = 512;
  const gctx = grassCanvas.getContext('2d');
  for (let i = 0; i < 16; i++) {
    gctx.fillStyle = i % 2 === 0 ? '#2d9b4a' : '#268a40';
    gctx.fillRect(0, (i * 512) / 16, 512, 512 / 16);
  }
  const grassTex = new THREE.CanvasTexture(grassCanvas);
  grassTex.wrapS = THREE.RepeatWrapping;
  grassTex.wrapT = THREE.RepeatWrapping;
  grassTex.repeat.set(6, 10);

  const grass = new THREE.Mesh(
    new THREE.PlaneGeometry(FIELD.length + 8, FIELD.width + 8),
    new THREE.MeshStandardMaterial({ map: grassTex, roughness: 0.95 })
  );
  grass.rotation.x = -Math.PI / 2;
  grass.receiveShadow = true;
  scene.add(grass);

  // Pitch lines
  const lineMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
  const addLine = (w, h, x, z) => {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h), lineMat);
    m.rotation.x = -Math.PI / 2;
    m.position.set(x, 0.02, z);
    scene.add(m);
  };

  // Outer boundary
  addLine(FIELD.length, 0.18, 0, -FIELD.halfW);
  addLine(FIELD.length, 0.18, 0, FIELD.halfW);
  addLine(0.18, FIELD.width, -FIELD.halfL, 0);
  addLine(0.18, FIELD.width, FIELD.halfL, 0);
  // Halfway
  addLine(FIELD.length, 0.16, 0, 0);
  // Center circle
  const ring = new THREE.Mesh(
    new THREE.RingGeometry(9.05, 9.25, 64),
    lineMat
  );
  ring.rotation.x = -Math.PI / 2;
  ring.position.y = 0.021;
  scene.add(ring);

  const centerSpot = new THREE.Mesh(new THREE.CircleGeometry(0.3, 16), lineMat);
  centerSpot.rotation.x = -Math.PI / 2;
  centerSpot.position.y = 0.022;
  scene.add(centerSpot);

  // Penalty boxes
  const boxW = 40.32;
  const boxD = 16.5;
  for (const side of [-1, 1]) {
    const z = side * (FIELD.halfW - boxD / 2);
    addLine(boxW, 0.14, 0, z - side * (boxD / 2));
    addLine(boxW, 0.14, 0, z + side * (boxD / 2));
    addLine(0.14, boxD, -boxW / 2, z);
    addLine(0.14, boxD, boxW / 2, z);
  }

  // Goals
  scene.add(createGoal(-FIELD.halfW));
  scene.add(createGoal(FIELD.halfW));

  // Stadium bowls / crowd
  scene.add(createStadium());

  // Training cones near center (visual flavor from reference)
  const coneMat = new THREE.MeshStandardMaterial({ color: 0xff6a00, roughness: 0.55 });
  for (const x of [-3, 3]) {
    const cone = new THREE.Mesh(new THREE.ConeGeometry(0.22, 0.45, 10), coneMat);
    cone.position.set(x, 0.22, 8);
    cone.castShadow = true;
    scene.add(cone);
  }

  return scene;
}

function createGoal(z) {
  const group = new THREE.Group();
  const postMat = new THREE.MeshStandardMaterial({ color: 0xf5f5f5, roughness: 0.35, metalness: 0.2 });
  const goalW = 7.32;
  const goalH = 2.44;
  const postR = 0.07;

  const left = new THREE.Mesh(new THREE.CylinderGeometry(postR, postR, goalH, 10), postMat);
  left.position.set(-goalW / 2, goalH / 2, z);
  group.add(left);

  const right = new THREE.Mesh(new THREE.CylinderGeometry(postR, postR, goalH, 10), postMat);
  right.position.set(goalW / 2, goalH / 2, z);
  group.add(right);

  const bar = new THREE.Mesh(new THREE.CylinderGeometry(postR, postR, goalW, 10), postMat);
  bar.rotation.z = Math.PI / 2;
  bar.position.set(0, goalH, z);
  group.add(bar);

  // Net (simple grid)
  const netMat = new THREE.MeshBasicMaterial({
    color: 0xffffff,
    transparent: true,
    opacity: 0.22,
    side: THREE.DoubleSide,
    wireframe: true,
  });
  const depth = z < 0 ? -2.2 : 2.2;
  const net = new THREE.Mesh(new THREE.PlaneGeometry(goalW, goalH, 8, 4), netMat);
  net.position.set(0, goalH / 2, z + depth * 0.15);
  group.add(net);

  const back = new THREE.Mesh(new THREE.PlaneGeometry(goalW, goalH, 8, 4), netMat);
  back.position.set(0, goalH / 2, z + depth);
  group.add(back);

  group.userData.goalZ = z;
  group.userData.goalWidth = goalW;
  group.userData.goalHeight = goalH;
  return group;
}

function createStadium() {
  const group = new THREE.Group();

  const standMat = new THREE.MeshStandardMaterial({ color: 0x3a4658, roughness: 0.9 });

  const makeStand = (w, d, x, z) => {
    const s = new THREE.Mesh(new THREE.BoxGeometry(w, 10, d), standMat);
    s.position.set(x, 4.5, z);
    s.castShadow = true;
    s.receiveShadow = true;
    group.add(s);

    // Crowd texture strip
    const crowd = makeCrowdPlane(w * 0.95, 6);
    crowd.position.set(x, 6.5, z + (z > 0 ? -d / 2 - 0.05 : z < 0 ? d / 2 + 0.05 : 0));
    if (Math.abs(x) > Math.abs(z)) {
      crowd.position.set(x + (x > 0 ? -d / 2 - 0.05 : d / 2 + 0.05), 6.5, z);
      crowd.rotation.y = Math.PI / 2;
    }
    group.add(crowd);
  };

  makeStand(FIELD.length + 24, 6, 0, FIELD.halfW + 10);
  makeStand(FIELD.length + 24, 6, 0, -(FIELD.halfW + 10));
  makeStand(6, FIELD.width + 12, FIELD.halfL + 10, 0);
  makeStand(6, FIELD.width + 12, -(FIELD.halfL + 10), 0);

  // Ad boards
  const adMat = new THREE.MeshStandardMaterial({ color: 0x1a3a6e, roughness: 0.4, metalness: 0.3 });
  for (const z of [-FIELD.halfW - 2.5, FIELD.halfW + 2.5]) {
    const board = new THREE.Mesh(new THREE.BoxGeometry(FIELD.length, 1.1, 0.25), adMat);
    board.position.set(0, 0.55, z);
    group.add(board);
  }

  return group;
}

function makeCrowdPlane(w, h) {
  const c = document.createElement('canvas');
  c.width = 256;
  c.height = 64;
  const ctx = c.getContext('2d');
  ctx.fillStyle = '#1e2a3a';
  ctx.fillRect(0, 0, 256, 64);
  for (let i = 0; i < 400; i++) {
    const colors = ['#1d4e89', '#c9a227', '#c8102e', '#2d6a4f', '#ffffff', '#6c757d'];
    ctx.fillStyle = colors[i % colors.length];
    ctx.fillRect(Math.random() * 256, Math.random() * 64, 2 + Math.random() * 3, 3 + Math.random() * 5);
  }
  const tex = new THREE.CanvasTexture(c);
  const mat = new THREE.MeshBasicMaterial({ map: tex });
  return new THREE.Mesh(new THREE.PlaneGeometry(w, h), mat);
}
