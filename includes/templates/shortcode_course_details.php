<div class="">
    <?php
        if (in_array('title', $course_details_array)) {
    ?>
            <section class="<?php print(implode(' ', $course_title_styles)); ?>">
                <h1><?php print($course_details['title']); ?></h1>
            </section>
    <?php
        }
    ?>
    <?php
        if (in_array('short_description', $course_details_array)) {
    ?>
            <section class="<?php print(implode(' ', $course_short_description_styles)); ?>">
                <p><?php print($course_details['short_description']); ?></p>
            </section>
    <?php
        }
    ?>
    <?php
        if (in_array('start', $course_details_array)) {
    ?>
            <section class="<?php print(implode(' ', $course_start_styles)); ?>">
                <p><?php print($course_details['start']); ?></p>
            </section>
    <?php
        }
    ?>
    <?php
        if (in_array('video', $course_details_array)) {
    ?>
            <iframe class="<?php print(implode(' ', $course_video_styles)); ?>" src="<?php print($course_details['video']['src']); ?>" frameborder="0" allowfullscreen=""></iframe>
    <?php
        }
    ?>
</div>