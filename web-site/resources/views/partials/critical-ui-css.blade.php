{{-- Inline safety net: icons + header chrome stay sized if cached CSS is stale --}}
<style id="gk-critical-ui">
.theme-icon{display:inline-flex;align-items:center;justify-content:center;line-height:0;flex-shrink:0;width:1em;height:1em;max-width:2.5rem;max-height:2.5rem;overflow:hidden;vertical-align:middle}
.theme-icon svg{display:block;width:1em!important;height:1em!important;max-width:100%;max-height:100%}
.site-header-toolbar{display:flex;align-items:center;gap:.45rem;flex-shrink:0;margin-left:auto}
.header-premium-btn,.profile-settings-open-btn,.header-matches-btn{display:inline-flex;align-items:center;gap:.4rem;min-height:2.4rem;padding:.45rem .8rem;border-radius:999px;font:inherit;font-size:.84rem;font-weight:700;text-decoration:none;cursor:pointer;white-space:nowrap;border:2px solid #f59e0b73;background:linear-gradient(135deg,#fbbf2429,#fff);color:#b45309}
.profile-settings-open-btn{border-color:#7c3aed59;background:#fffc;color:#7c3aed}
.header-matches-btn{border-color:#db277759;background:#fffc;color:#be185d}
.header-premium-btn-icon,.profile-settings-open-btn-icon,.header-matches-btn-icon{display:inline-flex;width:1.15rem;height:1.15rem;flex-shrink:0;will-change:transform,filter}
.header-premium-btn-icon{color:#fbbf24;animation:headerPremiumIconPulse 2.2s ease-in-out infinite}
.profile-settings-open-btn-icon{color:#7c3aed;animation:headerSettingsIconSpin 3.6s linear infinite}
.header-matches-btn-icon{color:#db2777;animation:headerMatchesIconBeat 1.6s ease-in-out infinite}
.header-premium-btn svg,.profile-settings-open-btn svg,.header-matches-btn svg,.header-premium-btn-icon svg,.profile-settings-open-btn-icon svg,.header-matches-btn-icon svg{display:block;width:20px!important;height:20px!important;max-width:20px;max-height:20px;overflow:visible}
.header-premium-btn-icon svg path,.profile-settings-open-btn-icon svg path,.profile-settings-open-btn-icon svg circle,.header-matches-btn-icon svg path{fill:none}
.header-matches-btn--filled .header-matches-btn-icon svg path{fill:currentColor}
@keyframes headerPremiumIconPulse{0%,100%{transform:scale(1);filter:brightness(1)}50%{transform:scale(1.14) translateY(-1px);filter:drop-shadow(0 0 10px rgba(251,191,36,.95)) brightness(1.35)}}
@keyframes headerSettingsIconSpin{0%{transform:rotate(0);filter:brightness(1)}25%{transform:rotate(90deg) scale(1.06);filter:drop-shadow(0 0 8px rgba(167,139,250,.85)) brightness(1.3)}50%{transform:rotate(180deg);filter:brightness(1.1)}75%{transform:rotate(270deg) scale(1.06);filter:drop-shadow(0 0 8px rgba(167,139,250,.85)) brightness(1.3)}100%{transform:rotate(360deg);filter:brightness(1)}}
@keyframes headerMatchesIconBeat{0%,100%{transform:scale(1);filter:brightness(1)}18%{transform:scale(1.22);filter:drop-shadow(0 0 9px rgba(244,63,94,.9)) brightness(1.4)}32%{transform:scale(.96)}48%{transform:scale(1.16);filter:drop-shadow(0 0 8px rgba(249,115,22,.65)) brightness(1.3)}}
@media(prefers-reduced-motion:reduce){.header-premium-btn-icon,.profile-settings-open-btn-icon,.header-matches-btn-icon{animation:none!important;filter:none!important}}
.app-sidebar-nav ul{list-style:none;padding:0;margin:0}
.app-sidebar-nav a,.app-sidebar-nav button{display:flex;align-items:center;gap:.5rem;text-decoration:none;color:inherit;font:inherit}
.sidebar-nav-icon{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:1.15rem;height:1.15rem;line-height:0;color:#7c3aed}
.sidebar-nav-icon svg{display:block;width:100%!important;height:100%!important;max-width:24px;max-height:24px}
@media(max-width:767px){.app-layout{padding-bottom:calc(5.75rem + env(safe-area-inset-bottom,0px))}.app-layout .app-sidebar{position:fixed;left:.7rem;right:.7rem;bottom:calc(.5rem + env(safe-area-inset-bottom,0px));z-index:120;width:auto;padding:.65rem .4rem .55rem;border-radius:1.45rem;border:1px solid rgba(124,58,237,.1);background:rgba(255,255,255,.98);box-shadow:0 14px 42px rgba(91,33,182,.16),0 4px 12px rgba(15,23,42,.06)}.app-layout .app-sidebar-nav ul{display:flex;align-items:flex-end;justify-content:space-around;gap:.15rem}.app-layout .app-sidebar-nav li{margin:0;flex:1 1 0;min-width:2.85rem;max-width:4.5rem;list-style:none}.app-layout .app-sidebar-nav a{flex-direction:column;align-items:center;justify-content:center;gap:.25rem;width:100%;min-height:3.15rem;padding:.4rem .15rem .35rem;font-size:.58rem;font-weight:700;text-align:center;color:#6b7aa6}.app-layout .app-sidebar-nav .sidebar-nav-icon{width:1.4rem;height:1.4rem}}
</style>
