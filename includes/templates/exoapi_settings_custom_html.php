<style>
.exoapi-add-new-users {
    display: inline-block;
    background: white;
    padding: 0 20px 15px 20px;
    border: 1px solid #CCC;
    border-radius: 3px;
    position: relative;
}
.exoapi-add-new-users .json-example {
	display: inline-block;
	background: #DDD;
	font-family: monospace;
	padding: 4px 2px;
	line-height: 1.5em;
	font-size: 11px;
	position: absolute;
	top: 0;
	left: 0;
	border-radius: 3px;
	padding: 5px;
}
.exoapi-add-new-users .json-example-container {
	position: relative;
}
.exoapi-add-new-users .json-toggle {
	text-decoration: underline;
	cursor: help;
}
.exoapi-add-new-users .exoapi-add-new-users {
	background: white;
}
.exoapi-add-new-users .exoapi-add-new-users {
    background: white;
    padding: 0 20px 15px 20px;
    display: inline-block;
    border: 1px solid #5555;
}
.exoapi-add-new-users .save-users-button {
	display: block;
	margin-top: 12px;
	margin-left: auto;
}
</style>
<div class="exoapi-add-new-users">
	<h2>Add new Open edx users</h2>
	<p>
		Write the new users info using a JSON array:
	</p>
	<textarea name="eox-api-new-users" id="eox-api-new-users" cols="70" rows="10">
[{
    "email": "honor@example.com",
    "username": "honor",
    "password": "edx",
    "fullname": "Honor McGregor",
    "activate_user": true
}]</textarea>
	<button class="button-secondary save-users-button">Execute API call</button>
</div>
<script>
jQuery(function ($) {

	let callAction = function (action, data_arg) {
		var data = {
			'action': action,
			'_ajax_nonce': "<?= wp_create_nonce( 'eoxapi' ); ?>"
		};
		Object.assign(data, data_arg);
		jQuery.post(ajaxurl, data, function(html) {
			$('.notice').remove();
			$('#wp-edunext-marketing-site_settings > h2').first().after(html);
		});
	}

	$('.json-toggle').on('click', function () {
		$('.json-example').value()
	});

	$('.save-users-button').click(function (e) {
		let data = {users: $('#eox-api-new-users').val()};
		callAction('save_users_ajax', data);
		e.stopPropagation();
		return false;
	});
})
</script>
