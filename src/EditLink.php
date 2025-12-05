<?php
/**
 * Creates a link to edit Q&A
 */

namespace PykamQA;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Utility class for generating secure and translatable edit links for posts in WordPress.
 *
 * This class provides a static method to generate an HTML link to edit a post,
 * ensuring the current user has the necessary capabilities and all output is properly
 * escaped to prevent security vulnerabilities (e.g., XSS).
 *
 * The generated link is filterable and supports custom HTML attributes,
 * making it flexible for use in themes, plugins, or custom templates.
 *
 * Example usage:
 * <code>
 * echo EditLink::getEditLink( $post_id );
 * </code>
 *
 * @package PykamQA
 * @since   1.0.0
 */
class EditLink {
    
    /**
     * Get the edit post link with proper escaping and translation.
     *
     * @param int $post_id The ID of the post.
     * @param array $attributes Optional HTML attributes for the link.
     * @return string HTML link or empty string if user cannot edit.
     */
    public static function getEditLink( $post_id, $attributes = [] ): string {
  
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return '';
        }

        $edit_url = get_edit_post_link( $post_id );
        if ( ! $edit_url ) {
            return '';
        }

        $class = 'pykam-qa-edit-link';
        if ( isset( $attributes['class'] ) ) {
            $class .= ' ' . esc_attr( $attributes['class'] );
        }

        $html = sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_url( $edit_url ),
            esc_attr( $class ),
            esc_html( __( 'Edit', 'pykam-qa' ) )
        );

        //Allow filtering the final output
        return apply_filters( 'pykam_qa_edit_link_html', $html, $post_id, $attributes );
    }
}
