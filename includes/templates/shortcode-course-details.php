<div class="">
<?php if ( in_array('title', $course_details_array) and isset($course_details['title']) ) : ?>
    <section class="<?php print( implode(' ', $course_title_styles) ); ?>">
        <h1><?php print( $course_details['title'] ); ?></h1>
    </section>
<?php endif; ?>
<?php if ( in_array('image', $course_details_array) and isset($course_details['img']) ) : ?>
    <section class="<?php print( implode(' ', $course_image_styles) ); ?>">
        <img src="<?php print( $course_details['img']['src'] ); ?>"></h1>
    </section>
<?php endif; ?>
<?php if ( in_array('short_description', $course_details_array) and isset($course_details['short_description']) ) : ?>
    <section class="<?php print( implode(' ', $course_short_description_styles) ); ?>">
        <p><?php print( $course_details['short_description'] ); ?></p>
    </section>
<?php endif; ?>
<?php if ( in_array('start', $course_details_array) and isset($course_details['start']) ) : ?>
    <section class="<?php print( implode(' ', $course_start_styles) ); ?>">
        <p><?php print( $course_details['start'] ); ?></p>
    </section>
<?php endif; ?>
<?php if ( in_array('video', $course_details_array) and isset($course_details['video']) ) : ?>
    <iframe class="<?php print( implode(' ', $course_video_styles) ); ?>" src="<?php print( $course_details['video']['src'] ); ?>" frameborder="0" allowfullscreen=""></iframe>
<?php endif; ?>
</div>
