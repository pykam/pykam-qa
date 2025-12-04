<?php
namespace PykamQA;

use PykamQA\PostType;
use PykamQA\MetaBox;

/**
 * Extends the admin list table with relationship columns and sorting.
 */
class TableColumns {

	/**
	 * Registers hooks used to customize list table columns.
	 */
	public function register(): void {
		add_filter( 'manage_' . PostType::POST_NAME . '_posts_columns', array( $this, 'add_related_post_column' ), 4 );
		add_action( 'manage_' . PostType::POST_NAME . '_posts_custom_column', array( $this, 'fill_related_post_column' ), 5, 2 );
		add_filter( 'manage_edit-' . PostType::POST_NAME . '_sortable_columns', array( $this, 'add_related_sortable_column' ) );
		add_action( 'pre_get_posts', array( $this, 'add_column_related_post_request' ) ); // for sorting
	}

	/**
	 * Adds the related post column definition.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_related_post_column( $columns ) {
		$num = 2; // after column num
		$new_columns = array(
			'related_post' => __( 'Related Post', 'pykam-qa' ),
		);
		return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
	}

	/**
	 * Populates the related post column content.
	 *
	 * @param string $colname
	 * @param int    $post_id
	 *
	 * @return void
	 */
	function fill_related_post_column( $colname, $post_id ) {
		if ( $colname === 'related_post' ) {
			$attached_post_id = get_post_meta( $post_id, MetaBox::ATTACHED_POST, true );
			if ( $attached_post_id ) {
				$edit_link = get_edit_post_link( $attached_post_id );
				$title = get_the_title( $attached_post_id );
				printf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html( $title ) );
			} else {
				echo '&mdash;';
			}
		}
	}

	/**
	 * Marks the related post column as sortable.
	 *
	 * @param array $sortable_columns
	 *
	 * @return array
	 */
	function add_related_sortable_column( $sortable_columns ) {
		$sortable_columns['related_post'] = array( 'related_post_' . MetaBox::ATTACHED_POST, false );
		return $sortable_columns;
	}

	/**
	 * Alters the query when sorting by the related post column.
	 *
	 * @param \WP_Query $query
	 *
	 * @return void
	 */
	function add_column_related_post_request( $query ) {
		if ( ! is_admin()
			|| ! $query->is_main_query()
			|| $query->get( 'orderby' ) !== 'related_post_' . MetaBox::ATTACHED_POST
			|| get_current_screen()->id !== 'edit-' . PostType::POST_NAME
		) {
			return;
		}

		$query->set( 'meta_key', MetaBox::ATTACHED_POST );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
