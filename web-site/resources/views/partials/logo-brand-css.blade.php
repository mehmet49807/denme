<style>
.site-logo.site-logo--brand {
    gap: 0;
    align-items: center;
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
.site-logo.site-logo--brand:hover .site-logo-brand-img {
    transform: translateY(-1px);
    filter: drop-shadow(0 10px 22px rgba(236, 72, 153, 0.2));
}
.page-landing .site-header--landing .site-logo-brand-img {
    height: 46px;
    max-width: min(220px, 56vw);
}
@media (min-width: 900px) {
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
