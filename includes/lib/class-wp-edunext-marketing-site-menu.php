<?php

if ( ! defined( 'ABSPATH' ) ) exit;

	// TODO get some docstrings here
	class WP_Base_Custom_Link_Object {
		public $db_id = 0;
		public $object = '';
		public $object_id;
		public $menu_item_parent = 0;
		public $type = 'custom';
		public $title;
		public $url = '#';
		public $target = '';
		public $attr_title = '';
		public $classes = array("menu-item", "menu-item-type-custom", "menu-item-object-custom", "open-edx-link");
		public $xfn = '';
		public $description = '';
	}



class WP_eduNEXT_Marketing_Site_Menu {


		public $button_types;

		/**
		 * Constructor function.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function __construct () {
				add_action( 'admin_head-nav-menus.php', array( $this, 'edunext_add_menu_metabox' ), 10 );
				add_filter( 'nav_menu_link_attributes', array( $this, 'edunext_nav_menu_filter'), 10, 3 );

				$this->button_types = array(
						"login_or_menu"  => __('Login/Username', 'wp-edunext-marketing-site'),
						"login"          => __('Login Btn', 'wp-edunext-marketing-site'),
						"register"       => __('Register Btn', 'wp-edunext-marketing-site'),
						"menu"           => __('User Menu', 'wp-edunext-marketing-site'),
						"resume"         => __('Resume your last course', 'wp-edunext-marketing-site'),
						"dashboard"      => __('Dashboard', 'wp-edunext-marketing-site'),
						"profile"        => __('Profile', 'wp-edunext-marketing-site'),
						"account"        => __('Account', 'wp-edunext-marketing-site'),
						"signout"        => __('Sign Out', 'wp-edunext-marketing-site'),
				);

		}


		/**
		 * Register new metabox for the menu items
		 * @return void
		 */
		public function edunext_add_menu_metabox() {
				add_meta_box('edunext_menu_items', __('Integration Open edX', 'wp-edunext-marketing-site'), array( $this, 'edunext_nav_menu_metabox' ), 'nav-menus', 'side', 'default');
		}


		/**
		 * Create the metabox at the menu side panel
		 *
		 * This function works thanks to cartpauj at https://caseproof.com
		 * Who in turn cites Grégory Viguier from https://screenfeed.fr as the author of the original code.
		 * Thanks to both of you! <3
		 *
		 * @return void
		 */
		public function edunext_nav_menu_metabox($object) {
				global $nav_menu_selected_id;

				$elems_obj = array();

				foreach($this->button_types as $value => $title) {
						$elems_obj[$value]              = new WP_Base_Custom_Link_Object();
						$elems_obj[$value]->title       = esc_attr($title);
						$elems_obj[$value]->object_id   = esc_attr($value);
						array_push($elems_obj[$value]->classes, esc_attr($value));
				}

				$walker = new Walker_Nav_Menu_Checklist(array());

				?>
				<div id="login-links" class="loginlinksdiv">
					<div id="tabs-panel-login-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
						<ul id="login-linkschecklist" class="list:login-links categorychecklist form-no-clear">
							<?php echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $elems_obj), 0, (object) array('walker' => $walker)); ?>
						</ul>
					</div>
					<p class="button-controls">
						<span class="add-to-menu">
							<input type="submit"<?php disabled($nav_menu_selected_id, 0); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu', 'wp-edunext-marketing-site'); ?>" name="add-login-links-menu-item" id="submit-login-links" />
							<span class="spinner"></span>
						</span>
					</p>
				</div>
				<?php
		}

		/**
		 * Create the correct links when called from the site
		 * @return object           attributes of the current menu item
		 */
		public function edunext_nav_menu_filter( $atts, $item, $args ) {

				// If the link is not one of ours, then just leave
				if ( in_array( "open-edx-link", $item->classes ) ) {

						// Read the cookie to see if we go to login or to dashboard
						$is_logged_in_cookie = "edxloggedin";  // TODO, read from the vars
						if(isset($_COOKIE[$is_logged_in_cookie])) {
								if ( "true" == $_COOKIE[$is_logged_in_cookie] ) {
										$atts["href"] = "#go-to-dashboard";
								}
						}

						$user_info_cookie = "edx-user-info";  // TODO, read from the vars
						if(isset($_COOKIE[$user_info_cookie])) {
								$cookie_val = $_COOKIE[$user_info_cookie];

								$remove_054 = preg_replace('/\\\054/', ',', $cookie_val);
								$stripslashes = stripslashes($remove_054);
								$cookie_json = json_decode($stripslashes);
								$cookie_data = json_decode($cookie_json, true);

								foreach($this->button_types as $value => $title) {
										if ( in_array( $value, $item->classes ) ) {
												return call_user_func(array($this, 'handle_' . $value), $atts, $item, $args, $cookie_data );
										}
								}
						}
				}

				return $atts;
		}

		public function handle_login_or_menu ( $atts, $item, $args, $data ) {
				return $atts;
		}

		public function handle_menu ( $atts, $item, $args, $data ) {
				return $atts;
		}

}
