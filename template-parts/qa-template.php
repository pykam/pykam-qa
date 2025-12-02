<?php
/**
 * Template parts for dispalying Q&A
 */

if (!defined('ABSPATH')) exit;
?>

<div id="<?php the_ID() ?>" class="pykam-qa">
    <div class="pykam-qa-question-section">
        <div class="pykam-qa-header pykam-qa-question-header">
            <span class="pykam-qa-date"><?php echo sprintf('%s: %s', __('Question Date'), get_the_date()); ?></span>
            <span class="pykam-qa-username"><?php echo sprintf('%s: %s', __('Question Author'), get_post_meta( get_the_id(), '_pykam_qa_question_author', true )) ?></span>
        </div>
        <div class="pykam-qa-question">
            <?php the_content() ?>
        </div>
    </div>
    <div class="pykam-qa-answer-section">
        <div class="pykam-qa-header pykam-qa-question-header">
            <span class="pykam-qa-date"><?php echo sprintf('%s: %s', __('Answer Date'), get_post_meta( get_the_id(), '_pykam_qa_answer_date', true )); ?></span>
            <span class="pykam-qa-username"><?php echo sprintf('%s: %s', __('Answer Author'), get_post_meta( get_the_id(), '_pykam_qa_answer_author', true )) ?></span>
        </div>
        <div class="pykam-qa-answer">
            <?php echo wp_kses_post(get_post_meta(get_the_id(), '_pykam_qa_answer_content', true)) ?>
        </div>
    </div>
</div>
