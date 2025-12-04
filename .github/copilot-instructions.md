# Pykam QA - AI Coding Agent Instructions

## Project Overview

**Pykam QA** is a WordPress plugin providing a lightweight Q&A custom post type system. It allows editors to create Q&A entries internally and link them to specific posts/pages, with front-end embedding via template tags. The plugin is **not publicly visible** — Q&A posts are hidden from feeds, archives, and direct access.

### Core Architecture

The plugin follows a **bootstrapper pattern** for modular registration:

```
Plugin (factory) → Bootstrappers → Domain Classes
                  ├─ PostTypeBootstrapper → PostType
                  ├─ MetaBoxBootstrapper → MetaBox
                  ├─ AssetsBootstrapper → Assets
                  └─ TableColumnsBootstrapper → TableColumns
```

**Key Entry Point**: `pykam-qa.php` → `Plugin::make()->boot()` (registered on `plugins_loaded` hook)

## Data Model & Post Relationships

Q&A entries are stored as a **custom post type** (`pykam-qa`) with metadata:

| Meta Key | Class Constant | Purpose |
|----------|---|---|
| `_pykam_qa_attached_post_id` | `MetaBox::ATTACHED_POST` | Links Q&A to a specific post/page |
| `_pykam_qa_answer_content` | `MetaBox::ANSWER` | Rich text answer body |
| `_pykam_qa_question_author` | `MetaBox::QUESTION_AUTHOR` | Question author name |
| `_pykam_qa_answer_author` | `MetaBox::ANSWER_AUTHOR` | Answer author name |
| `_pykam_qa_answer_date` | `MetaBox::ANSWER_DATE` | Unix timestamp for answer publication |

**Query Pattern**: Use `WP_Query` with `meta_query` filtering on `ATTACHED_POST` to retrieve Q&As for a post (see `PykamQA::get_wp_query()`).

## Key Components & Patterns

### 1. **PostType** (`src/PostType.php`)
- Registers `pykam-qa` as `public=false, publicly_queryable=false`
- Hooks: `init`, `template_redirect` (hide singles), `pre_get_posts` (exclude widgets), disables feeds
- Post type supports: `title`, `editor`, `author`; REST-enabled for Gutenberg

### 2. **MetaBox** (`src/MetaBox.php`)
- **Three metaboxes**:
  - *Answer Details*: Rich editor for answer content (`wp_editor()`)
  - *Post Relations*: AJAX dialog for selecting the linked post
  - *Additional Information*: Author/date fields
- **AJAX action**: `wp_ajax_pykam_qa_get_posts` (paginated post search)
- **Nonce validation** on all form submissions

### 3. **PykamQA** (`src/PykamQA.php`)
- **Template rendering class** — queries and displays Q&As attached to a post
- Constructor signature: `new PykamQA(int $count = 0, int $post_id = 0)`
  - `$count = 0` → all Q&As; `$count > 0` → limit results
  - `$post_id = 0` → uses current global post
- **Output method**: `render()` displays via `template-parts/qa-template.php`
- **Template tag usage**: `<?php pykam_qa(12, 3); ?>` (3 Q&As for post #12)

### 4. **Assets** (`src/Assets.php`)
- Conditional loading: admin assets only on `post.php`/`post-new.php` when `post_type === 'pykam-qa'`
- **Admin JS** (`assets/admin/scripts.js`): jQuery UI dialog for post selector, AJAX pagination
- Localized strings passed via `wp_localize_script('pykam-qa-admin', 'pykamQaAdmin', [...])`

### 5. **TableColumns** (`src/TableColumns.php`)
- Adds sortable column to admin list table showing linked post relationship

## Frontend Rendering

**Template**: `template-parts/qa-template.php`
- Outputs structured HTML with `pykam-qa` wrapper class
- **Conditional rendering**: answer shown only if content exists AND `answer_date <= current_time()`
- **Security**: `wp_kses_post()` on answer content, `the_content()` sanitizes question
- **CSS**: `assets/public/pykam-qa.css` (included on all public pages via `wp_enqueue_scripts`)

## Development Workflows

### Adding a New Feature

1. **Create a domain class** in `src/` (e.g., `src/NewFeature.php`) with a `register()` method
2. **Create a bootstrapper** in `src/Bootstrapper/` implementing `BootstrapperInterface`
3. **Register in `Plugin::make()`** by adding to the bootstrappers array

**Example**: To add a custom field to the metabox:
- Add constant in `MetaBox` for the meta key
- Add field rendering in `render_main_metabox()` or new metabox method
- Handle save logic in `save_metaboxes()`

### Modifying Admin UI

- Edit `assets/admin/scripts.js` for JavaScript behavior
- Edit `assets/admin/styles.css` for styling
- **Important**: Admin assets only load when `$hook === 'post.php' || 'post-new.php'` AND `$post_type === 'pykam-qa'`

### Updating Templates

- Edit `template-parts/qa-template.php` for front-end markup
- Use `MetaBox::CONSTANT` to reference meta keys (consistency with `MetaBox` class)
- Always sanitize output: use `wp_kses_post()`, `the_content()`, or `esc_html()`

## Localization & i18n

- **Text domain**: `pykam-qa`
- **Load location**: `pykam-qa.php` via `load_plugin_textdomain()`
- **Language files**: `languages/pykam-qa-ru_RU.*` (Russian translation included)
- Use `__()` for simple strings, `_e()` for echo, `sprintf()` for parameterized messages

## Important Constants & Paths

| Constant | Purpose |
|----------|---------|
| `PYKAM_QA_PATH` | Absolute plugin directory path |
| `PYKAM_QA_URL` | Plugin URL base |
| `PYKAM_QA_VERSION` | Plugin version (1.0.0) |
| `PostType::POST_NAME` | Custom post type slug (`'pykam-qa'`) |

## Testing & Verification Checklist

- Verify Q&As don't appear on front-end single pages or feeds
- Test AJAX post selector works with pagination
- Check metabox nonce validation
- Confirm template renders only when answer timestamp <= now
- Validate admin assets load only on Q&A edit screens
