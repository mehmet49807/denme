{{-- Inline safety net: icons + header chrome + Premium stay usable if cached CSS is stale --}}
<style id="gk-critical-ui">
.theme-icon{display:inline-flex;align-items:center;justify-content:center;line-height:0;flex-shrink:0;width:1em;height:1em;max-width:2.5rem;max-height:2.5rem;overflow:hidden;vertical-align:middle}
.theme-icon svg{display:block;width:1em!important;height:1em!important;max-width:100%;max-height:100%}
.site-header-toolbar{display:flex;align-items:center;gap:.45rem;flex-shrink:0;margin-left:auto}
.header-premium-btn,.profile-settings-open-btn{display:inline-flex;align-items:center;gap:.4rem;min-height:2.4rem;padding:.45rem .8rem;border-radius:999px;font:inherit;font-size:.84rem;font-weight:700;text-decoration:none;cursor:pointer;white-space:nowrap;border:2px solid #f59e0b73;background:linear-gradient(135deg,#fbbf2429,#fff);color:#b45309}
.profile-settings-open-btn{border-color:#7c3aed59;background:#fffc;color:#7c3aed}
.header-premium-btn-icon,.profile-settings-open-btn-icon{display:inline-flex;width:1.15rem;height:1.15rem;flex-shrink:0}
.header-premium-btn svg,.profile-settings-open-btn svg,.header-premium-btn-icon svg,.profile-settings-open-btn-icon svg{display:block;width:20px!important;height:20px!important;max-width:20px;max-height:20px}
.premium-page{max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:.9rem;width:100%;min-width:0;color:#1a1523;padding:.15rem .1rem .75rem;background:linear-gradient(180deg,#fff7ed 0%,#fff 38%,#f8f5ff 100%);border-radius:1.1rem}
.premium-hero{position:relative;overflow:hidden;padding:1.35rem 1.15rem;border-radius:1.2rem;background:linear-gradient(135deg,#9f1239 0%,#db2777 42%,#ea580c 78%,#f59e0b 100%);color:#fff;box-shadow:0 16px 40px rgba(190,24,93,.28)}
.premium-hero h1{margin:0;font-size:clamp(1.4rem,4.2vw,1.95rem);font-weight:900;letter-spacing:-.03em;line-height:1.15;color:#fff}
.premium-hero-lead{margin:.55rem 0 0;font-size:.92rem;line-height:1.45;color:rgba(255,255,255,.9);max-width:34ch}
.premium-hero-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .7rem;margin-bottom:.7rem;font-size:.7rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#fff;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.35);border-radius:999px}
.premium-hero-badge .theme-icon{width:.95rem;height:.95rem;color:#fde68a}
.premium-hero-cta{display:inline-flex;margin-top:.95rem;padding:.65rem 1rem;border-radius:999px;font-weight:800;font-size:.88rem;text-decoration:none;color:#9a3412;background:#fff;box-shadow:0 8px 22px rgba(0,0,0,.18)}
.premium-package-card,.premium-card,.premium-status,.premium-feature,.premium-app-cta{border-radius:1.05rem;border:1px solid #f1e4d8;background:#fff;box-shadow:0 10px 26px rgba(26,21,35,.05);padding:1rem;color:#1a1523}
.premium-package-card--featured{border-color:#f59e0b8c;background:linear-gradient(160deg,#fff7ed,#fff 55%,#fff1f2)}
.premium-package-head{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:.7rem;align-items:center}
.premium-package-icon,.premium-card-icon,.premium-status-icon,.premium-feature-icon{display:inline-flex;align-items:center;justify-content:center;width:2.35rem;height:2.35rem;border-radius:.85rem;background:linear-gradient(145deg,#fff7ed,#ffedd5);color:#c2410c;flex-shrink:0}
.premium-package-icon .theme-icon,.premium-card-icon .theme-icon,.premium-status-icon .theme-icon,.premium-feature-icon .theme-icon{width:1.2rem;height:1.2rem}
.premium-package-card h3{margin:0 0 .1rem;font-size:1rem;color:#9f1239}
.premium-package-price{margin:0;font-size:1.25rem;font-weight:900;color:#c2410c;white-space:nowrap}
.premium-package-duration,.premium-section-sub{margin:0;font-size:.8rem;color:#78716c}
.premium-package-perks{list-style:none;margin:.55rem 0 0;padding:.65rem 0 0;border-top:1px solid #f1e4d8;display:grid;gap:.25rem}
.premium-package-perks li{position:relative;padding-left:1.05rem;font-size:.78rem;color:#57534e}
.premium-package-perks li:before{content:"✓";position:absolute;left:0;color:#db2777;font-weight:800}
.premium-section-title{margin:0;font-size:1.1rem;font-weight:800;color:#1a1523}
.premium-features-grid{display:grid;gap:.65rem}
.premium-feature{display:grid;grid-template-columns:auto minmax(0,1fr);gap:.75rem;align-items:start}
.premium-feature h3{margin:0 0 .2rem;font-size:.9rem}
.premium-feature p{margin:0;font-size:.8rem;color:#78716c;line-height:1.4}
.premium-status{display:flex;gap:.8rem;align-items:flex-start}
.premium-app-cta h2{margin:0 0 .35rem;font-size:1.05rem}
.premium-app-cta p{margin:0 0 .7rem;font-size:.85rem;color:#78716c}
</style>
