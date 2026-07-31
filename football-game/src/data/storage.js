const KEY = 'gol_arena_save_v1';

const defaultState = () => ({
  auth: null, // { provider, managerName, email?, photoUrl? }
  team: null, // { id, name, short, colors, logoDataUrl, players, managerName }
});

export function loadSave() {
  try {
    const raw = localStorage.getItem(KEY);
    if (!raw) return defaultState();
    const data = JSON.parse(raw);
    return { ...defaultState(), ...data };
  } catch {
    return defaultState();
  }
}

export function saveState(partial) {
  const next = { ...loadSave(), ...partial };
  localStorage.setItem(KEY, JSON.stringify(next));
  return next;
}

export function clearAuth() {
  const cur = loadSave();
  localStorage.setItem(KEY, JSON.stringify({ ...cur, auth: null }));
}

export function clearAll() {
  localStorage.removeItem(KEY);
}

export function wordCount(str) {
  return String(str || '')
    .trim()
    .split(/\s+/)
    .filter(Boolean).length;
}

export function validateTeamName(name) {
  const t = String(name || '').trim().replace(/\s+/g, ' ');
  if (t.length < 2) return 'Takım adı en az 2 karakter olmalı.';
  if (t.length > 28) return 'Takım adı çok uzun.';
  if (wordCount(t) > 3) return 'Takım adı en fazla 3 kelime olabilir.';
  return null;
}

export function validateShortName(short) {
  const s = String(short || '').trim().toUpperCase();
  if (s.length < 2 || s.length > 4) return 'Skor tabelası adı 2–4 harf olmalı.';
  if (!/^[A-ZÇĞİÖŞÜ0-9]{2,4}$/i.test(s)) return 'Sadece harf/rakam kullanın.';
  return null;
}

export function validateManagerName(name) {
  const t = String(name || '').trim();
  if (t.length < 2) return 'Teknik direktör adı en az 2 karakter olmalı.';
  if (t.length > 32) return 'İsim çok uzun.';
  return null;
}
