/** Telifsiz rakip takım isimleri (kullanıcı kulüpleri değil). */

const AI_NAMES = [
  { name: 'Kuzey Fırtınası', short: 'KF' },
  { name: 'Mavi Dalga', short: 'MD' },
  { name: 'Kartal Vadi', short: 'KV' },
  { name: 'Altın Boynuz', short: 'AB' },
  { name: 'Şehir Ateşi', short: 'ŞA' },
  { name: 'Yeşil Hat', short: 'YH' },
  { name: 'Demir Liman', short: 'DL' },
  { name: 'Güneş Park', short: 'GP' },
  { name: 'Beyaz Tepe', short: 'BT' },
  { name: 'Kırmızı Rüzgar', short: 'KR' },
  { name: 'Gökyüzü SK', short: 'GSK' },
  { name: 'Nehir Spor', short: 'NS' },
];

const COLOR_PAIRS = [
  { primary: '#1b6ca8', secondary: '#f0c14b', accent: '#ffffff' },
  { primary: '#0d5c3d', secondary: '#e8e8e8', accent: '#d4a017' },
  { primary: '#8b1e3f', secondary: '#f5f5f5', accent: '#222222' },
  { primary: '#2c2c2c', secondary: '#e85d04', accent: '#ffffff' },
  { primary: '#13315c', secondary: '#76c893', accent: '#ffffff' },
  { primary: '#5a189a', secondary: '#ffd60a', accent: '#ffffff' },
  { primary: '#9b2226', secondary: '#001219', accent: '#ee9b00' },
  { primary: '#0077b6', secondary: '#caf0f8', accent: '#023e8a' },
];

export function listAiTemplates() {
  return AI_NAMES.map((t, i) => ({
    ...t,
    id: `ai_${i}`,
    colors: COLOR_PAIRS[i % COLOR_PAIRS.length],
  }));
}
