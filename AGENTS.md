# AGENTS.md

## Cursor Cloud specific instructions

### Product overview

This repository is the **Brushed** responsive one-page portfolio template (static HTML/CSS/JS with a small PHP contact handler). There is no package manager, build step, test suite, or linter.

### One-time / startup layout

HTML references assets under `_include/`, but the repo stores CSS/JS at the repository root and does not ship image files. Run:

```bash
bash scripts/setup-dev.sh
```

This creates `_include/` symlinks to root assets, copies `contact.php` into `_include/php/`, and generates placeholder JPEGs for missing images.

### Running the dev server

PHP is required for the contact form (`_include/php/contact.php`). Start from the repo root:

```bash
php -S localhost:8080 -t /workspace
```

Then open:

- http://localhost:8080/index.html — main site
- http://localhost:8080/shortcodes.html — component showcase

`python3 -m http.server 8080` works for static preview only; the contact form will not function without PHP.

### Lint / test

No automated lint or test commands are defined in this repository.

### Known limitations

- Image assets in the original template are not in the repo; placeholders are used after `setup-dev.sh`.
- The Twitter ticker expects a PHP proxy at `_include/js/twitter/` (not included); the widget may fail silently.
- Contact form email delivery depends on the host's `mail()` configuration; local dev usually validates submission via JSON response only.
