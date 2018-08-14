
Examples
========

Creating a new user based on the current user (using wp_get_current_user)

```php
$user = wp_get_current_user();
$new_password = wp_generate_password();

$response = WP_EoxCoreApi()->create_user([
    'username' => $user->user_login,
    'email' => $user->user_email,
    'fullname' => trim($user->user_firstname . ' ' . $user->user_lastname),
    'password' => $new_password
]);

if (is_wp_error($response)) {
	echo $response->get_error_message();
} else {
	/* no error found, response is the user created */
	echo "<h3>Edx Account created for {$response->username}! Your password is $new_password</h3>";
}

```

Enrolling the current user to a course (assuming user was already created using create_user)

```php
$user = wp_get_current_user();

$response = WP_EoxCoreApi()->create_enrollment([
    'email' => $user->user_email,
    'course_id' => 'course-v1:edX+E2E-101+course'
])

if (is_wp_error($response)) {
	echo $response->get_error_message();
} else {
	echo "<h3>Enroll success!</h3>";
}
;
```