<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Base_Custom_Link_Object {

		/**
		 * Default object ID.
		 * @var     number
		 * @access  public
		 * @since   1.1.0
		 */
		public $db_id = 0;

		/**
		 * Type of link.
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $object = '';

		/**
		 * Link ID. Normally used for the POST->ID
		 * @var     number
		 * @access  public
		 * @since   1.1.0
		 */
		public $object_id;

		/**
		 * Parent ID
		 * @var     number
		 * @access  public
		 * @since   1.1.0
		 */
		public $menu_item_parent = 0;

		/**
		 * Type of menu object. Wordpress support only a few, so default is custom.
		 * We override them using loop hooks to display the necessary values.
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $type = 'custom';

		/**
		 * User facing label
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $title;

		/**
		 * Link URL
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $url = '#';

		/**
		 * target attribute for the <a> tag
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $target = '';

		/**
		 * Unused
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $attr_title = '';

		/**
		 * CSS classes applied to the link
		 * @var     array of strings
		 * @access  public
		 * @since   1.1.0
		 */
		public $classes = array("menu-item", "menu-item-type-custom", "menu-item-object-custom", "open-edx-link");

		/**
		 * Unused
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $xfn = '';

		/**
		 * Unused
		 * @var     string
		 * @access  public
		 * @since   1.1.0
		 */
		public $description = '';

}


class WP_eduNEXT_Marketing_Site_Menu {

		/**
		 * Possible link values
		 * @var     array of strings
		 * @access  public
		 * @since   1.1.0
		 */
		public $button_types;


		/**
		 * Constructor function.
		 * @access  public
		 * @since   1.1.0
		 * @return  void
		 */
		public function __construct () {
				add_action( 'admin_head-nav-menus.php', array( $this, 'edunext_add_menu_metabox' ), 10 );
				add_filter( 'nav_menu_link_attributes', array( $this, 'edunext_nav_menu_filter'), 10, 3 );

				add_filter( 'wp_setup_nav_menu_item', array( $this, 'edunext_menu_set_types'), 10, 3 );
				add_filter( 'wp_get_nav_menu_items', array( $this, 'edunext_filter_invalid_items'), 10, 3 );

				$this->button_types = array(
						"login_or_menu_openedx"  => __('Login/User Menu', 'wp-edunext-marketing-site'),
						"login_or_dash_openedx"  => __('Login/Dashboard', 'wp-edunext-marketing-site'),
						"login_openedx"          => __('Login Btn', 'wp-edunext-marketing-site'),
						"register_openedx"       => __('Register Btn', 'wp-edunext-marketing-site'),
						"menu_openedx"           => __('User Menu', 'wp-edunext-marketing-site'),
						"resume_openedx"         => __('Resume your last course', 'wp-edunext-marketing-site'),
						"dashboard_openedx"      => __('Dashboard', 'wp-edunext-marketing-site'),
						"profile_openedx"        => __('Profile', 'wp-edunext-marketing-site'),
						"account_openedx"        => __('Account', 'wp-edunext-marketing-site'),
						"signout_openedx"        => __('Sign Out', 'wp-edunext-marketing-site'),
				);

		}


		/**
		 * Modify the items to hold more descriptive types and labels
		 * @return object              WP_Menu_Item
		 */
		public function edunext_menu_set_types($menu_item) {

				if ( in_array( "open-edx-link", $menu_item->classes ) ) {
						$menu_item->type_label = __('Open edX Link', 'wp-edunext-marketing-site');
						$menu_item->type = "wp-edunext-marketing-site";
						foreach ($this->button_types as $key => $value) {
								if (in_array( $key, $menu_item->classes) ) {
										$menu_item->object = $key;
								}
						}
				}

				return $menu_item;
		}


		/**
		 * Work on the final list of menu items
		 * @return array
		 */
		function edunext_filter_invalid_items ($items, $menu, $args) {

				if ( is_admin() ) {
					return $items;
				}

				// Read the cookie to see if we go to login or to dashboard
				$is_user_logged_in = false;
				$is_logged_in_cookie = get_option('wpt_is_logged_in_cookie_name');
				if(isset($_COOKIE[$is_logged_in_cookie])) {
						if ( "true" == $_COOKIE[$is_logged_in_cookie] ) {
								$is_user_logged_in = true;
						}
				}

				foreach ( $items as $key => $item ) {
						if ( $item->type == "wp-edunext-marketing-site" ) {

								// Items with OR clauses need to decide their path
								if ( $item->object == "login_or_menu_openedx" ) {
										$title = preg_split("/\//", $item->title);
										if ( $is_user_logged_in ) {
												$item->object = "menu_openedx";
												$item->title = isset($title[1]) ? $title[1] : __("Dashboard", 'wp-edunext-marketing-site');
										}
										else {
												$item->object = "login_openedx";
												$item->title = isset($title[0]) ? $title[0] : __("Login", 'wp-edunext-marketing-site');
										}
								}
								if ( $item->object == "login_or_dash_openedx" ) {
										$title = preg_split("/\//", $item->title);
										if ( $is_user_logged_in ) {
												$item->object = "dashboard_openedx";
												$item->title = isset($title[1]) ? $title[1] : __("Dashboard", 'wp-edunext-marketing-site');
										}
										else {
												$item->object = "login_openedx";
												$item->title = isset($title[0]) ? $title[0] : __("Login", 'wp-edunext-marketing-site');
										}
								}

								// Users with no session, don't see this items
								if ( !$is_user_logged_in && in_array($item->object, array("menu_openedx", "resume_openedx", "dashboard_openedx", "profile_openedx", "account_openedx", "signout_openedx") ) ) {
										unset($items[$key]);
								}

								// Users with session, don't need to see this items
								if ( $is_user_logged_in && in_array($item->object, array("login_openedx", "register_openedx") ) ) {
										unset($items[$key]);
								}

						}
				}
				return $items;
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
				<div id="openedx-links" class="openedxlinksdiv">
					<div id="tabs-panel-openedx-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
						<ul id="openedx-linkschecklist" class="list:openedx-links categorychecklist form-no-clear">
							<?php echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $elems_obj), 0, (object) array('walker' => $walker)); ?>
						</ul>
					</div>
					<p class="button-controls">
						<span class="add-to-menu">
							<input type="submit"<?php disabled($nav_menu_selected_id, 0); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu', 'wp-edunext-marketing-site'); ?>" name="add-openedx-links-menu-item" id="submit-openedx-links" />
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
				if ( $item->type == "wp-edunext-marketing-site" ) {

						$user_info_cookie = get_option('wpt_user_info_cookie_name');
						if(isset($_COOKIE[$user_info_cookie])) {
								$cookie_val = $_COOKIE[$user_info_cookie];

								$remove_054 = preg_replace('/\\\054/', ',', $cookie_val);
								$stripslashes = stripslashes($remove_054);
								$cookie_json = json_decode($stripslashes);
								$cookie_data = json_decode($cookie_json, true);

								return call_user_func(array($this, 'handle_' . $item->object), $atts, $item, $args, $cookie_data );
						}
				}

				return $atts;
		}

		/**
		 * Create a link to the lms login page
		 * @return object              attributes for an anchor tag
		 */
		public function handle_login_openedx ( $atts, $item, $args, $data ) {
				$base_url = get_option('wpt_lms_base_url');
				$atts["href"] = $base_url . "/login";
				return $atts;
		}

		/**
		 * Create a link to the lms register page
		 * @return object              attributes for an anchor tag
		 */
		public function handle_register_openedx ( $atts, $item, $args, $data ) {
				$base_url = get_option('wpt_lms_base_url');
				$atts["href"] = $base_url . "/register";
				return $atts;
		}

		/**
		 * Create a link to the dashboard page with the username as title
		 * @return object              attributes for an anchor tag
		 */
		public function handle_menu_openedx ( $atts, $item, $args, $data ) {
				$base_url = get_option('wpt_lms_base_url');
				$atts["href"] = $base_url . "/dashboard";
				if ( isset( $data['username'] ) ) {
						$item->title = $data['username'];
				}
				return $atts;
		}

		/**
		 * Create a link to resume block written in the cookie
		 * @return object              attributes for an anchor tag
		 */
		public function handle_resume_openedx ( $atts, $item, $args, $data ) {
				if ( isset( $data['header_urls'] ) && isset( $data['header_urls']["resume_block"] ) ) {
						$atts["href"] = $data['header_urls']["resume_block"];
				}
				return $atts;
		}

		/**
		 * Create a link to the resume block written in the cookie
		 * @return object              attributes for an anchor tag
		 */
		public function handle_dashboard_openedx ( $atts, $item, $args, $data ) {
				$base_url = get_option('wpt_lms_base_url');
				$atts["href"] = $base_url . "/dashboard";
				return $atts;
		}

		/**
		 * Create a link to the profile page written in the cookie
		 * @return object              attributes for an anchor tag
		 */
		public function handle_profile_openedx ( $atts, $item, $args, $data ) {
				if ( isset( $data['header_urls'] ) && isset( $data['header_urls']["learner_profile"] ) ) {
						$atts["href"] = $data['header_urls']["learner_profile"];
				}
				return $atts;
		}

		/**
		 * Create a link to the account settings page written in the cookie
		 * @return object              attributes for an anchor tag
		 */
		public function handle_account_openedx ( $atts, $item, $args, $data ) {
				if ( isset( $data['header_urls'] ) && isset( $data['header_urls']["account_settings"] ) ) {
						$atts["href"] = $data['header_urls']["account_settings"];
				}
				return $atts;
		}

		/**
		 * Create a link to the signout page written in the cookie
		 * @return object              attributes for an anchor tag
		 */
		public function handle_signout_openedx ( $atts, $item, $args, $data ) {
				if ( isset( $data['header_urls'] ) && isset( $data['header_urls']["logout"] ) ) {
						$atts["href"] = $data['header_urls']["logout"];
				}
				return $atts;
		}

}
