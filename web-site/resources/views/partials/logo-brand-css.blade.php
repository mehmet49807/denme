<style>
.site-logo.site-logo--brand {
    gap: 0;
    align-items: center;
    flex-shrink: 0;
}
.site-logo.site-logo--brand .site-logo-mark,
.site-logo.site-logo--brand .site-logo-text {
    display: none !important;
}
.site-logo-brand-img {
    display: block;
    height: 50px;
    width: auto;
    max-width: min(240px, 58vw);
    object-fit: contain;
    filter: drop-shadow(0 6px 18px rgba(124, 58, 237, 0.14));
    transition: transform 0.18s ease, filter 0.18s ease;
}
.page-landing .site-header--landing .site-logo-brand-img {
    height: 46px;
    max-width: min(220px, 56vw);
    filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.35));
}
.page-landing .site-header--landing .site-header-inner {
    flex-wrap: wrap;
    align-items: center;
    row-gap: 0.55rem;
}
.page-landing .site-header--landing .site-logo--brand {
    order: 1;
    width: 100%;
    justify-content: center;
}
.page-landing .site-header--landing .site-nav {
    order: 2;
    width: 100%;
    justify-content: center;
}
@media (min-width: 900px) {
    .page-landing .site-header--landing .site-header-inner {
        flex-wrap: nowrap;
    }
    .page-landing .site-header--landing .site-logo--brand {
        order: 0;
        width: auto;
        justify-content: flex-start;
    }
    .page-landing .site-header--landing .site-nav {
        order: 0;
        width: auto;
        justify-content: flex-end;
    }
    .site-logo-brand-img {
        height: 54px;
        max-width: 260px;
    }
    .page-landing .site-header--landing .site-logo-brand-img {
        height: 52px;
    }
}
@media (max-width: 480px) {
    .site-logo-brand-img,
    .page-landing .site-header--landing .site-logo-brand-img {
        height: 40px;
        max-width: 52vw;
    }
}
</style>
