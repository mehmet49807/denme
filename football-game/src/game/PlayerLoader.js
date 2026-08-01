import { createPlayerMesh, animatePlayerWalk as animateProc } from './PlayerFactory.js';

/** GLB asker modeli referansa uymadığı için DLS/FTS prosedürel insan modeli kullanılır. */
export function preloadPlayers() {
  return Promise.resolve(null);
}

export async function createAthlete(colors, isKeeper = false, appearance = {}) {
  return createPlayerMesh(colors, isKeeper, {
    kitStyle: 'training',
    ...appearance,
  });
}

export function animateAthlete(mesh, time, speed, _dt = 0.016) {
  animateProc(mesh, time, speed);
}
