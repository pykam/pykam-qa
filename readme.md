# Pykam QA

Lightweight WordPress plugin that adds an internal “Question & Answer” custom post type and lets editors embed curated Q&A blocks inside any post or page.

## Features

- Custom post type `pykam-qa` with full admin UI while remaining hidden from the public site and feeds.
- Rich metabox UI for authoring answers, linking a Q&A to any existing post/page, and storing author/date metadata.
- AJAX-powered post selector that helps editors quickly attach a Q&A to the right article.
- Front-end template (`template-parts/qa-template.php`) and public stylesheet (`assets/public/pykam-qa.css`) for clean question/answer rendering.
- Admin list column showing the related post, sortable via stored meta.

## Installation

1. Copy the `pykam-qa` folder into `wp-content/plugins/`.
2. Run `composer install` if the `vendor` directory is missing.
3. Activate “Q&A” from the WordPress Plugins screen.

## Usage

- In the dashboard, create a new “Q&A” entry, fill in the question (title + content) and answer fields, then attach it to a post via the “Post Relations” metabox.
- Insert the block in templates with `<?php pykam_qa( $post_id, $count ); ?>` (omit arguments to display all Q&A linked to the current post).
- Customize markup/styles by editing `template-parts/qa-template.php` or overriding the CSS.

## Localization

- Text domain: `pykam-qa`
- Language files are stored in `languages/`, currently shipping a `pykam-qa-ru_RU` translation.

## Development Notes

- Core classes live under `src/` (`PostType`, `MetaBox`, `Assets`, `TableColumns`, `PykamQA`).
- Public assets: `assets/public/pykam-qa.css`.
- Admin assets: `assets/admin/`.
- Composer autoloading is required; the entry point is `pykam-qa.php`.

## License

Add your preferred license statement here.