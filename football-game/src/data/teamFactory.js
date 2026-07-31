import { generateSquad, teamOvr } from './playerPool.js';
import { listAiTemplates } from './aiTeams.js';
import { makeAiLogo } from '../ui/logoEditor.js';

export function createUserTeam({
  name,
  short,
  colors,
  logoDataUrl,
  managerName,
}) {
  const players = generateSquad(Date.now() % 1000);
  return {
    id: `user_${Date.now()}`,
    name: name.trim().replace(/\s+/g, ' '),
    short: short.trim().toUpperCase(),
    colors,
    logoDataUrl,
    managerName,
    players,
    ovr: teamOvr(players),
    createdAt: Date.now(),
  };
}

/** Kullanıcı takımı dışında telifsiz AI rakipler. */
export function buildAiOpponents(excludeShort = '') {
  return listAiTemplates()
    .filter((t) => t.short !== excludeShort)
    .map((t) => {
      const players = generateSquad(t.short.charCodeAt(0) * 13);
      return {
        id: t.id,
        name: t.name,
        short: t.short,
        colors: t.colors,
        logoDataUrl: makeAiLogo(t.short, t.colors),
        managerName: 'AI Teknik Direktör',
        players,
        ovr: teamOvr(players),
        isAi: true,
      };
    });
}

export { teamOvr };
