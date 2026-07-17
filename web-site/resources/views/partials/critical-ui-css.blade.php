{{-- Inline safety net: if app.min.css is stale/cached, icons and Premium still render sized + colored --}}
<style id="gk-critical-ui">
.theme-icon{display:inline-flex;align-items:center;justify-content:center;line-height:0;flex-shrink:0;width:1em;height:1em;max-width:2.5rem;max-height:2.5rem;overflow:hidden;vertical-align:middle}
.theme-icon svg{display:block;width:1em;height:1em;max-width:100%;max-height:100%}
.header-premium-btn svg,.header-settings-open-btn svg,.site-header .header-action-btn svg{display:block;width:20px;height:20px;max-width:20px;max-height:20px}
.premium-page{max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:1rem;width:100%;min-width:0;color:#1a1523}
.premium-hero{position:relative;overflow:hidden;padding:1.75rem 1.5rem;border-radius:1.25rem;background:linear-gradient(135deg,#2d1b4e,#5b21b6 38%,#be185d 72%,#ea580c);color:#fff;box-shadow:0 16px 48px rgba(91,33,182,.28)}
.premium-hero h1{margin:0;font-size:clamp(1.45rem,4vw,2rem);font-weight:900;letter-spacing:-.03em;line-height:1.15;color:#fff}
.premium-hero-lead{margin:.65rem 0 0;font-size:.95rem;line-height:1.45;color:rgba(255,255,255,.88);max-width:36ch}
.premium-hero-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .75rem;margin-bottom:.75rem;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#fff;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);border-radius:999px}
.premium-hero-badge .theme-icon,.premium-hero-perks .theme-icon{width:.95rem;height:.95rem;color:#fbbf24}
.premium-hero-perks{list-style:none;padding:0;margin:1rem 0 0;display:flex;flex-wrap:wrap;gap:.5rem .85rem}
.premium-hero-perks li{display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.9)}
.premium-package-card,.premium-card,.premium-status,.premium-feature-card{border-radius:1.1rem;border:1px solid #e8e0f5;background:#fff;box-shadow:0 10px 28px rgba(26,21,35,.06);padding:1rem 1.1rem;color:#1a1523}
.premium-package-icon,.premium-card-icon,.premium-status-icon,.premium-feature-icon{display:inline-flex;align-items:center;justify-content:center;width:2.4rem;height:2.4rem;border-radius:.85rem;background:linear-gradient(145deg,#f5f3ff,#ede9fe);color:#7c3aed;font-size:1.25rem}
.premium-package-icon .theme-icon,.premium-card-icon .theme-icon,.premium-status-icon .theme-icon,.premium-feature-icon .theme-icon{width:1.25rem;height:1.25rem}
.premium-section-title{margin:0;font-size:1.15rem;font-weight:800;color:#1a1523}
.premium-section-sub{margin:.35rem 0 0;font-size:.88rem;color:#5c5470}
.premium-pkg-cta,.premium-package-cta,.btn-premium,.premium-cta{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem 1rem;border-radius:999px;font-weight:800;text-decoration:none;color:#fff;background:linear-gradient(135deg,#7c3aed,#db2777);border:0}
</style>
