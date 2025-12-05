<?php
/**
 * MetaBox class for handling Q&A metaboxes and AJAX operations.
 *
 * @package PykamQA
 */

namespace PykamQA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Handles metabox rendering, saving, and AJAX helpers for Q&A posts.
 */
class MetaBox {

	const QUESTION_AUTHOR = '_pykam_qa_question_author';
	const ANSWER = '_pykam_qa_answer_content';
	const ANSWER_AUTHOR = '_pykam_qa_answer_author';
	const ANSWER_DATE = '_pykam_qa_answer_date';
	const ATTACHED_POST = '_pykam_qa_attached_post_id';

	/**
	 * Registers WordPress hooks for metabox management and AJAX actions.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
		add_action( 'save_post_pykam-qa', array( $this, 'save_metaboxes' ), 10, 2 );
		add_action( 'wp_ajax_pykam_qa_get_posts', array( $this, 'ajax_get_posts' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Registers all metaboxes used by the custom post type.
	 *
	 * @return void
	 */
	public function register_metaboxes() {
		add_meta_box(
			'pykam_qa_main',
			__( 'Answer Details', 'pykam-qa' ),
			array( $this, 'render_main_metabox' ),
			'pykam-qa',
			'normal',
			'high'
		);

		add_meta_box(
			'pykam_qa_relations',
			__( 'Post Relations', 'pykam-qa' ),
			array( $this, 'render_relations_metabox' ),
			'pykam-qa',
			'side',
			'default'
		);

		add_meta_box(
			'pykam_qa_additional',
			__( 'Additional Information', 'pykam-qa' ),
			array( $this, 'render_additional_metabox' ),
			'pykam-qa',
			'side',
			'default'
		);
	}

	/**
	 * Renders the main metabox containing the rich text editor for the answer.
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function render_main_metabox( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'pykam_qa_fields_nonce' );

        // Get current value
        $fields = $this->get_field_values($post->ID);
		?>
		<div class="pykam-qa-container">
			
			<!-- Rich Text Editor for the answer -->
		<div class="pykam-field-group pykam-border-green">
			<label for="answer_content">
				<strong><?php esc_html_e( 'Answer:', 'pykam-qa' ); ?></strong>
			</label>
				<?php
				$editor_settings = array(
					'textarea_name' => 'pykam_qa[answer_content]',
					'textarea_rows' => 15,
					'media_buttons' => true,
					'teeny' => false,
					'quicktags' => true,
				);
				wp_editor( wp_kses_post( $fields['answer_content'] ), 'answer_content', $editor_settings );
				?>
			</div>
			
		</div>
		<?php
	}

	/**
	 * Renders the metabox that links a Q&A entry to another post.
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
    public function render_relations_metabox($post) {
        
        // Retrieve the currently attached post
        $attached_post_id = get_post_meta($post->ID, self::ATTACHED_POST, true);
        $attached_post_title = '';
        
        if ($attached_post_id) {
            $attached_post = get_post($attached_post_id);
            if ($attached_post) {
                $attached_post_title = $attached_post->post_title;
            }
        }
        
        // Fetch public post types for the dropdown
        $post_types = get_post_types(array('public' => true), 'objects');
		?>
		<div class="pykam-relations-fields">
			
			<!-- Hidden field for the attached post ID -->
			<input type="hidden" 
				   id="pykam_qa_attached_post_id" 
				   name="pykam_qa_attached_post_id" 
				   value="<?php echo esc_attr( $attached_post_id ); ?>">
			
			<!-- Field that displays the attached post title and allows inline search -->
		<div class="pykam-field-group">
			<label for="attached_post_display">
				<strong><?php esc_html_e( 'Attached to post:', 'pykam-qa' ); ?></strong>
			</label>
				<div class="attached-post-display">
					<input type="text" 
						   id="attached_post_display" 
						   value="<?php echo esc_attr( $attached_post_title ); ?>"
						   class="widefat pykam-qa-post-search"
						   placeholder="<?php esc_attr_e( 'Search posts...', 'pykam-qa' ); ?>">
						<div id="pykam_qa_suggestions" class="pykam-qa-suggestions" style="display:none;"></div>
					
					<div class="attached-post-actions">
						<?php if ( $attached_post_id ) : ?>
						<button type="button" 
								class="button button-small remove-post-btn" 
								data-target="#pykam_qa_attached_post_id"
						data-display="#attached_post_display">
						<?php esc_html_e( 'Remove', 'pykam-qa' ); ?>
					</button>
						<a href="<?php echo esc_url( get_edit_post_link( $attached_post_id ) ); ?>" 
						   class="button button-small pykam-qa-edit-selected-post" 
						   target="_blank"
						   style="margin-top: 5px; display: inline-block;">
							<?php _e( 'Edit Post', 'pykam-qa' ); ?>
						</a>
						<?php else : ?>
						<a href="#" class="button button-small pykam-qa-edit-selected-post" style="display:none; margin-top:5px;">&nbsp;</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			
			<!-- Post type filter -->
			<div class="pykam-field-group">
				<label for="post_type_filter">
					<strong><?php esc_html_e( 'Filter by post type:', 'pykam-qa' ); ?></strong>
				</label>
				<select id="post_type_filter" class="widefat">

					<?php foreach ( $post_types as $post_type ) : ?>
						<?php if ( $post_type->name !== 'pykam-qa' ) : ?>
							<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $post_type->name, 'post' ); ?> >
								<?php echo esc_html( $post_type->labels->singular_name ); ?>
							</option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		

		<?php
	}

	/**
	 * Renders the sidebar metabox with additional metadata fields.
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function render_additional_metabox( $post ) {

		$fields = $this->get_field_values( $post->ID );

		?>
		<div class="pykam-side-fields">

			<!-- Field: Question author name -->
			<div class="pykam-field-group pykam-border-green">
				<label for="question_author">
					<strong><?php esc_html_e( 'Question Author:', 'pykam-qa' ); ?></strong>
				</label>
				<input type="text" 
					   id="question_author" 
					   name="pykam_qa[question_author]" 
					   value="<?php echo esc_attr( $fields['question_author'] ); ?>"
					   class="widefat"
					   placeholder="<?php _e( 'Enter name', 'pykam-qa' ); ?>">
			</div>
			
			<!-- Field: Answer date -->
			<div class="pykam-field-group">
				<label for="answer_date">
					<strong><?php esc_html_e( 'Answer Date:', 'pykam-qa' ); ?></strong>
				</label>
				<input type="date" 
					   id="answer_date" 
					   name="pykam_qa[answer_date]" 
					   value="<?php echo esc_attr( $fields['answer_date'] ); ?>"
					   style="width:100%">
			</div>

			<!-- Field: Answer author name -->
			<div class="pykam-field-group">
				<label for="answer_author">
					<strong><?php esc_html_e( 'Answer Author:', 'pykam-qa' ); ?></strong>
				</label>
				<input type="text" 
					   id="answer_author" 
					   name="pykam_qa[answer_author]" 
					   value="<?php echo esc_attr( $fields['answer_author'] ); ?>"
					   class="widefat"
					   placeholder="<?php _e( 'Enter expert name', 'pykam-qa' ); ?>">
			</div>
			
			<!-- Information box -->
			<div class="pykam-info-box">
				<p><strong><?php _e( 'Created:', 'pykam-qa' ); ?></strong> 
				<?php echo get_the_date( '', $post ); ?></p>
				<p><strong><?php _e( 'Last modified:', 'pykam-qa' ); ?></strong> 
				<?php echo get_the_modified_date( '', $post ); ?></p>
			</div>
			
		</div>
		<?php
	}

	/**
	 * Retrieves and normalizes all saved meta values for the provided post.
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	private function get_field_values( $post_id ) {
		return array(
			'question_author' => get_post_meta( $post_id, self::QUESTION_AUTHOR, true ),
			'answer_content' => get_post_meta( $post_id, self::ANSWER, true ),
			'answer_author' => get_post_meta( $post_id, self::ANSWER_AUTHOR, true ) ?: wp_get_current_user()->display_name,
			'answer_date' => get_post_meta( $post_id, self::ANSWER_DATE, true ) ? date( 'Y-m-d', get_post_meta( $post_id, '_pykam_qa_answer_date', true ) ) : current_time( 'Y-m-d' ),
		);
	}

	/**
	 * Validates and saves metabox data whenever the custom post type is saved.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 *
	 * @return void|int
	 */
    public function save_metaboxes($post_id, $post) {

        if (!isset($_POST['pykam_qa_fields_nonce']) || 
            !wp_verify_nonce($_POST['pykam_qa_fields_nonce'], basename(__FILE__))) {
            return $post_id;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ($post->post_type !== 'pykam-qa') {
            return $post_id;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        // Save attached post ID
        if (isset($_POST['pykam_qa_attached_post_id'])) {
            $attached_post_id = intval($_POST['pykam_qa_attached_post_id']);
            
            if ($attached_post_id > 0) {
                // Confirm the post exists
                if (get_post($attached_post_id)) {
                    update_post_meta($post_id, self::ATTACHED_POST, $attached_post_id);
                    
                    // Store reverse relation on the attached post
                    $this->update_attached_post_meta($attached_post_id, $post_id);
                } else {
                    // Remove value if the post does not exist
                    delete_post_meta($post_id, self::ATTACHED_POST);
                }
            } else {
                // Remove metadata when nothing is attached
                delete_post_meta($post_id, self::ATTACHED_POST);
                
                // Remove reverse relation
                $old_attached_post_id = get_post_meta($post_id, self::ATTACHED_POST, true);
                if ($old_attached_post_id) {
                    $this->remove_attached_post_meta($old_attached_post_id, $post_id);
                }
            }
        }
        
        // Save the remaining fields
        if (isset($_POST['pykam_qa'])) {
            $data = $_POST['pykam_qa'];
            
            // Sanitize each field independently
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

	/**
	 * Adds the current Q&A post to the attached post meta list.
	 *
	 * @param int $attached_post_id
	 * @param int $qa_post_id
	 *
	 * @return void
	 */
	private function update_attached_post_meta( $attached_post_id, $qa_post_id ) {
		
		// Retrieve current list of attached Q&A posts
        $attached_qas = get_post_meta($attached_post_id, '_pykam_qa_attached_qas', true);
		if ( ! is_array( $attached_qas ) ) {
			$attached_qas = array();
		}

		// .
		if ( ! in_array( $qa_post_id, $attached_qas ) ) {
			$attached_qas[] = $qa_post_id;
			update_post_meta( $attached_post_id, '_pykam_qa_attached_qas', $attached_qas );
		}
	}

	/**
	 * Removes the current Q&A post from the attached post meta list.
	 *
	 * @param int $attached_post_id
	 * @param int $qa_post_id
	 *
	 * @return void
	 */
    private function remove_attached_post_meta($attached_post_id, $qa_post_id) {
        // Retrieve current list of attached Q&A posts
        $attached_qas = get_post_meta($attached_post_id, '_pykam_qa_attached_qas', true);
        
        if (is_array($attached_qas)) {
            // Remove the Q&A from the list
            $attached_qas = array_diff($attached_qas, array($qa_post_id));
            update_post_meta($attached_post_id, '_pykam_qa_attached_qas', $attached_qas);
        }
	}

	/**
	* AJAX handler that returns posts for the selector modal .
	*
	* @return void
	*/
	public function ajax_get_posts() {

		check_ajax_referer( 'pykam_qa_get_posts_nonce', 'nonce' );

		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

		$args = array(
			'post_type' => $post_type ? array( $post_type ) : 'any',
			'post_status' => 'publish',
			'posts_per_page' => 10,
			'paged' => $page,
			'orderby' => 'title',
			'order' => 'ASC',
			'post__not_in' => array( get_the_ID() ), // .
			);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
			}

		$query = new \WP_Query( $args );

		$posts = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_type_obj = get_post_type_object( get_post_type() );

				$posts[] = array(
					'ID' => get_the_ID(),
					'post_title' => get_the_title(),
					'post_type' => get_post_type(),
					'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type(),
					'post_date_formatted' => get_the_date( 'd.m.Y' ),
					'edit_link' => get_edit_post_link( get_the_ID(), '' ),
				);
			}
		}

		wp_reset_postdata();

		wp_send_json_success(
			array(
				'posts' => $posts,
				'total_pages' => $query->max_num_pages,
				'current_page' => $page,
				'total_posts' => $query->found_posts,
			)
		);
	}

	/**
	 * Registers REST API routes for admin post search (used by inline autocomplete).
	 */
	public function register_rest_routes() {
		register_rest_route(
			'pykam-qa/v1',
			'/posts',
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'rest_get_posts' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * REST handler returning posts for the admin inline selector.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function rest_get_posts( \WP_REST_Request $request ) {
		$search = sanitize_text_field( $request->get_param( 'search' ) );
		$post_type = sanitize_text_field( $request->get_param( 'post_type' ) );
		$page = intval( $request->get_param( 'page' ) ) ?: 1;
		$per_page = intval( $request->get_param( 'per_page' ) ) ?: 10;

		$args = array(
			'post_type' => $post_type ? array( $post_type ) : 'any',
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $page,
			'orderby' => 'title',
			'order' => 'ASC',
			'post__not_in' => array( get_the_ID() ),
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );

		$posts = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_type_obj = get_post_type_object( get_post_type() );

				$posts[] = array(
					'ID' => get_the_ID(),
					'post_title' => get_the_title(),
					'post_type' => get_post_type(),
					'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type(),
					'post_date_formatted' => get_the_date( 'd.m.Y' ),
					'edit_link' => get_edit_post_link( get_the_ID(), '' ),
				);
			}
		}

		wp_reset_postdata();

		return rest_ensure_response( array(
			'posts' => $posts,
			'total_pages' => $query->max_num_pages,
			'current_page' => $page,
			'total_posts' => $query->found_posts,
		) );
	}
}

