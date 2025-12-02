<?php
namespace PykamQA;

if (!defined('ABSPATH')) exit;

/**
 * 
 */
class PykamQA
{
    protected $post_id;
    protected $count;

    public function __construct(int $count = 0, int $post_id = 0, )
    {
        $this->init($count, $post_id);
    }

    private function init(int $count, int $post_id) : void
    {
        $this->post_id = $post_id;
        $this->count = $count;

        if ($this->post_id === 0) {
            $this->post_id = $this->get_current_post_id();
        }
    }

    private function get_current_post_id(): int
    {
        global $post;
        return $post->ID;
    }

    private function get_wp_query(): \WP_Query
    {

        $args = array(
            'post_type' => 'pykam-qa',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_pykam_qa_attached_post_id',
                    'value' => $this->post_id,
                )
            ),
            'orderby'  => 'date',
	        'order'    => 'DESC',
            'no_found_rows' => 'true',
            'posts_per_page' => ( (int) $this->count === 0) ? -1 : $this->count,
        );

        $qa_list = new \WP_Query($args);

        return $qa_list;
    }

    public function print() : void {
        $qa_list = $this->get_wp_query();

        if ($qa_list->have_posts()) {
            while ( $qa_list->have_posts() ) {
                $qa_list->the_post();         
                include constant('PYKAM_QA_PATH') . '/template-parts/qa-template.php';
            }
        }

        wp_reset_postdata();
    }


}