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
    justify-content: space-between;
    row-gap: 0.55rem;
}
.page-landing .site-header--landing .site-logo--brand {
    order: 1;
    width: auto;
    justify-content: flex-start;
    margin-right: auto;
}
.page-landing .site-header--landing .site-nav {
    order: 2;
    width: auto;
    flex: 1 1 auto;
    justify-content: flex-end;
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
@media (max-width: 640px) {
    .page-landing .site-header--landing .site-header-inner {
        flex-wrap: nowrap;
        gap: 0.5rem;
    }
    .page-landing .site-header--landing .site-logo--brand {
        flex: 0 0 auto;
    }
    .page-landing .site-header--landing .site-nav {
        flex: 1 1 auto;
        justify-content: flex-end;
        gap: 0.15rem 0.35rem;
        font-size: 0.8rem;
    }
    .site-logo-brand-img,
    .page-landing .site-header--landing .site-logo-brand-img {
        height: 40px;
        max-width: 42vw;
    }
}
</style>
