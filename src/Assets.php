<?php
namespace PykamQA;

if (!defined('ABSPATH')) exit;

/**
 * Styles and Scripts
 */
class Assets
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_footer', array($this, 'add_admin_footer_scripts'));
    }

    public function enqueue_admin_styles($hook) {
        global $post_type;
        
        if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'pykam-qa') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style( 'pykam-qa-admin', constant('PYKAM_QA_URL') . '/assets/admin/styles.css' );
        }
    } 

    public function add_admin_footer_scripts() {
        global $post_type;
        
        if ($post_type === 'pykam-qa'): ?>
        <script>
        jQuery(document).ready(function($) {
            let modal = $('#pykam-qa-post-selector');
            let overlay = $('<div class="post-modal-overlay"></div>');
            let currentPage = 1;
            let searchQuery = '';
            let postTypeFilter = '';
            
            // Открытие модального окна
            $('.select-post-btn').on('click', function() {
                let targetId = $(this).data('target');
                let displayId = $(this).data('display');
                
                modal.data('target', targetId);
                modal.data('display', displayId);
                
                $('body').append(overlay);
                modal.show();
                overlay.show();
                
                loadPosts();
            });
            
            // Закрытие модального окна
            $('.pykam-modal-close').on('click', closeModal);
            overlay.on('click', closeModal);
            
            // Удаление прикрепленного поста
            $('.remove-post-btn').on('click', function() {
                let targetId = $(this).data('target');
                let displayId = $(this).data('display');
                
                $(targetId).val('');
                $(displayId).val('');
                
                // Обновляем информацию
                $('.attached-post-info').remove();
            });
            
            // Поиск постов
            $('#post_search_btn').on('click', function() {
                searchQuery = $('#post_search_input').val();
                currentPage = 1;
                loadPosts();
            });
            
            $('#post_search_input').on('keypress', function(e) {
                if (e.which === 13) {
                    searchQuery = $(this).val();
                    currentPage = 1;
                    loadPosts();
                }
            });
            
            // Фильтр по типу поста
            $('#post_type_filter').on('change', function() {
                postTypeFilter = $(this).val();
                currentPage = 1;
                loadPosts();
            });
            
            // Загрузка постов через AJAX
            function loadPosts() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pykam_qa_get_posts',
                        page: currentPage,
                        search: searchQuery,
                        post_type: postTypeFilter,
                        nonce: '<?php echo wp_create_nonce('pykam_qa_get_posts_nonce'); ?>'
                    },
                    beforeSend: function() {
                        $('#posts_list_container').html(
                            '<div class="loading" style="text-align: center; padding: 20px;">' +
                            '<?php _e('Loading posts...', 'pykam-qa'); ?>' +
                            '</div>'
                        );
                    },
                    success: function(response) {
                        if (response.success) {
                            renderPostsList(response.data);
                        } else {
                            $('#posts_list_container').html(
                                '<div class="error" style="text-align: center; padding: 20px; color: #dc3232;">' +
                                response.data.message +
                                '</div>'
                            );
                        }
                    },
                    error: function() {
                        $('#posts_list_container').html(
                            '<div class="error" style="text-align: center; padding: 20px; color: #dc3232;">' +
                            '<?php _e('Error loading posts', 'pykam-qa'); ?>' +
                            '</div>'
                        );
                    }
                });
            }
            
            // Рендеринг списка постов
            function renderPostsList(data) {
                let html = '';
                
                if (data.posts.length > 0) {
                    html += '<div class="posts-list">';
                    
                    $.each(data.posts, function(index, post) {
                        html += `
                            <div class="post-item" data-id="${post.ID}" data-title="${post.post_title}">
                                <div>
                                    <div class="post-title">${post.post_title}</div>
                                    <div style="font-size: 12px; color: #666; margin-top: 3px;">
                                        ID: ${post.ID} | 
                                        <span class="post-type">${post.post_type_label}</span> | 
                                        ${post.post_date_formatted}
                                    </div>
                                </div>
                                <div>
                                    <a href="${post.edit_link}" target="_blank" class="button button-small">
                                        <?php _e('View', 'pykam-qa'); ?>
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    // Пагинация
                    if (data.total_pages > 1) {
                        html += '<div class="posts-pagination">';
                        
                        if (currentPage > 1) {
                            html += `<button class="pagination-btn" data-page="${currentPage - 1}">
                                        <?php _e('Previous', 'pykam-qa'); ?>
                                     </button>`;
                        }
                        
                        html += `<span style="padding: 5px 10px;">
                                    ${currentPage} / ${data.total_pages}
                                 </span>`;
                        
                        if (currentPage < data.total_pages) {
                            html += `<button class="pagination-btn" data-page="${currentPage + 1}">
                                        <?php _e('Next', 'pykam-qa'); ?>
                                     </button>`;
                        }
                        
                        html += '</div>';
                    }
                } else {
                    html += '<div style="text-align: center; padding: 30px; color: #666;">' +
                           '<?php _e('No posts found', 'pykam-qa'); ?>' +
                           '</div>';
                }
                
                $('#posts_list_container').html(html);
                
                // Обработчики для выбора поста
                $('.post-item').on('click', function() {
                    $('.post-item').removeClass('selected');
                    $(this).addClass('selected');
                    
                    let postId = $(this).data('id');
                    let postTitle = $(this).data('title');
                    let targetId = modal.data('target');
                    let displayId = modal.data('display');
                    
                    $(targetId).val(postId);
                    $(displayId).val(postTitle);
                    
                    closeModal();
                    
                    // Показать уведомление
                    alert('<?php _e('Post selected successfully!', 'pykam-qa'); ?>');
                });
                
                // Обработчики для пагинации
                $('.pagination-btn').on('click', function() {
                    currentPage = $(this).data('page');
                    loadPosts();
                });
            }
            
            function closeModal() {
                modal.hide();
                overlay.remove();
            }
        });
        </script>
        <?php endif;
    }

}