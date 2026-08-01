import * as THREE from 'three';
import { createPlayerMesh, animatePlayerWalk } from './PlayerFactory.js';
import { createBall, updateBall } from './Ball.js';
import { FIELD } from './Pitch.js';

const FORMATION = [
  { x: 0, z: -45 }, // GK
  { x: -10, z: -32 },
  { x: 10, z: -32 },
  { x: -20, z: -28 },
  { x: 20, z: -28 },
  { x: 0, z: -20 },
  { x: -12, z: -12 },
  { x: 12, z: -12 },
  { x: -18, z: -4 },
  { x: 18, z: -4 },
  { x: 0, z: 2 }, // ST
];

export class Match {
  constructor({ scene, homeTeam, awayTeam, onGoal, onEnd, onPossession }) {
    this.scene = scene;
    this.homeTeam = homeTeam;
    this.awayTeam = awayTeam;
    this.onGoal = onGoal;
    this.onEnd = onEnd;
    this.onPossession = onPossession;

    this.score = { home: 0, away: 0 };
    this.minute = 0;
    this._timeAcc = 0;
    this.matchDurationSec = 90; // real seconds ≈ 90 match minutes
    this.paused = false;
    this.ended = false;
    this._goalLock = 0;

    this.ball = createBall();
    scene.add(this.ball.mesh);

    this.players = [];
    this._spawnTeam(homeTeam, 1);
    this._spawnTeam(awayTeam, -1);

    this.controlled = this.players.find((p) => p.side === 1 && p.data.pos === 'ST')
      || this.players.find((p) => p.side === 1);
    this.kickCooldown = 0;

    // Controlled player ground ring
    this.controlRing = new THREE.Mesh(
      new THREE.RingGeometry(0.45, 0.58, 32),
      new THREE.MeshBasicMaterial({ color: 0xd4a017, transparent: true, opacity: 0.85, side: THREE.DoubleSide })
    );
    this.controlRing.rotation.x = -Math.PI / 2;
    this.controlRing.position.y = 0.03;
    scene.add(this.controlRing);
  }

  _spawnTeam(team, side) {
    team.players.forEach((data, i) => {
      const form = FORMATION[i] || FORMATION[FORMATION.length - 1];
      const seed = (data.name?.charCodeAt(0) || 0) + i * 17 + (side > 0 ? 0 : 99);
      const mesh = createPlayerMesh(team.colors, data.pos === 'GK', {
        skin: [0xe0ac69, 0xc68642, 0x8d5524, 0xd4a574, 0xb07d4f][seed % 5],
        hair: [0x1a120c, 0x2c1810, 0x0d0d0d, 0x3b2f2f][seed % 4],
        beard: seed % 3 !== 0,
      });
      const z = form.z * side;
      const x = form.x;
      mesh.position.set(x, 0, z);
      mesh.rotation.y = side === 1 ? 0 : Math.PI;
      this.scene.add(mesh);

      // Name label (küçük, oyuncunun üstünde)
      const label = makeNameSprite(data.name.split(' ').slice(-1)[0], data.number);
      label.position.y = 1.95;
      label.scale.set(1.6, 0.4, 1);
      mesh.add(label);

      this.players.push({
        mesh,
        data,
        side,
        team,
        home: { x, z },
        vel: new THREE.Vector3(),
        stamina: 1,
      });
    });
  }

  resetKickoff(towardSide = 1) {
    this.ball.mesh.position.set(0, 0.22, 0);
    this.ball.velocity.set(0, 0, 0.8 * towardSide);
    for (const p of this.players) {
      p.mesh.position.set(p.home.x, 0, p.home.z);
      p.vel.set(0, 0, 0);
    }
    this.kickCooldown = 0.4;
  }

  update(dt, input) {
    if (this.paused || this.ended) return;

    this._timeAcc += dt;
    this.minute = Math.min(90, Math.floor((this._timeAcc / this.matchDurationSec) * 90));
    if (this._timeAcc >= this.matchDurationSec) {
      this.ended = true;
      this.onEnd?.(this.score);
      return;
    }

    if (this._goalLock > 0) {
      this._goalLock -= dt;
      if (this._goalLock <= 0) this.resetKickoff(this._lastGoalSide === 1 ? -1 : 1);
      updateBall(this.ball, dt, FIELD);
      return;
    }

    this.kickCooldown = Math.max(0, this.kickCooldown - dt);
    this._autoSwitch();
    this._updateHuman(dt, input);
    this._updateAI(dt);
    updateBall(this.ball, dt, FIELD);
    this._ballPlayerCollision();
    this._checkGoal();

    const owner = this._nearestToBall();
    if (owner) this.onPossession?.(owner.data.name);

    if (this.controlRing && this.controlled) {
      this.controlRing.position.x = this.controlled.mesh.position.x;
      this.controlRing.position.z = this.controlled.mesh.position.z;
      this.controlRing.rotation.z = performance.now() * 0.002;
    }
  }

  _autoSwitch() {
    // Switch to nearest outfield player on ball side when ball far from controlled
    const human = this.players.filter((p) => p.side === 1 && p.data.pos !== 'GK');
    let best = this.controlled;
    let bestD = Infinity;
    const bp = this.ball.mesh.position;
    for (const p of human) {
      const d = p.mesh.position.distanceTo(bp);
      if (d < bestD) {
        bestD = d;
        best = p;
      }
    }
    if (best && best !== this.controlled) {
      const curD = this.controlled.mesh.position.distanceTo(bp);
      if (bestD + 2 < curD) this.controlled = best;
    }
  }

  _updateHuman(dt, input) {
    const p = this.controlled;
    if (!p) return;

    const base = 7 + p.data.pace * 0.05;
    const speed = input.sprint ? base * 1.35 : base;
    const moveX = input.x;
    const moveZ = input.y;

    p.vel.x = moveX * speed;
    p.vel.z = moveZ * speed;
    p.mesh.position.x += p.vel.x * dt;
    p.mesh.position.z += p.vel.z * dt;
    this._clampPlayer(p);

    if (Math.hypot(moveX, moveZ) > 0.1) {
      p.mesh.rotation.y = Math.atan2(moveX, moveZ);
    }

    const spd = Math.hypot(p.vel.x, p.vel.z);
    animatePlayerWalk(p.mesh, performance.now() / 1000, spd / 8);

    const dist = p.mesh.position.distanceTo(this.ball.mesh.position);
    if (dist < 1.6 && this.kickCooldown <= 0) {
      if (input.shoot) this._kick(p, 'shoot');
      else if (input.pass) this._kick(p, 'pass');
      else if (spd > 1.5 && dist < 1.1) this._dribble(p);
    }
  }

  _updateAI(dt) {
    const ball = this.ball.mesh.position;
    for (const p of this.players) {
      if (p === this.controlled) continue;

      const isGK = p.data.pos === 'GK';
      let tx = p.home.x;
      let tz = p.home.z;

      const toBall = ball.clone().sub(p.mesh.position);
      const dist = toBall.length();

      if (isGK) {
        tx = THREE.MathUtils.clamp(ball.x, -3, 3);
        tz = p.home.z + THREE.MathUtils.clamp((ball.z - p.home.z) * 0.08, -2, 2);
      } else {
        // Support / press
        const attackDir = p.side;
        const roleZ = p.home.z;
        tx = THREE.MathUtils.lerp(p.home.x, ball.x, 0.35);
        tz = THREE.MathUtils.lerp(roleZ, ball.z - attackDir * 6, 0.45);

        // Closest AI to ball presses harder
        if (dist < 14 && this._isCloserThanTeammates(p, dist)) {
          tx = ball.x;
          tz = ball.z;
        }
      }

      const dx = tx - p.mesh.position.x;
      const dz = tz - p.mesh.position.z;
      const len = Math.hypot(dx, dz) || 1;
      const maxSpd = (6 + p.data.pace * 0.04) * (p.side === -1 ? 0.92 : 0.88);
      const step = Math.min(maxSpd, len * 3);
      p.vel.x = (dx / len) * step;
      p.vel.z = (dz / len) * step;
      p.mesh.position.x += p.vel.x * dt;
      p.mesh.position.z += p.vel.z * dt;
      this._clampPlayer(p);

      if (len > 0.2) p.mesh.rotation.y = Math.atan2(dx, dz);
      animatePlayerWalk(p.mesh, performance.now() / 1000 + p.data.number, step / 8);

      // AI kick
      if (dist < 1.35 && this.kickCooldown <= 0 && Math.random() < 0.02) {
        const towardGoal = -p.side;
        const goalDist = Math.abs(ball.z - towardGoal * FIELD.halfW);
        this._kick(p, goalDist < 28 && Math.random() < 0.55 ? 'shoot' : 'pass');
      }
    }
  }

  _isCloserThanTeammates(p, dist) {
    for (const o of this.players) {
      if (o === p || o.side !== p.side || o.data.pos === 'GK') continue;
      if (o.mesh.position.distanceTo(this.ball.mesh.position) + 0.5 < dist) return false;
    }
    return true;
  }

  _clampPlayer(p) {
    p.mesh.position.x = THREE.MathUtils.clamp(p.mesh.position.x, -FIELD.halfL + 0.5, FIELD.halfL - 0.5);
    p.mesh.position.z = THREE.MathUtils.clamp(p.mesh.position.z, -FIELD.halfW + 0.5, FIELD.halfW - 0.5);
    // Keep GK near goal
    if (p.data.pos === 'GK') {
      const gz = p.side * -FIELD.halfW;
      p.mesh.position.z = THREE.MathUtils.clamp(p.mesh.position.z, Math.min(gz, gz + p.side * 8), Math.max(gz, gz + p.side * 8));
      p.mesh.position.x = THREE.MathUtils.clamp(p.mesh.position.x, -8, 8);
    }
  }

  _dribble(p) {
    const dir = new THREE.Vector3(Math.sin(p.mesh.rotation.y), 0, Math.cos(p.mesh.rotation.y));
    this.ball.mesh.position.copy(p.mesh.position).addScaledVector(dir, 0.85);
    this.ball.mesh.position.y = 0.22;
    this.ball.velocity.copy(dir).multiplyScalar(3 + p.data.pace * 0.03);
    this.ball.velocity.y = 0;
  }

  _kick(p, type) {
    const power = type === 'shoot'
      ? 18 + p.data.shooting * 0.12
      : 12 + p.data.passing * 0.08;
    let dir;

    if (type === 'pass') {
      const mate = this._findPassTarget(p);
      if (mate) {
        dir = mate.mesh.position.clone().sub(this.ball.mesh.position);
      }
    }
    if (!dir) {
      // Shoot toward opponent goal
      const goalZ = -p.side * FIELD.halfW;
      dir = new THREE.Vector3(-this.ball.mesh.position.x * 0.15, 0, goalZ - this.ball.mesh.position.z);
    }
    dir.y = 0;
    if (dir.lengthSq() < 0.01) dir.set(0, 0, -p.side);
    dir.normalize();

    this.ball.velocity.copy(dir).multiplyScalar(power);
    this.ball.velocity.y = type === 'shoot' ? 2.2 + Math.random() * 1.5 : 0.6;
    this.kickCooldown = 0.35;
    p.mesh.rotation.y = Math.atan2(dir.x, dir.z);
  }

  _findPassTarget(p) {
    let best = null;
    let bestScore = -Infinity;
    const forward = -p.side;
    for (const o of this.players) {
      if (o === p || o.side !== p.side) continue;
      const dz = (o.mesh.position.z - p.mesh.position.z) * forward;
      const dist = o.mesh.position.distanceTo(p.mesh.position);
      if (dist < 3 || dist > 28) continue;
      const score = dz * 2 - dist * 0.3 + o.data.passing * 0.05;
      if (score > bestScore) {
        bestScore = score;
        best = o;
      }
    }
    return best;
  }

  _ballPlayerCollision() {
    for (const p of this.players) {
      const d = p.mesh.position.distanceTo(this.ball.mesh.position);
      if (d < 0.75) {
        const push = this.ball.mesh.position.clone().sub(p.mesh.position);
        push.y = 0;
        if (push.lengthSq() < 0.001) push.set(Math.random() - 0.5, 0, Math.random() - 0.5);
        push.normalize();
        this.ball.mesh.position.copy(p.mesh.position).addScaledVector(push, 0.8);
        this.ball.mesh.position.y = 0.22;
        const bump = 2 + p.data.physical * 0.02;
        this.ball.velocity.addScaledVector(push, bump);
        this.ball.velocity.addScaledVector(p.vel, 0.35);
      }
    }
  }

  _checkGoal() {
    const { x, y, z } = this.ball.mesh.position;
    const inGoalX = Math.abs(x) < 3.66;
    const inGoalY = y < 2.5;

    if (inGoalX && inGoalY && z < -FIELD.halfW - 0.3) {
      this._registerGoal(1);
    } else if (inGoalX && inGoalY && z > FIELD.halfW + 0.3) {
      this._registerGoal(-1);
    } else if (Math.abs(z) > FIELD.halfW + 0.5 && !inGoalX) {
      // Goal kick / rough reset
      this.ball.mesh.position.set(0, 0.22, Math.sign(z) * (FIELD.halfW - 8));
      this.ball.velocity.set(0, 0, -Math.sign(z) * 4);
    }
  }

  _registerGoal(scoredBySide) {
    if (this._goalLock > 0) return;
    if (scoredBySide === 1) this.score.home += 1;
    else this.score.away += 1;
    this._lastGoalSide = scoredBySide;
    this._goalLock = 2.2;

    const scorer = this._nearestToBall(scoredBySide);
    this.onGoal?.(this.score, scorer?.data.name ?? 'Oyuncu');
    this.ball.velocity.set(0, 0, 0);
  }

  _nearestToBall(side = null) {
    let best = null;
    let bestD = Infinity;
    for (const p of this.players) {
      if (side != null && p.side !== side) continue;
      const d = p.mesh.position.distanceToSquared(this.ball.mesh.position);
      if (d < bestD) {
        bestD = d;
        best = p;
      }
    }
    return best;
  }

  getCameraTarget() {
    const ball = this.ball.mesh.position;
    const player = this.controlled?.mesh.position;
    if (!player) return ball.clone();
    return new THREE.Vector3(
      THREE.MathUtils.lerp(player.x, ball.x, 0.35),
      0,
      THREE.MathUtils.lerp(player.z, ball.z, 0.45)
    );
  }

  dispose() {
    for (const p of this.players) {
      this.scene.remove(p.mesh);
    }
    this.scene.remove(this.ball.mesh);
    if (this.controlRing) this.scene.remove(this.controlRing);
    this.players = [];
  }
}

function makeNameSprite(name, number) {
  const canvas = document.createElement('canvas');
  canvas.width = 256;
  canvas.height = 64;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, 256, 64);
  ctx.fillStyle = 'rgba(0,0,0,0.45)';
  roundRect(ctx, 28, 12, 200, 40, 10);
  ctx.fill();
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 22px DM Sans, sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText(`${number}  ${name}`, 128, 40);
  const tex = new THREE.CanvasTexture(canvas);
  const mat = new THREE.SpriteMaterial({ map: tex, transparent: true, depthTest: false });
  const sprite = new THREE.Sprite(mat);
  sprite.scale.set(2.2, 0.55, 1);
  return sprite;
}

function roundRect(ctx, x, y, w, h, r) {
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.arcTo(x + w, y, x + w, y + h, r);
  ctx.arcTo(x + w, y + h, x, y + h, r);
  ctx.arcTo(x, y + h, x, y, r);
  ctx.arcTo(x, y, x + w, y, r);
  ctx.closePath();
}
