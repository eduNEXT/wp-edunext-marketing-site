<div class="">
    <?php
        if (in_array('title', $course_details_array)) {
    ?>
            <h1><?php print($course_details['title']); ?></h1>
    <?php
        }
    ?>
    <?php
        if (in_array('short_description', $course_details_array)) {
    ?>
            <p><?php print($course_details['short_description']); ?></p>
    <?php
        }
    ?>
    <?php
        if (in_array('start', $course_details_array)) {
    ?>
            <p><?php print($course_details['start']); ?></p>
    <?php
        }
    ?>
    <?php
        if (in_array('video', $course_details_array)) {
    ?>
            <iframe width="618" height="350" src="<?php print($course_details['video']['src']); ?>" frameborder="0" allowfullscreen=""></iframe>
    <?php
        }
    ?>
</div>