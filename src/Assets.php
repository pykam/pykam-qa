<?php
namespace PykamQA;

if (!defined('ABSPATH')) exit;

/**
 * Registers and renders all admin/public assets required by the plugin.
 */
class Assets
{
    /**
     * Wires WordPress hooks responsible for asset loading.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_footer', array($this, 'add_admin_footer_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_styles'));
    }

    /**
     * Loads admin styles/scripts on Q&A edit screens.
     *
     * @param string $hook
     *
     * @return void
     */
    public function enqueue_admin_styles($hook) {
        global $post_type;
        
        if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'pykam-qa') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style( 'pykam-qa-admin', constant('PYKAM_QA_URL') . '/assets/admin/styles.css' );
        }
    } 

    /**
     * Enqueues front-end styles for the Q&A block.
     *
     * @return void
     */
    public function enqueue_public_styles() {
        wp_enqueue_style('pykam-qa-styles', constant('PYKAM_QA_URL') . '/assets/public/pykam-qa.css');
    }

    /**
     * Outputs inline admin scripts used for the post selector modal.
     *
     * @return void
     */
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
            
            // Open the modal window
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
            
            // Close the modal window
            $('.pykam-modal-close').on('click', closeModal);
            overlay.on('click', closeModal);
            
            // Remove the attached post
            $('.remove-post-btn').on('click', function() {
                let targetId = $(this).data('target');
                let displayId = $(this).data('display');
                
                $(targetId).val('');
                $(displayId).val('');
                
                // Remove the info box
                $('.attached-post-info').remove();
            });
            
            // Search posts
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
            
            // Filter by post type
            $('#post_type_filter').on('change', function() {
                postTypeFilter = $(this).val();
                currentPage = 1;
                loadPosts();
            });
            
            // Load posts via AJAX
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
            
            // Render the posts list
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
                    
                    // Pagination UI
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
                
                // Handlers for selecting a post
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
                    
                    // Show confirmation message
                    alert('<?php _e('Post selected successfully!', 'pykam-qa'); ?>');
                });
                
                // Pagination buttons
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