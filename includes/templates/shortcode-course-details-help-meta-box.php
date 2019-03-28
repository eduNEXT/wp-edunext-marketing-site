<p>Example of usage:<br>
<pre>
[course_details course_id="edX+CS105" course_details="video, title, short_description, start" course_title_styles="style1, style2" course_video_styles="my_custom_video_style"]
</pre>
<p>Shortcode attributes:</p>
<ul style="list-style-type: square;">
    <li>course_id - Required</li><p>Open edX Discovery API course id, it must include the short organization name and course number. e.g edX+DemoX</p>
    <li>course_details - Required</li><p>String or list separated by commas, with desired course details. (title, short_description, start, video)</p>
    <li>course_title_styles - Optional</li><p>String or list separated by commas, with desired class names. e.g my-awesome-style</p>
    <li>course_short_description_styles - Optional</li><p>String or list separated by commas, with desired class names. e.g my-awesome-style</p>
    <li>course_start_styles - Optional</li><p>String or list separated by commas, with desired class names. e.g my-awesome-style</p>
    <li>course_video_styles - Optional</li><p>String or list separated by commas, with desired class names. e.g my-awesome-style</p>
</ul>
