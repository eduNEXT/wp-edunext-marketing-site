<p>As part of the Wordpress Open edX integrator, you can use a shortcode (edunext_enroll_button) to add a Course button to your pages or posts. This button will be linked to one of your Open edX courses and it will link your post or page will the course as required, according to the Open edX user session and the course settings.</p>

<h4>Basic Usage:</h4>
<p>In the most simple configuration, the button will behave and look as specified in the Open edX integrator plugin settings.</p>
	<p><pre>[edunext_enroll_button course_id="course-v1:organization+coursenumber+courserun"]</pre></p>

<h4>Overriding properties for the button:</h4>
	<p>To specify a custom class for the container, button or color you may use the attributes 
		<strong>button_class_generic</strong>, <strong>container_class_generic</strong>, <strong>color_class_generic</strong>
	</p> 
	<p>There are 5 alternative buttons. One of them will appear depending on the learner state and course settings:</p>
	<ul>
		<li><strong>• enroll </strong><i>the learner is not enrolled and the course is open for enrollments</i></li>
		<li><strong>• go_to_course </strong><i>the learner is enrolled and can access the course contents</i></li>
		<li><strong>• course_has_not_started </strong><i>the learner is enrolled but the course hasn't started</i></li>
		<li><strong>• invitation_only </strong><i>the learner is not enrolled and the course is by invitation only</i></li>
		<li><strong>• enrollment_closed </strong><i>the learner is not enrolled and the course is closed for enrollments</i></li>
	</ul>
	<p>There are some settings that can be customized for each button type, including the label, and some CSS classes to be used with the markup. This customizations can be done for all the Course buttons across the site from the Open edX integrator plugin settings page.</p>

	<p>Additionally, the properties can be overwriten for a single button, by adding the desired property and value inside the shortcode. for example you want to customize how this particular button looks when in the case of "invitation_only" you may use this shortcode: </p>
	<p><pre>[edunext_enroll_button course_id="course-v1:organization+coursenumber+courserun"
label_<strong>invitation_only</strong>="Sorry, invitation only!"
button_class_<strong>invitation_only</strong>="my-custom-button"
container_class_<strong>invitation_only</strong>="my-custom-container"
color_class_<strong>invitation_only</strong>="my-custom-color"]</pre></p>

	<p>You only need to include the properties you want to override in each button.  The list of possible variables you can override is:</p>
	<ul>
		<li>button_class_generic</li>
		<li>container_class_generic</li>
		<li>color_class_generic</li>

		<li>label_enroll</li>
		<li>button_class_enroll</li>
		<li>container_class_enroll</li>
		<li>color_class_enroll</li>

		<li>label_go_to_course</li>
		<li>button_class_go_to_course</li>
		<li>container_class_go_to_course</li>
		<li>color_class_go_to_course</li>

		<li>label_course_has_not_started</li>
		<li>button_class_course_has_not_started</li>
		<li>container_class_course_has_not_started</li>
		<li>color_class_course_has_not_started</li>

		<li>label_invitation_only</li>
		<li>button_class_invitation_only</li>
		<li>container_class_invitation_only</li>
		<li>color_class_invitation_only</li>

		<li>label_enrollment_closed</li>
		<li>button_class_enrollment_closed</li>
		<li>container_class_enrollment_closed</li>
		<li>color_class_enrollment_closed</li>
		
		<li>hide_if</li>
	</ul>
<h4>Advanced usage:</h4>
		<p>You may also use the attribute <strong>hide_if="not logged in"</strong> if you want to hide the button when the user is NOT logged in. Inversely you may use the attribute <strong>hide_if="logged in"</strong> if you want to hide the button when the user is logged in</p>
		<p><pre>[edunext_enroll_button course_id="course-v1:organization+coursenumber+courserun" hide_if="logged in"]</pre></p>		
		<script>
			jQuery(function ($) {
				var $metabox = $('#exo-shortcode-help');
				var $wpcontent = $("#wp-content-wrap");
				var $textarea = $('#html_text_area_id');

				$metabox.addClass('closed');
				var interval = setInterval(function () {
					var content;
					if ($wpcontent.hasClass("tmce-active")){
					    content = tinyMCE.activeEditor.getContent();
					} else {
					    content = $textarea.val() || '';
					}
					if (content.indexOf('[edun') !== -1) {
						if ($('.shine').length === 0) {
							$metabox.removeClass('closed').addClass('shine');
						}
					}
				}, 2000);
				setTimeout(function () {
					$('#exo-shortcode-help .ui-sortable-handle').click(function () {
						$('.shine').removeClass('shine');
						clearInterval(interval);
					});
				}, 1000);
			})
		</script>