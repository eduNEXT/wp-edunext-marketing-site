Troubleshooting
=======

### Why can't I see the buttons to conditionally hide menu items?
**A:** Some WordPress plugins such as "Popup Maker" use their own custom code for the menus admin interface and unfortunately WordPress only allows one overwrite, so you need to [disable menu options for Popup Maker](https://docs.wppopupmaker.com/article/297-popup-maker-is-overwriting-my-menu-editor-functions-how-can-i-fix-this) or any plugin that may be interfering; you may re-enable those later without fear of losing your "hide-if" parameters.


Examples
========

Creating a new user using an array of data

```php
$user = [
    'username' => 'jhondoe',
    'email' => 'jhondoe@example.com',
    'fullname' => 'Jhon Doe',
    'password' => 'foo123456'
];

$response = WP_EoxCoreApi()->create_user($user);

if (is_wp_error($response)) {
    echo $response->get_error_message();
} else {
    /* no error found, response is the user created */
    echo "<h3>Edx Account created for {$response->username}! Your password is $new_password</h3>";
}

```
---

Creating a new user based on the current user (if your site uses wordpress users and they are logged in)

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
---

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
---

Enrolling using Woocommerce after succesful purchase, assuming the product already has an "id" attribute with the id of the course as shown in this image (separate by "|" for multiple values):

<img src="https://i.imgur.com/S2xLZfy.png" alt="id attribute on woocommerce product">

```php

function handle_payment_successful_result( $order_id ) {
    $order = wc_get_order( $order_id );
    $items = $order->get_items();
    $user = wp_get_current_user();
    $course_items_count = 0;
    foreach ( $items as $item ) {
        $product = $item->get_product();
        $is_course_item = False;
        $mode = $product->get_attribute('mode');
        if (empty($mode)) {
            $mode = 'honor';
        }
        foreach (['course_id', 'bundle_id'] as $key) {
            # code...
            $attr_course_id = $product->get_attribute($key);
            if (!$attr_course_id) {
                $attr_course_id = $product->get_attribute($key . 's');
            }
            if ($attr_course_id) {
                $is_course_item = True;
                $ids = explode('|', $attr_course_id);
                foreach ($ids as $id) {

                    $response = WP_EoxCoreApi()->create_enrollment([
                        'email' => $user->user_email,
                        $key => trim($id),
                        'mode' => $mode,
                        'force' => True
                    ]);
                    if (is_wp_error($response)) {
                        error_log($response->get_error_message());
                    }
                }
            }
        }
        if ($is_course_item) {
            $course_items_count++;
        }
    }
    if (count($items) == $course_items_count) {
        $order->update_status('completed');
    }
};

add_action( 'woocommerce_order_status_processing', 'handle_payment_successful_result', 10, 1 );
```
