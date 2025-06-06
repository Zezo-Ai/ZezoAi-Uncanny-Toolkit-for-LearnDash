<?php

namespace uncanny_learndash_toolkit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class LoginLogoutMenu
 * @package uncanny_learndash_toolkit
 *
 * @since   1.3.2 uo_login changed to uo_login_link to resolve conflict from Front End Logn
 *
 *
 *
 */
class LoginLogoutMenu extends Config implements RequiredFunctions {
	// Custom Menu Items
	protected static $login_menu_item_urls = array(
		'#uo_log_in_link',
		'#uo_log_out_link',
		'#uo_log_in_out_link',
		'#uo_register_link'
	);

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'run_frontend_hooks' ) );
	}

	/*
	 * Initialize frontend actions and filters
	 *
	 * @deprecated v1.3.2 uo_login shortcode
	 */
	public static function run_frontend_hooks() {

		if ( true === self::dependants_exist() ) {

			if ( is_admin() ) {
				add_action( 'admin_head-nav-menus.php', array( __CLASS__, 'add_admin_nav_menus_metabox' ) );
				add_filter( 'wp_setup_nav_menu_item', array( __CLASS__, 'update_menu_item_labels' ) );
			} else {
				add_filter( 'wp_setup_nav_menu_item', array( __CLASS__, 'override_setup_nav_menu_item' ) );
				add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'filter_wp_nav_menu_objects' ) );

				add_shortcode( 'uo_login_link', array( __CLASS__, 'login_link' ) );
				add_shortcode( 'uo_loginout', array( __CLASS__, 'loginout_link' ) );
				add_shortcode( 'uo_logout', array( __CLASS__, 'logout_link' ) );
				add_shortcode( 'uo_register', array( __CLASS__, 'register_link' ) );

			}

			if ( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus', false ) ) {
				if ( "" !== self::get_settings_value( 'uo_frontendloginplus_enable_ajax_support', 'FrontendLoginPlus', '' ) ) {
					self::$login_menu_item_urls[] = '#ult-modal-open----ult-login';
				}
			}
		}

	}

	/**
	 * Description of class in Admin View
	 *
	 * @return array
	 */
	public static function get_details() {
		$module_id   = 'log-in-log-out-links';
		$class_title = esc_html__( 'Log In/Log Out Links', 'uncanny-learndash-toolkit' );

		$kb_link = 'https://www.uncannyowl.com/knowledge-base/log-in-log-out-links/';

		/* Sample Simple Description with shortcode */
		$class_description = esc_html__( 'Add Log In and Log Out links to menus, or to any page or widget with a shortcode.', 'uncanny-learndash-toolkit' );

		/* Icon as fontawesome icon */
		$class_icon = '<i class="uo_icon_fa fa fa-bars"></i>';
		$category   = 'wordpress';
		$type       = 'free';

		return array(
			'id'               => $module_id,
			'title'            => $class_title,
			'type'             => $type,
			'category'         => $category,
			'kb_link'          => $kb_link, // OR set as null not to display
			'description'      => $class_description,
			'dependants_exist' => self::dependants_exist(),
			'settings'         => self::get_class_settings( $class_title ),
			'icon'             => $class_icon,
		);

	}

	/**
	 * Does the plugin rely on another function or plugin
	 *
	 * @return boolean || string Return either true or name of function or plugin
	 *
	 */
	public static function dependants_exist() {
		// Return true if no dependency or dependency is available
		return true;
	}


	/**
	 * HTML for modal to create settings
	 *
	 * @static
	 *
	 * @param $class_title
	 *
	 * @return string
	 */
	public static function get_class_settings( $class_title ) {

		return false;
	}

	/**
	 * Adds the admin nav menu metabox
	 *
	 * @since 1.3.2
	 */
	public static function add_admin_nav_menus_metabox() {
		add_meta_box( 'uncanny_menu_links', esc_html__( 'Uncanny Menu Links', 'uncanny-learndash-toolkit' ), array(
			__CLASS__,
			'create_admin_nav_menu_metabox'
		), 'nav-menus', 'side', 'default' );
	}

	/**
	 * Creates the admin nav menu metabox
	 *
	 * @since 1.3.2
	 *
	 * @param object $object The nav menu object
	 */
	public static function create_admin_nav_menu_metabox( $object ) {
		global $nav_menu_selected_id;

		$nav_menu_items = array();

		foreach ( self::$login_menu_item_urls as $url ) {

			// Add proper labels
			switch ( $url ) {
				case '#uo_log_in_link':
					$label = esc_html__( 'Log In', 'uncanny-learndash-toolkit');
					break;
				case '#uo_log_out_link':
					$label = esc_html__( 'Log Out', 'uncanny-learndash-toolkit');
					break;
				case '#uo_log_in_out_link':
					$label = esc_html__( 'Log In / Log Out', 'uncanny-learndash-toolkit');
					break;
				case '#uo_register_link':
					$label = esc_html__( 'Register', 'uncanny-learndash-toolkit');
					break;
				case '#ult-modal-open----ult-login':
					$label = esc_html__( 'Front end login modal', 'uncanny-learndash-toolkit');
					break;
				default:
					$label = '';
			}
			$nav_menu_items[ $label ]                   = new \stdClass();
			$nav_menu_items[ $label ]->db_id            = 0;
			$nav_menu_items[ $label ]->object           = 'uo-login-logout-menu';
			$nav_menu_items[ $label ]->object_id        = esc_attr( $url );
			$nav_menu_items[ $label ]->menu_item_parent = 0;
			$nav_menu_items[ $label ]->type             = 'custom';
			$nav_menu_items[ $label ]->title            = $label;
			$nav_menu_items[ $label ]->url              = esc_attr( $url );
			$nav_menu_items[ $label ]->target           = '';
			$nav_menu_items[ $label ]->attr_title       = '';
			$nav_menu_items[ $label ]->classes          = array( 'uo-login-logout-menu-item' );
			$nav_menu_items[ $label ]->xfn              = '';
		}


		$walker = new \Walker_Nav_Menu_Checklist( array() );

		// Output the html
		?>
		<div id="uo-login-logout-menu">

			<div id="tabs-panel-uo-login-logout-menu-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="uo-login-logout-menuchecklist"
					class="list:uo-login-logout-menu categorychecklist form-no-clear">
					<?php echo walk_nav_menu_tree( array_map( '\wp_setup_nav_menu_item', $nav_menu_items ), 0, (object) array( 'walker' => $walker ) ); ?>
				</ul>
			</div>

			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?>
						   class="button-secondary submit-add-to-menu right"
						   value="<?php esc_attr_e( 'Add to Menu', 'uncanny-learndash-toolkit' ); ?>"
						   name="add-uo-login-logout-menu-menu-item" id="submit-uo-login-logout-menu"/>
					<span class="spinner"></span>
				</span>
			</p>

		</div>
		<?php
	}

	/**
	 * Updates menu item labels in admin
	 *
	 * @since 1.3.2
	 *
	 * @param object $menu_item The menu item object
	 * @return object Modified menu item object
	 */
	public static function update_menu_item_labels( $menu_item ) {

		// Check if menu item is in the list
		if ( isset( $menu_item->object, $menu_item->url ) && 'custom' === $menu_item->object && in_array( $menu_item->url, self::$login_menu_item_urls ) ) {

			switch ( $menu_item->url ) {
				case '#uo_log_in_link':
					$label = esc_html__( ' - Log In', 'uncanny-learndash-toolkit');
					break;
				case '#uo_log_out_link':
					$label = esc_html__( ' - Log Out', 'uncanny-learndash-toolkit');
					break;
				case '#uo_log_in_out_link':
					$label = esc_html__( ' - Log In/Out', 'uncanny-learndash-toolkit');
					break;
				case '#uo_register_link':
					$label = esc_html__( ' - Register', 'uncanny-learndash-toolkit');
					break;
				case '#ult-modal-open----ult-login':
					$label = esc_html__( ' - Front end login modal', 'uncanny-learndash-toolkit');
					break;
				default:
					$label = '';
			}

			$menu_item->type_label = esc_html__( 'Uncanny Toolkit', 'uncanny-learndash-toolkit') . $label;
		}

		return $menu_item;
	}

	/**
	 * Overrides the default nav menu item setup
	 *
	 * @since 1.3.2
	 *
	 * @param object $item The menu item object
	 * @return object Modified menu item object
	 */
	public static function override_setup_nav_menu_item( $item ) {

		// Only do this when we are on the frontend and only if its a Uncanny Link
		if ( ! defined( 'DOING_AJAX' ) && isset( $item->url ) && ( strpos( $item->url, '#uo_' ) !== false || strpos( $item->url, '#ult-' ) !== false ) ) {

			switch ( $item->url ) {
				case '#uo_log_in_out_link' :

					// Get variable title
					$title = explode( '/', $item->title );

					if ( 2 === count( $title ) ) {

						//remove trailing and leading spaces
						$logged_in_title = isset( $title[0] ) ? sanitize_text_field( $title[0] ) : '';
						$logged_out_title = isset( $title[1] ) ? sanitize_text_field( $title[1] ) : '';
					} else {
						$logged_in_title  = $item->title;
						$logged_out_title = $item->title;
					}

					if ( ! is_user_logged_in() ) {
						$item->url   = wp_login_url();
						$item->title = $logged_in_title;
					} else {
						$item->url   = wp_logout_url();
						$item->title = $logged_out_title;
					}
					break;
				case '#uo_log_in_link' :
					$item->url = wp_login_url();
					break;
				case '#uo_log_out_link' :
					$item->url = wp_logout_url();
					break;
				case '#uo_register_link' :
					// registration is allowed
					if ( is_user_logged_in() || ! get_option( 'users_can_register' ) ) {
						$item->url = '#uo_remove_item';
					} else {
						$item->url = site_url( 'wp-login.php?action=register', 'login' );
					}
					break;
				case '#ult-modal-open----ult-login' :
					if ( is_user_logged_in() ) {
						$item->url = '#uo_remove_item';
					} else {
					    if( class_exists( '\uncanny_learndash_toolkit\FrontendLoginPlus', false ) ) {
						    $login_page_id = \uncanny_learndash_toolkit\FrontendLoginPlus::get_login_redirect_page_id();
						    $login_page    = '#ult-modal-open----ult-login';
						    if ( $login_page_id ) {
							    $login_page = get_permalink( $login_page_id ) . $login_page;
						    } else {
							    $login_page = site_url( 'wp-login.php' ) . $login_page;
						    }
						    $item->url = $login_page;
					    } else {
						    $item->url = site_url( 'wp-login.php' );
                        }
					}
					break;
			}
			$item->url = esc_url( $item->url );
		}

		return $item;
	}

	/**
	 * Filters nav menu objects to remove certain items
	 *
	 * @since 1.3.2
	 *
	 * @param array $sorted_menu_items Array of menu items
	 * @return array Filtered array of menu items
	 */
	public static function filter_wp_nav_menu_objects( $sorted_menu_items ) {
		foreach ( $sorted_menu_items as $k => $item ) {
			if ( ! isset(  $item->url ) ) {
				continue;
			}
			if ( '#uo_remove_item' == $item->url ) {
				unset( $sorted_menu_items[ $k ] );
			}
		}

		return $sorted_menu_items;
	}

	/**
	 * Creates a login link shortcode
	 *
	 * @since 1.3.2
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 * @return string HTML for login link
	 */
	public static function login_link( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"edit_tag" => "",
			"redirect" => esc_url( $_SERVER['REQUEST_URI'] )
		), $atts, 'login' );

		$href     = wp_login_url( /*$atts['redirect']*/ );
		$content  = $content != '' ? $content : esc_html__( 'Log In', 'uncanny-learndash-toolkit' );

		return '<a href="' . esc_url( $href ) . '" ' . self::sanitize_edit_tag($atts['edit_tag']) . '>' . $content . '</a>';
	}

	/**
	 * Creates a login/logout toggle link shortcode
	 *
	 * @since 1.3.2
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 * @return string HTML for login/logout toggle link
	 */
	public static function loginout_link( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"edit_tag" => "",
			"redirect" => esc_url( $_SERVER['REQUEST_URI'] ),
			"log_in_text"  => esc_html__( 'Log In', 'uncanny-learndash-toolkit' ),
			"log_out_text" => esc_html__( 'Logout', 'uncanny-learndash-toolkit' )
		), $atts, 'loginout' );

		$href     = is_user_logged_in() ? wp_logout_url( /*$atts['redirect']*/ ) : wp_login_url( /*$atts['redirect']*/ );
		if ( $content && strstr( $content, '|' ) != '' ) { // the "|" char is used to split titles
			$content = explode( '|', $content );
			$content = is_user_logged_in() ? $content[1] : $content[0];
		} else {
			$content = is_user_logged_in() ? $atts[ 'log_out_text' ] : $atts[ 'log_in_text' ];
		}

		return '<a href="' . esc_url( $href ) . '" ' . self::sanitize_edit_tag($atts['edit_tag']) . '>' . wp_kses_post($content) . '</a>';
	}

	/**
	 * Creates a logout link shortcode
	 *
	 * @since 1.3.2
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 * @return string HTML for logout link
	 */
	public static function logout_link( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"edit_tag" => "",
			"redirect" => esc_url( $_SERVER['REQUEST_URI'] )
		), $atts, 'logout' );

		$href     = wp_logout_url( /*$atts['redirect']*/ );
		$content  = $content != '' ? $content : esc_html__( 'Logout', 'uncanny-learndash-toolkit' );

		return '<a href="' . esc_url( $href ) . '" ' . self::sanitize_edit_tag($atts['edit_tag']) . '>' . wp_kses_post($content) . '</a>';
	}

	/**
	 * Creates a registration link shortcode
	 *
	 * @since 1.3.2
	 *
	 * @param array  $atts    Shortcode attributes
	 * @param string $content Shortcode content
	 * @return string HTML for registration link
	 */
	public static function register_link( $atts, $content = null ) {
		if ( is_user_logged_in() ) {
			return '';
		}
		$href    = site_url( 'wp-login.php?action=register', 'login' );
		$content = $content != '' ? $content : esc_html__( 'Register', 'uncanny-learndash-toolkit' );
		$link    = '<a href="' . esc_url($href) . '">' . wp_kses_post($content) . '</a>';

		return $link;
	}

	/**
	 * Sanitizes HTML attributes for login/logout links
	 *
	 * @since 1.3.2
	 *
	 * @param string $tag_value The HTML attributes to sanitize
	 * @return string Sanitized HTML attributes
	 */
	private static function sanitize_edit_tag( $tag_value ) {
		// Define allowed HTML attributes for links
		$allowed_attributes = array(
			'class'    => true,
			'id'       => true,
			'title'    => true,
			'rel'      => true,
			'target'   => true,
			'data-*'   => true,
		);

		// Sanitize the input string
		$tag_value = wp_kses_no_null( $tag_value );
		$tag_value = preg_replace( '/[\x00-\x1F\x7F]/', '', $tag_value );

		// Remove event handlers and disallowed attributes
		$edit_tag = '';
		$edit_tag_parts = explode( ' ', $tag_value );
		
		foreach ( $edit_tag_parts as $part ) {
			$part = trim( $part );
			
			// Skip empty parts
			if ( empty( $part ) ) {
				continue;
			}

			// Skip event handlers and javascript: URLs
			if ( strpos( $part, 'on' ) === 0 || 
				 strpos( $part, 'javascript:' ) === 0 || 
				 strpos( $part, 'data:' ) === 0 ) {
				continue;
			}

			// Handle attributes with values
			if ( strpos( $part, '=' ) !== false ) {
				list( $attribute, $value ) = explode( '=', $part, 2 );
				$attribute = strtolower( trim( $attribute ) );
				$value = trim( $value, '"\'' );

				// Additional security checks for data attributes
				if ( strpos( $attribute, 'data-' ) === 0 ) {
					// Only allow alphanumeric characters, hyphens, and underscores in data attribute names
					if ( ! preg_match( '/^data-[a-z0-9-_]+$/', $attribute ) ) {
						continue;
					}
					// Sanitize data attribute values
					$value = wp_kses_no_null( $value );
					$value = preg_replace( '/[\x00-\x1F\x7F]/', '', $value );
				}

				// Check if attribute is allowed
				if ( isset( $allowed_attributes[ $attribute ] ) || 
					( strpos( $attribute, 'data-' ) === 0 && isset( $allowed_attributes['data-*'] ) ) ) {
					$edit_tag .= ' ' . $attribute . '="' . esc_attr( $value ) . '"';
				}
			} else {
				// Handle boolean attributes
				$attribute = strtolower( trim( $part ) );
				if ( isset( $allowed_attributes[ $attribute ] ) ) {
					$edit_tag .= ' ' . $attribute;
				}
			}
		}

		return $edit_tag;
	}
}
