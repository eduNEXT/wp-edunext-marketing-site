
Examples
========

Creating a new user based on the current user (using wp_get_current_user)

```php
$user = wp_get_current_user();
$new_password = wp_generate_password();

WP_EoxCoreApi()->create_user([
    'username' => $user->user_login,
    'email' => $user->user_email,
    'fullname' => trim($user->user_firstname . ' ' . $user->user_lastname),
    'password' => $new_password
]);

echo "<h3>Edx Account created! Your new password is $new_password</h3>";
```

Enrolling the current user to a course (assuming user was already created using create_user)

```php
$user = wp_get_current_user();

WP_EoxCoreApi()->create_enrollment([
    'email' => $user->user_email,
    'course_id' => 'course-v1:edX+E2E-101+course'
]);
```