# Pykam QA - Инструкции для AI кодирования

## Обзор проекта

**Pykam QA** — это плагин WordPress, предоставляющий лёгкую систему пользовательского типа записей Q&A (Вопросы и ответы). Позволяет редакторам создавать записи Q&A внутренне и связывать их с конкретными постами/страницами, с внешним встраиванием через теги шаблонов. Плагин **не виден публично** — записи Q&A скрыты от лент новостей, архивов и прямого доступа.

### Основная архитектура

Плагин следует паттерну **bootstrapper** для модульной регистрации:

```
Plugin (фабрика) → Bootstrappers → Domain классы
                  ├─ PostTypeBootstrapper → PostType
                  ├─ MetaBoxBootstrapper → MetaBox
                  ├─ AssetsBootstrapper → Assets
                  └─ TableColumnsBootstrapper → TableColumns
```

**Точка входа**: `pykam-qa.php` → `Plugin::make()->boot()` (зарегистрировано на хук `plugins_loaded`)

## Модель данных и отношения между постами

Записи Q&A хранятся как **кастомный тип записи** (`pykam-qa`) с метаданными:

| Ключ мета | Константа класса | Назначение |
|-----------|---|---|
| `_pykam_qa_attached_post_id` | `MetaBox::ATTACHED_POST` | Связывает Q&A с конкретным постом/страницей |
| `_pykam_qa_answer_content` | `MetaBox::ANSWER` | Тело ответа в богатом формате |
| `_pykam_qa_question_author` | `MetaBox::QUESTION_AUTHOR` | Имя автора вопроса |
| `_pykam_qa_answer_author` | `MetaBox::ANSWER_AUTHOR` | Имя автора ответа |
| `_pykam_qa_answer_date` | `MetaBox::ANSWER_DATE` | Unix временная метка публикации ответа |

**Паттерн запроса**: Используйте `WP_Query` с фильтрацией `meta_query` по `ATTACHED_POST` для получения Q&A для поста (см. `PykamQA::get_wp_query()`).

## Ключевые компоненты и паттерны

### 1. **PostType** (`src/PostType.php`)
- Регистрирует `pykam-qa` как `public=false, publicly_queryable=false`
- Хуки: `init`, `template_redirect` (скрыть отдельные), `pre_get_posts` (исключить из виджетов), отключает ленты
- Поддержка типа поста: `title`, `editor`, `author`; включен REST для Gutenberg

### 2. **MetaBox** (`src/MetaBox.php`)
- **Три метабокса**:
  - *Детали ответа*: Богатый редактор для содержимого ответа (`wp_editor()`)
  - *Отношения с постами*: AJAX диалог для выбора связанного поста
  - *Дополнительная информация*: Поля автор/дата
- **AJAX действие**: `wp_ajax_pykam_qa_get_posts` (постраничный поиск постов)
- **Проверка nonce** на всех отправках формы

### 3. **PykamQA** (`src/PykamQA.php`)
- **Класс рендеринга шаблонов** — запрашивает и отображает Q&A, привязанные к посту
- Сигнатура конструктора: `new PykamQA(int $count = 0, int $post_id = 0)`
  - `$count = 0` → все Q&A; `$count > 0` → ограничить результаты
  - `$post_id = 0` → использует текущий глобальный пост
- **Метод вывода**: `render()` отображает через `template-parts/qa-template.php`
- **Использование тега шаблона**: `<?php pykam_qa(12, 3); ?>` (3 Q&A для поста #12)

### 4. **Assets** (`src/Assets.php`)
- Условная загрузка: ресурсы админки только на `post.php`/`post-new.php` когда `post_type === 'pykam-qa'`
- **JS админки** (`assets/admin/scripts.js`): jQuery UI диалог для селектора постов, AJAX постраничность
- Локализованные строки передаются через `wp_localize_script('pykam-qa-admin', 'pykamQaAdmin', [...])`

### 5. **TableColumns** (`src/TableColumns.php`)
- Добавляет сортируемую колонку в таблицу списка админки, показывающую связь связанного поста

## Фронтенд рендеринг

**Шаблон**: `template-parts/qa-template.php`
- Выводит структурированный HTML с классом обёртки `pykam-qa`
- **Условный рендеринг**: ответ показывается только если содержимое существует И `answer_date <= current_time()`
- **Безопасность**: `wp_kses_post()` на содержимое ответа, `the_content()` санитизирует вопрос
- **CSS**: `assets/public/pykam-qa.css` (включается на всех публичных страницах через `wp_enqueue_scripts`)

## Рабочие процессы разработки

### Добавление новой функции

1. **Создайте доменный класс** в `src/` (напр., `src/NewFeature.php`) с методом `register()`
2. **Создайте bootstrapper** в `src/Bootstrapper/` реализующий `BootstrapperInterface`
3. **Зарегистрируйте в `Plugin::make()`** добавив в массив bootstrappers

**Пример**: Чтобы добавить пользовательское поле в метабокс:
- Добавьте константу в `MetaBox` для ключа мета
- Добавьте рендеринг поля в `render_main_metabox()` или новый метод метабокса
- Обработайте логику сохранения в `save_metaboxes()`

### Изменение UI админки

- Редактируйте `assets/admin/scripts.js` для поведения JavaScript
- Редактируйте `assets/admin/styles.css` для стилизации
- **Важно**: Ресурсы админки загружаются только когда `$hook === 'post.php' || 'post-new.php'` И `$post_type === 'pykam-qa'`

### Обновление шаблонов

- Редактируйте `template-parts/qa-template.php` для разметки фронтенда
- Используйте `MetaBox::CONSTANT` для ссылки на ключи мета (согласованность с классом `MetaBox`)
- Всегда санитизируйте вывод: используйте `wp_kses_post()`, `the_content()` или `esc_html()`

## Локализация и i18n

- **Текстовый домен**: `pykam-qa`
- **Место загрузки**: `pykam-qa.php` через `load_plugin_textdomain()`
- **Языковые файлы**: `languages/pykam-qa-ru_RU.*` (русский перевод включен)
- Используйте `__()` для простых строк, `_e()` для вывода, `sprintf()` для параметризованных сообщений

## Важные константы и пути

| Константа | Назначение |
|-----------|---------|
| `PYKAM_QA_PATH` | Абсолютный путь директории плагина |
| `PYKAM_QA_URL` | Базовый URL плагина |
| `PYKAM_QA_VERSION` | Версия плагина (1.0.0) |
| `PostType::POST_NAME` | Слаг кастомного типа записи (`'pykam-qa'`) |

## Проверка и верификация - контрольный список

- Убедитесь, что Q&A не появляются на фронтенде отдельных страниц или лент
- Протестируйте селектор постов AJAX с постраничностью
- Проверьте валидацию nonce метабокса
- Подтвердите, что шаблон рендерится только когда временная метка ответа <= now
- Валидируйте, что ресурсы админки загружаются только на экранах редактирования Q&A

## Паттерны кодирования

### Работа с мета-полями

Всегда используйте константы класса `MetaBox` вместо строк:

```php
// ✅ Правильно
$answer = get_post_meta($post_id, MetaBox::ANSWER, true);

// ❌ Неправильно
$answer = get_post_meta($post_id, '_pykam_qa_answer_content', true);
```

### AJAX запросы

AJAX действие должно иметь проверку nonce и заканчиваться `wp_die()`:

```php
add_action('wp_ajax_pykam_qa_get_posts', array($this, 'ajax_get_posts'));

public function ajax_get_posts() {
    check_ajax_referer('pykam_qa_get_posts_nonce', 'nonce');
    // ваша логика
    wp_die();
}
```

### Санитизация вывода

- `wp_kses_post()` — для HTML контента с ограничениями
- `the_content()` — для содержимого поста (автоматически санитизирует)
- `esc_html()` — для простого текста без HTML
