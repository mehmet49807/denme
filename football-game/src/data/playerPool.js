/**
 * Gerçek futbolcu isimleri — kulüp markası yok.
 * Kadro otomatik bu havuzdan atanır.
 */

const GK = [
  'Thibaut Courtois', 'Alisson', 'Ederson', 'Marc-André ter Stegen',
  'Jan Oblak', 'Mike Maignan', 'Gianluigi Donnarumma', 'Unai Simón',
  'Uğurcan Çakır', 'Dominik Livaković', 'Fernando Muslera', 'Mert Günok',
];

const DEF = [
  'Virgil van Dijk', 'Rúben Dias', 'Antonio Rüdiger', 'William Saliba',
  'Ronald Araújo', 'Alessandro Bastoni', 'Joško Gvardiol', 'Trent Alexander-Arnold',
  'Achraf Hakimi', 'Theo Hernández', 'Davinson Sánchez', 'Çağlar Söyüncü',
  'Abdulkerim Bardakcı', 'Jules Koundé', 'Alejandro Balde', 'Kyle Walker',
  'Andrew Robertson', 'Ferland Mendy', 'Bright Osayi-Samuel', 'Jayden Oosterwolde',
];

const MID = [
  'Jude Bellingham', 'Rodri', 'Pedri', 'Kevin De Bruyne',
  'Martin Ødegaard', 'Declan Rice', 'Federico Valverde', 'Bernardo Silva',
  'İrfan Can Kahveci', 'Lucas Torreira', 'Fred', 'Gavi',
  'Alexis Mac Allister', 'Florian Wirtz', 'Jamal Musiala', 'Bruno Fernandes',
  'Kerem Demirbay', 'Sebastian Szymański', 'Eduardo Camavinga', 'Nicolò Barella',
];

const ATT = [
  'Kylian Mbappé', 'Erling Haaland', 'Vinícius Júnior', 'Mohamed Salah',
  'Harry Kane', 'Robert Lewandowski', 'Lautaro Martínez', 'Lamine Yamal',
  'Mauro Icardi', 'Barış Alper Yılmaz', 'Youssef En-Nesyri', 'Rodrygo',
  'Phil Foden', 'Bukayo Saka', 'Rafa Silva', 'Dries Mertens',
  'Dušan Tadić', 'Darwin Núñez', 'Julián Álvarez', 'Raphinha',
];

const POSITIONS = [
  { pos: 'GK', pool: GK, number: 1 },
  { pos: 'CB', pool: DEF, number: 4 },
  { pos: 'CB', pool: DEF, number: 5 },
  { pos: 'LB', pool: DEF, number: 3 },
  { pos: 'RB', pool: DEF, number: 2 },
  { pos: 'CDM', pool: MID, number: 6 },
  { pos: 'CM', pool: MID, number: 8 },
  { pos: 'CM', pool: MID, number: 14 },
  { pos: 'RW', pool: ATT, number: 7 },
  { pos: 'LW', pool: ATT, number: 11 },
  { pos: 'ST', pool: ATT, number: 9 },
];

const BASE_STATS = {
  GK: { pace: 48, shooting: 22, passing: 68, defending: 82, physical: 76 },
  CB: { pace: 72, shooting: 42, passing: 66, defending: 84, physical: 82 },
  LB: { pace: 82, shooting: 52, passing: 72, defending: 76, physical: 74 },
  RB: { pace: 82, shooting: 52, passing: 72, defending: 76, physical: 74 },
  CDM: { pace: 70, shooting: 64, passing: 80, defending: 82, physical: 80 },
  CM: { pace: 74, shooting: 72, passing: 84, defending: 70, physical: 74 },
  RW: { pace: 88, shooting: 80, passing: 78, defending: 40, physical: 70 },
  LW: { pace: 88, shooting: 80, passing: 78, defending: 40, physical: 70 },
  ST: { pace: 82, shooting: 88, passing: 70, defending: 34, physical: 80 },
};

function rand(min, max) {
  return min + Math.floor(Math.random() * (max - min + 1));
}

function jitter(base) {
  const out = {};
  for (const [k, v] of Object.entries(base)) {
    out[k] = Math.max(20, Math.min(97, v + rand(-6, 8)));
  }
  return out;
}

function pickUnique(pool, used) {
  const available = pool.filter((n) => !used.has(n));
  const list = available.length ? available : pool;
  const name = list[rand(0, list.length - 1)];
  used.add(name);
  return name;
}

/** 11 kişilik kadro — gerçek isimler, rastgele güç. */
export function generateSquad(seed = Math.random()) {
  // simple seeded shuffle influence
  const used = new Set();
  const players = POSITIONS.map((slot, i) => {
    // slight seed mix
    const name = pickUnique(slot.pool, used);
    const stats = jitter(BASE_STATS[slot.pos] || BASE_STATS.CM);
    // bump a few stars using seed
    if (((seed * 1000 + i * 17) % 7) < 2) {
      stats.shooting = Math.min(96, stats.shooting + 6);
      stats.pace = Math.min(97, stats.pace + 4);
    }
    return {
      name,
      pos: slot.pos,
      number: slot.number,
      ...stats,
    };
  });
  return players;
}

export function teamOvr(players) {
  if (!players?.length) return 0;
  const vals = players.map(
    (p) => (p.pace + p.shooting + p.passing + p.defending + p.physical) / 5
  );
  return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
}
