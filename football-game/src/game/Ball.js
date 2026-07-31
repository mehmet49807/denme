import * as THREE from 'three';

export function createBall() {
  const canvas = document.createElement('canvas');
  canvas.width = 256;
  canvas.height = 256;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#f5f5f5';
  ctx.fillRect(0, 0, 256, 256);
  ctx.fillStyle = '#111';
  // pentagon-ish pattern
  for (let y = 0; y < 5; y++) {
    for (let x = 0; x < 5; x++) {
      if ((x + y) % 2 === 0) {
        ctx.beginPath();
        const cx = 25 + x * 50;
        const cy = 25 + y * 50;
        for (let i = 0; i < 5; i++) {
          const a = (i / 5) * Math.PI * 2 - Math.PI / 2;
          const px = cx + Math.cos(a) * 16;
          const py = cy + Math.sin(a) * 16;
          if (i === 0) ctx.moveTo(px, py);
          else ctx.lineTo(px, py);
        }
        ctx.closePath();
        ctx.fill();
      }
    }
  }

  const tex = new THREE.CanvasTexture(canvas);
  const mesh = new THREE.Mesh(
    new THREE.SphereGeometry(0.22, 24, 18),
    new THREE.MeshStandardMaterial({ map: tex, roughness: 0.45, metalness: 0.05 })
  );
  mesh.castShadow = true;
  mesh.position.set(0, 0.22, 0);

  return {
    mesh,
    velocity: new THREE.Vector3(),
    radius: 0.22,
    onGround: true,
  };
}

export function updateBall(ball, dt, field) {
  const g = -18;
  ball.velocity.y += g * dt;
  ball.mesh.position.addScaledVector(ball.velocity, dt);

  // Ground
  if (ball.mesh.position.y < ball.radius) {
    ball.mesh.position.y = ball.radius;
    if (ball.velocity.y < 0) ball.velocity.y *= -0.45;
    ball.velocity.x *= 0.985;
    ball.velocity.z *= 0.985;
    if (Math.abs(ball.velocity.y) < 0.4) ball.velocity.y = 0;
  }

  // Soft bounds (kick-in style bounce)
  const { halfL, halfW } = field;
  if (Math.abs(ball.mesh.position.x) > halfL) {
    ball.mesh.position.x = Math.sign(ball.mesh.position.x) * halfL;
    ball.velocity.x *= -0.55;
  }
  // Behind goal line — leave to match for goal / goal kick
  if (Math.abs(ball.mesh.position.z) > halfW + 3) {
    ball.mesh.position.z = Math.sign(ball.mesh.position.z) * (halfW + 3);
    ball.velocity.z *= -0.3;
  }

  // Spin visual
  const speed = Math.hypot(ball.velocity.x, ball.velocity.z);
  if (speed > 0.1) {
    ball.mesh.rotation.x += ball.velocity.z * dt * 2;
    ball.mesh.rotation.z -= ball.velocity.x * dt * 2;
  }

  // Friction air
  ball.velocity.x *= 1 - 0.35 * dt;
  ball.velocity.z *= 1 - 0.35 * dt;
}
