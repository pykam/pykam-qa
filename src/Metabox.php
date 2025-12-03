<?php
namespace PykamQA;

if (!defined('ABSPATH')) exit;

/**
 * CPT metaboxes
 */
class MetaBox {

    const QUESTION_AUTHOR = '_pykam_qa_question_author';
    const ANSWER = '_pykam_qa_answer_content';
    const ANSWER_AUTHOR = '_pykam_qa_answer_author';
    const ANSWER_DATE = '_pykam_qa_answer_date';
    const ATTACHED_POST = '_pykam_qa_attached_post_id';
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'register_metaboxes'));
        add_action('save_post_pykam-qa', array($this, 'save_metaboxes'), 10, 2);
        add_action('wp_ajax_pykam_qa_get_posts', array($this, 'ajax_get_posts'));
    }
    
    public function register_metaboxes() {
        add_meta_box(
            'pykam_qa_main',
            __('Answer Details', 'pykam-qa'),
            array($this, 'render_main_metabox'),
            'pykam-qa',
            'normal',
            'high'
        );

        add_meta_box(
            'pykam_qa_relations',
            __('Post Relations', 'pykam-qa'), 
            array($this, 'render_relations_metabox'),
            'pykam-qa',
            'side',
            'default'
        );
        
        add_meta_box(
            'pykam_qa_additional',
            __('Additional Information', 'pykam-qa'),
            array($this, 'render_additional_metabox'),
            'pykam-qa',
            'side',
            'default'
        );

    }
    
    public function render_main_metabox($post) {
        wp_nonce_field(basename(__FILE__), 'pykam_qa_fields_nonce');
        
        // Get current value
        $fields = $this->get_field_values($post->ID);
        ?>
        <div class="pykam-qa-container">
            
            <!-- Rich Text Editor для ответа -->
            <div class="pykam-field-group pykam-border-green">
                <label for="answer_content">
                    <strong><?php _e('Answer:', 'pykam-qa'); ?></strong>
                </label>
                <?php
                $editor_settings = array(
                    'textarea_name' => 'pykam_qa[answer_content]',
                    'textarea_rows' => 15,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                );
                wp_editor(wp_kses_post($fields['answer_content']), 'answer_content', $editor_settings);
                ?>
            </div>
            
        </div>
        <?php
    }

    public function render_relations_metabox($post) {
        
        // Получаем прикрепленный пост
        $attached_post_id = get_post_meta($post->ID, self::ATTACHED_POST, true);
        $attached_post_title = '';
        
        if ($attached_post_id) {
            $attached_post = get_post($attached_post_id);
            if ($attached_post) {
                $attached_post_title = $attached_post->post_title;
            }
        }
        
        // Получаем типы постов для селекта
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <div class="pykam-relations-fields">
            
            <!-- Скрытое поле для ID прикрепленного поста -->
            <input type="hidden" 
                   id="pykam_qa_attached_post_id" 
                   name="pykam_qa_attached_post_id" 
                   value="<?php echo esc_attr($attached_post_id); ?>">
            
            <!-- Поле для отображения названия поста -->
            <div class="pykam-field-group">
                <label for="attached_post_display">
                    <strong><?php _e('Attached to post:', 'pykam-qa'); ?></strong>
                </label>
                <div class="attached-post-display">
                    <input type="text" 
                           id="attached_post_display" 
                           value="<?php echo esc_attr($attached_post_title); ?>"
                           class="widefat"
                           readonly
                           placeholder="<?php _e('No post selected', 'pykam-qa'); ?>">
                    
                    <div class="attached-post-actions">
                        <button type="button" 
                                class="button button-small select-post-btn" 
                                data-target="#pykam_qa_attached_post_id"
                                data-display="#attached_post_display">
                            <?php _e('Select Post', 'pykam-qa'); ?>
                        </button>
                        
                        <?php if ($attached_post_id): ?>
                        <button type="button" 
                                class="button button-small remove-post-btn" 
                                data-target="#pykam_qa_attached_post_id"
                                data-display="#attached_post_display">
                            <?php _e('Remove', 'pykam-qa'); ?>
                        </button>
                        
                        <a href="<?php echo get_edit_post_link($attached_post_id); ?>" 
                           class="button button-small" 
                           target="_blank"
                           style="margin-top: 5px; display: inline-block;">
                            <?php _e('Edit Post', 'pykam-qa'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($attached_post_id): ?>
                <div class="attached-post-info" style="margin-top: 10px; padding: 8px; background: #f0f0f1; border-radius: 3px;">
                    <p style="margin: 0 0 5px 0;">
                        <strong><?php _e('Current post:', 'pykam-qa'); ?></strong><br>
                        <a href="<?php echo get_permalink($attached_post_id); ?>" target="_blank">
                            <?php echo esc_html($attached_post_title); ?>
                        </a>
                    </p>
                    <p style="margin: 0; font-size: 12px; color: #666;">
                        ID: <?php echo esc_html($attached_post_id); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Фильтр по типу поста -->
            <div class="pykam-field-group">
                <label for="post_type_filter">
                    <strong><?php _e('Filter by post type:', 'pykam-qa'); ?></strong>
                </label>
                <select id="post_type_filter" class="widefat">

                    <?php foreach ($post_types as $post_type): ?>
                        <?php if ($post_type->name !== 'pykam-qa'): ?>
                            <option value="<?php echo esc_attr($post_type->name); ?>"<?php ($post_type->name === 'post') ?: ' selected' ?>>
                                <?php echo esc_html($post_type->labels->singular_name); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Информация -->
            <div class="pykam-info-box">
                <p style="margin: 0 0 5px 0; font-size: 12px;">
                    <strong><?php _e('Note:', 'pykam-qa'); ?></strong><br>
                    <?php _e('Link this Q&A to a specific post. Useful for creating FAQ sections for individual products/articles.', 'pykam-qa'); ?>
                </p>
            </div>
            
        </div>
        
        <!-- Модальное окно для выбора поста -->
        <div id="pykam-qa-post-selector" style="display: none;">
            <div class="pykam-modal-content">
                <div class="pykam-modal-header">
                    <h2><?php _e('Select Post', 'pykam-qa'); ?></h2>
                    <button type="button" class="pykam-modal-close">&times;</button>
                </div>
                
                <div class="pykam-modal-body">
                    <div class="post-search-box">
                        <input type="text" 
                               id="post_search_input" 
                               placeholder="<?php _e('Search posts...', 'pykam-qa'); ?>"
                               class="widefat">
                        <button type="button" id="post_search_btn" class="button">
                            <?php _e('Search', 'pykam-qa'); ?>
                        </button>
                    </div>
                    
                    <div id="posts_list_container">
                        <!-- Список постов будет загружен через AJAX -->
                        <div class="loading" style="text-align: center; padding: 20px;">
                            <?php _e('Loading posts...', 'pykam-qa'); ?>
                        </div>
                    </div>
                    
                    <div class="posts-pagination"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function render_additional_metabox($post) {

        $fields = $this->get_field_values($post->ID);

        ?>
        <div class="pykam-side-fields">

            <!-- Поле: Имя автора вопроса -->
            <div class="pykam-field-group pykam-border-green">
                <label for="question_author">
                    <strong><?php _e('Question Author:', 'pykam-qa'); ?></strong>
                </label>
                <input type="text" 
                       id="question_author" 
                       name="pykam_qa[question_author]" 
                       value="<?php echo esc_attr($fields['question_author']); ?>"
                       class="widefat"
                       placeholder="<?php _e('Enter name', 'pykam-qa'); ?>">
            </div>
            
            <!-- Поле: Дата ответа -->
            <div class="pykam-field-group">
                <label for="answer_date">
                    <strong><?php _e('Answer Date:', 'pykam-qa'); ?></strong>
                </label>
                <input type="date" 
                       id="answer_date" 
                       name="pykam_qa[answer_date]" 
                       value="<?php echo esc_attr($fields['answer_date']); ?>"
                       style="width:100%">
            </div>

            <!-- Поле: Имя ответившего -->
            <div class="pykam-field-group">
                <label for="answer_author">
                    <strong><?php _e('Answer Author:', 'pykam-qa'); ?></strong>
                </label>
                <input type="text" 
                       id="answer_author" 
                       name="pykam_qa[answer_author]" 
                       value="<?php echo esc_attr($fields['answer_author']); ?>"
                       class="widefat"
                       placeholder="<?php _e('Enter expert name', 'pykam-qa'); ?>">
            </div>
            
            <!-- Информация -->
            <div class="pykam-info-box">
                <p><strong><?php _e('Created:', 'pykam-qa'); ?></strong> 
                <?php echo get_the_date('', $post); ?></p>
                <p><strong><?php _e('Last modified:', 'pykam-qa'); ?></strong> 
                <?php echo get_the_modified_date('', $post); ?></p>
            </div>
            
        </div>
        <?php
    }
    
    private function get_field_values($post_id) {
        return array(
            'question_author' => get_post_meta($post_id, self::QUESTION_AUTHOR, true),
            'answer_content' => get_post_meta($post_id, self::ANSWER, true),
            'answer_author' => get_post_meta($post_id, self::ANSWER_AUTHOR, true) ?: wp_get_current_user()->display_name,
            'answer_date' => get_post_meta($post_id, self::ANSWER_DATE, true) ? date('Y-m-d', get_post_meta($post_id, '_pykam_qa_answer_date', true)) : current_time('Y-m-d'),
        );
    }
    
    public function save_metaboxes($post_id, $post) {

        if (!isset($_POST['pykam_qa_fields_nonce']) || 
            !wp_verify_nonce($_POST['pykam_qa_fields_nonce'], basename(__FILE__))) {
            return $post_id;
        }
        
        // Проверка автосохранения
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Проверка типа поста
        if ($post->post_type !== 'pykam-qa') {
            return $post_id;
        }
        
        // Проверка прав пользователя
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Сохранение прикрепленного поста
        if (isset($_POST['pykam_qa_attached_post_id'])) {
            $attached_post_id = intval($_POST['pykam_qa_attached_post_id']);
            
            if ($attached_post_id > 0) {
                // Проверяем, существует ли пост
                if (get_post($attached_post_id)) {
                    update_post_meta($post_id, self::ATTACHED_POST, $attached_post_id);
                    
                    // Также можно сохранить обратную связь в метаданных прикрепленного поста
                    $this->update_attached_post_meta($attached_post_id, $post_id);
                } else {
                    // Если пост не существует, очищаем поле
                    delete_post_meta($post_id, self::ATTACHED_POST);
                }
            } else {
                // Если ID равен 0, удаляем метаданные
                delete_post_meta($post_id, self::ATTACHED_POST);
                
                // Удаляем обратную связь
                $old_attached_post_id = get_post_meta($post_id, self::ATTACHED_POST, true);
                if ($old_attached_post_id) {
                    $this->remove_attached_post_meta($old_attached_post_id, $post_id);
                }
            }
        }
        
        // Обработка данных
        if (isset($_POST['pykam_qa'])) {
            $data = $_POST['pykam_qa'];
            
            // Сохраняем каждое поле
            $fields = array(
                'question_author' => 'sanitize_text_field',
                'answer_content' => 'wp_kses_post',
                'answer_author' => 'sanitize_text_field',
                'answer_date' => 'sanitize_text_field',
            );

            // $fields['answer_date'] = strtotime($fields['answer_date']);
            
            foreach ($fields as $field => $sanitize_callback) {
                if (isset($data[$field])) {
                    $value = call_user_func($sanitize_callback, $data[$field]);
                    if ($field === 'answer_date') {
                        $value = strtotime($value);
                    }
                    update_post_meta($post_id, '_pykam_qa_' . $field, $value);
                }
            }
        }
    }

    private function update_attached_post_meta($attached_post_id, $qa_post_id) {
        // Получаем текущий список прикрепленных Q&A
        $attached_qas = get_post_meta($attached_post_id, '_pykam_qa_attached_qas', true);
        
        if (!is_array($attached_qas)) {
            $attached_qas = array();
        }
        
        // Добавляем текущий Q&A, если его еще нет
        if (!in_array($qa_post_id, $attached_qas)) {
            $attached_qas[] = $qa_post_id;
            update_post_meta($attached_post_id, '_pykam_qa_attached_qas', $attached_qas);
        }
    }

    private function remove_attached_post_meta($attached_post_id, $qa_post_id) {
        // Получаем текущий список прикрепленных Q&A
        $attached_qas = get_post_meta($attached_post_id, '_pykam_qa_attached_qas', true);
        
        if (is_array($attached_qas)) {
            // Удаляем Q&A из списка
            $attached_qas = array_diff($attached_qas, array($qa_post_id));
            update_post_meta($attached_post_id, '_pykam_qa_attached_qas', $attached_qas);
        }
    }

    // AJAX обработчик для получения постов
    public function ajax_get_posts() {
        check_ajax_referer('pykam_qa_get_posts_nonce', 'nonce');
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
        
        $args = array(
            'post_type' => $post_type ? array($post_type) : 'any',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'paged' => $page,
            'orderby' => 'title',
            'order' => 'ASC',
            'post__not_in' => array(get_the_ID()), // Исключаем текущий пост
        );
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new \WP_Query($args);
        
        $posts = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_type_obj = get_post_type_object(get_post_type());
                
                $posts[] = array(
                    'ID' => get_the_ID(),
                    'post_title' => get_the_title(),
                    'post_type' => get_post_type(),
                    'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type(),
                    'post_date_formatted' => get_the_date('d.m.Y'),
                    'edit_link' => get_edit_post_link(get_the_ID(), '')
                );
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'posts' => $posts,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page,
            'total_posts' => $query->found_posts
        ));
    }

}