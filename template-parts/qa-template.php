<?php
/**
 * Template parts for dispalying Q&A
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PykamQA\MetaBox;
use PykamQA\EditLink;

$question_author = get_post_meta( get_the_id(), MetaBox::QUESTION_AUTHOR, true );
$answer_author = get_post_meta( get_the_id(), MetaBox::ANSWER_AUTHOR, true );
$answer_date = get_post_meta( get_the_id(), MetaBox::ANSWER_DATE, true );
$answer_content = get_post_meta( get_the_id(), MetaBox::ANSWER, true );

?>

<div id="<?php the_ID(); ?>" class="pykam-qa">
	<div class="pykam-qa-question-section">
		<div class="pykam-qa-header pykam-qa-question-header">
			<span class="pykam-qa-date">
				<?php printf( '%s: %s', __( 'Question Date', 'pykam-qa' ), get_the_date() ); ?>
			</span>
			<span class="pykam-qa-username">
				<?php printf( '%s: %s', __( 'Question Author', 'pykam-qa' ), $question_author ); ?>
			</span>
		</div>
		<div class="pykam-qa-question">
			<?php the_content(); ?>
		</div>
	</div>
	<?php if ( trim( $answer_content ) !== '' && ( $answer_date <= time() ) ) : ?>
		<div class="pykam-qa-answer-section">
			<div class="pykam-qa-header pykam-qa-question-header">
				<span class="pykam-qa-date">
					<?php printf( '%s: %s, %s', __( 'Respond', 'pykam-qa' ), $answer_author, date( 'd.m.Y', $answer_date ) ); ?>
				</span>
			</div>
			<div class="pykam-qa-answer">
				<?php echo wp_kses_post( $answer_content ); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php echo EditLink::getEditLink(get_the_id()) ?>
</div>
