<?php
/**
 * Plugin Name: Simple Timeline Collections
 * Description: Create responsive timeline collections from reusable event entries and display them with a shortcode.
 * Version:     1.9.4
 * Author:      MyPotsTracker
 * Author URI:  https://mypotstracker.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-timeline-collections
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'STC_Simple_Timeline_Collections' ) ) {

final class STC_Simple_Timeline_Collections {
	const VERSION            = '1.9.4';
	const DB_VERSION_OPTION  = 'stc_simple_timeline_collections_db_version';
	const POST_TYPE          = 'stc_event';
	const TIMELINE_POST_TYPE = 'stc_timeline';
	const OPTION_NAME        = 'stc_simple_timeline_collections_options';

	const EVENT_NONCE_ACTION    = 'stc_save_event_details';
	const EVENT_NONCE_NAME      = 'stc_event_details_nonce';
	const TIMELINE_NONCE_ACTION = 'stc_save_timeline_settings';
	const TIMELINE_NONCE_NAME   = 'stc_timeline_settings_nonce';

	const META_DATE           = '_stc_event_date';
	const META_DESC           = '_stc_event_description';
	const META_CTA_URL        = '_stc_event_cta_url';
	const META_CTA_TEXT       = '_stc_event_cta_text';
	const META_CTA_NEW        = '_stc_event_cta_new_tab';
	const META_EVENT_TIMELINE = '_stc_timeline_id';

	const META_TIMELINE_TITLE                         = '_stc_timeline_title';
	const META_TIMELINE_TITLE_ALIGNMENT               = '_stc_timeline_title_alignment';
	const META_TIMELINE_LAYOUT                        = '_stc_timeline_layout';
	const META_TIMELINE_ALTERNATE_CARD_GAP           = '_stc_timeline_alternate_card_gap';
	const META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX   = '_stc_timeline_event_item_before_color_hex';
	const META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX  = '_stc_timeline_event_item_current_color_hex';
	const META_TIMELINE_CARD_BACKGROUND_COLOR_HEX    = '_stc_timeline_card_background_color_hex';
	const META_TIMELINE_RAIL_COLOR_HEX               = '_stc_timeline_rail_color_hex';


	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_timeline_post_type' ) );
		add_action( 'init', array( $this, 'register_event_post_type' ) );
		add_action( 'admin_init', array( $this, 'maybe_run_upgrade' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_event_meta' ) );
		add_action( 'save_post_' . self::TIMELINE_POST_TYPE, array( $this, 'save_timeline_meta' ) );
		add_filter( 'enter_title_here', array( $this, 'custom_title_placeholder' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_shortcode( 'simple_timeline_collections', array( $this, 'render_shortcode' ) );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'set_event_admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_event_admin_columns' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'set_event_sortable_columns' ) );
		add_action( 'restrict_manage_posts', array( $this, 'render_events_timeline_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_and_filter_admin_events' ) );

		add_filter( 'manage_' . self::TIMELINE_POST_TYPE . '_posts_columns', array( $this, 'set_timeline_admin_columns' ) );
		add_action( 'manage_' . self::TIMELINE_POST_TYPE . '_posts_custom_column', array( $this, 'render_timeline_admin_columns' ), 10, 2 );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
	}

	public function register_timeline_post_type() {
		$labels = array(
			'name'               => __( 'Timelines', 'simple-timeline-collections' ),
			'singular_name'      => __( 'Timeline', 'simple-timeline-collections' ),
			'menu_name'          => __( 'Simple Timeline Collections', 'simple-timeline-collections' ),
			'name_admin_bar'     => __( 'Timeline', 'simple-timeline-collections' ),
			'add_new'            => __( 'Add Timeline', 'simple-timeline-collections' ),
			'add_new_item'       => __( 'Add Timeline', 'simple-timeline-collections' ),
			'new_item'           => __( 'New Timeline', 'simple-timeline-collections' ),
			'edit_item'          => __( 'Edit Timeline', 'simple-timeline-collections' ),
			'view_item'          => __( 'View Timeline', 'simple-timeline-collections' ),
			'all_items'          => __( 'All Timelines', 'simple-timeline-collections' ),
			'search_items'       => __( 'Search Timelines', 'simple-timeline-collections' ),
			'not_found'          => __( 'No timelines found.', 'simple-timeline-collections' ),
			'not_found_in_trash' => __( 'No timelines found in Trash.', 'simple-timeline-collections' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 21,
			'menu_icon'          => 'dashicons-backup',
			'supports'           => array( 'title', 'revisions' ),
			'show_in_rest'       => true,
		);

		register_post_type( self::TIMELINE_POST_TYPE, $args );
	}

	public function register_event_post_type() {
		$labels = array(
			'name'               => __( 'Timeline Events', 'simple-timeline-collections' ),
			'singular_name'      => __( 'Timeline Event', 'simple-timeline-collections' ),
			'menu_name'          => __( 'Timeline Events', 'simple-timeline-collections' ),
			'name_admin_bar'     => __( 'Timeline Event', 'simple-timeline-collections' ),
			'add_new'            => __( 'Add Event', 'simple-timeline-collections' ),
			'add_new_item'       => __( 'Add Timeline Event', 'simple-timeline-collections' ),
			'new_item'           => __( 'New Timeline Event', 'simple-timeline-collections' ),
			'edit_item'          => __( 'Edit Timeline Event', 'simple-timeline-collections' ),
			'view_item'          => __( 'View Timeline Event', 'simple-timeline-collections' ),
			'all_items'          => __( 'All Timeline Events', 'simple-timeline-collections' ),
			'search_items'       => __( 'Search Timeline Events', 'simple-timeline-collections' ),
			'not_found'          => __( 'No timeline events found.', 'simple-timeline-collections' ),
			'not_found_in_trash' => __( 'No timeline events found in Trash.', 'simple-timeline-collections' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=' . self::TIMELINE_POST_TYPE,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'timeline-event' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'revisions' ),
			'show_in_rest'       => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	public function maybe_run_upgrade( $force = false ) {
		$stored_version = (string) get_option( self::DB_VERSION_OPTION, '0.0.0' );

		if ( ! $force && version_compare( $stored_version, self::VERSION, '>=' ) ) {
			return;
		}

		$default_timeline_id = $this->ensure_default_timeline();

		if ( $default_timeline_id ) {
			$this->assign_orphan_events_to_timeline( $default_timeline_id );
		}


		update_option( self::DB_VERSION_OPTION, self::VERSION );
	}

	private function ensure_default_timeline() {
		$options             = $this->get_options();
		$default_timeline_id = isset( $options['default_timeline_id'] ) ? absint( $options['default_timeline_id'] ) : 0;

		if ( $this->is_valid_timeline_id( $default_timeline_id ) ) {
			$this->ensure_timeline_has_settings( $default_timeline_id );
			return $default_timeline_id;
		}

		$timeline_id = 0;
		$existing    = get_page_by_path( 'brief-history', OBJECT, self::TIMELINE_POST_TYPE );

		if ( $existing && self::TIMELINE_POST_TYPE === $existing->post_type ) {
			$timeline_id = absint( $existing->ID );
		}

		if ( ! $timeline_id ) {
			$timeline_id = wp_insert_post(
				array(
					'post_type'   => self::TIMELINE_POST_TYPE,
					'post_status' => 'publish',
					'post_title'  => $options['timeline_title'] ? $options['timeline_title'] : __( 'Brief History', 'simple-timeline-collections' ),
					'post_name'   => 'brief-history',
				)
			);

			if ( is_wp_error( $timeline_id ) ) {
				return 0;
			}
		}

		$this->ensure_timeline_has_settings( $timeline_id );

		$options['default_timeline_id'] = $timeline_id;
		update_option( self::OPTION_NAME, $options );

		return $timeline_id;
	}

	private function ensure_timeline_has_settings( $timeline_id ) {
		$timeline_id = absint( $timeline_id );
		$options     = $this->get_options();

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_TITLE, true ) ) {
			update_post_meta( $timeline_id, self::META_TIMELINE_TITLE, $options['timeline_title'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_TITLE_ALIGNMENT, true ) ) {
			update_post_meta( $timeline_id, self::META_TIMELINE_TITLE_ALIGNMENT, $options['title_alignment'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_LAYOUT, true ) ) {
			update_post_meta( $timeline_id, self::META_TIMELINE_LAYOUT, $options['layout'] );
		}

		if ( '' === get_post_meta( $timeline_id, self::META_TIMELINE_ALTERNATE_CARD_GAP, true ) ) {
			update_post_meta( $timeline_id, self::META_TIMELINE_ALTERNATE_CARD_GAP, $options['alternate_card_gap'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX, true ) ) {
			$this->update_or_delete_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX, $options['event_item_before_color'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX, true ) ) {
			$this->update_or_delete_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX, $options['event_item_current_color'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_CARD_BACKGROUND_COLOR_HEX, true ) ) {
			$this->update_or_delete_meta( $timeline_id, self::META_TIMELINE_CARD_BACKGROUND_COLOR_HEX, $options['card_background_color'] );
		}

		if ( ! get_post_meta( $timeline_id, self::META_TIMELINE_RAIL_COLOR_HEX, true ) ) {
			$this->update_or_delete_meta( $timeline_id, self::META_TIMELINE_RAIL_COLOR_HEX, $options['rail_color'] );
		}
	}



	private function assign_orphan_events_to_timeline( $timeline_id ) {
		$timeline_id = absint( $timeline_id );
		if ( ! $timeline_id ) {
			return;
		}

		$orphan_query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => self::META_EVENT_TIMELINE,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => self::META_EVENT_TIMELINE,
						'value'   => '',
						'compare' => '=',
					),
				),
			)
		);

		foreach ( $orphan_query->posts as $event_id ) {
			update_post_meta( absint( $event_id ), self::META_EVENT_TIMELINE, $timeline_id );
		}

		wp_reset_postdata();
	}

	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::TIMELINE_POST_TYPE,
			__( 'Simple Timeline Collections Settings', 'simple-timeline-collections' ),
			__( 'Settings', 'simple-timeline-collections' ),
			'manage_options',
			'stc-timeline-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'stc_timeline_settings',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => $this->get_default_options(),
			)
		);

		add_settings_section(
			'stc_timeline_display_section',
			__( 'Global Defaults', 'simple-timeline-collections' ),
			array( $this, 'render_display_section_intro' ),
			'stc-timeline-settings'
		);

		add_settings_field(
			'default_timeline_id',
			__( 'Default Timeline', 'simple-timeline-collections' ),
			array( $this, 'render_default_timeline_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);

		add_settings_field(
			'timeline_title',
			__( 'New Timeline Default Title', 'simple-timeline-collections' ),
			array( $this, 'render_title_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);

		add_settings_field(
			'title_alignment',
			__( 'New Timeline Title Alignment', 'simple-timeline-collections' ),
			array( $this, 'render_title_alignment_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);

		add_settings_field(
			'layout',
			__( 'New Timeline Event Alignment', 'simple-timeline-collections' ),
			array( $this, 'render_layout_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);

		add_settings_field(
			'alternate_card_gap',
			__( 'New Timeline Same-side Card Gap', 'simple-timeline-collections' ),
			array( $this, 'render_alternate_card_gap_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);

		add_settings_field(
			'event_item_colors',
			__( 'New Timeline Event Item Colors', 'simple-timeline-collections' ),
			array( $this, 'render_event_item_color_field' ),
			'stc-timeline-settings',
			'stc_timeline_display_section'
		);
	}

	private function get_default_options() {
		return array(
			'timeline_title'           => __( 'Brief History', 'simple-timeline-collections' ),
			'title_alignment'         => 'left',
			'layout'                  => 'right',
			'alternate_card_gap'      => 100,
			'event_item_before_color' => '',
			'event_item_current_color' => '',
			'card_background_color'   => '',
			'rail_color'              => '',
			'default_timeline_id'      => 0,

		);
	}

	private function get_allowed_layouts() {
		return array(
			'alternate' => __( 'Alternate left and right of the center line', 'simple-timeline-collections' ),
			'left'      => __( 'All events left of the center line', 'simple-timeline-collections' ),
			'right'     => __( 'All events right of the center line', 'simple-timeline-collections' ),
		);
	}

	private function get_allowed_title_alignments() {
		return array(
			'left'   => __( 'Left', 'simple-timeline-collections' ),
			'center' => __( 'Center', 'simple-timeline-collections' ),
			'right'  => __( 'Right', 'simple-timeline-collections' ),
		);
	}


	private function normalize_alternate_card_gap( $value, $fallback = 100 ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = '';
		}

		if ( '' === trim( (string) $value ) ) {
			$value = $fallback;
		}

		$gap = absint( $value );

		return min( 300, max( 0, $gap ) );
	}

	private function normalize_event_item_color_settings( $before_color, $current_color, $card_background_color, $rail_color = '', $fallback = array() ) {
		$fallback = wp_parse_args(
			is_array( $fallback ) ? $fallback : array(),
			$this->get_default_options()
		);

		$before_color = sanitize_hex_color( $before_color );
		if ( ! $before_color ) {
			$before_color = sanitize_hex_color( $fallback['event_item_before_color'] );
		}

		$current_color = sanitize_hex_color( $current_color );
		if ( ! $current_color ) {
			$current_color = sanitize_hex_color( $fallback['event_item_current_color'] );
		}

		$card_background_color = sanitize_hex_color( $card_background_color );
		if ( ! $card_background_color ) {
			$card_background_color = sanitize_hex_color( $fallback['card_background_color'] );
		}

		$rail_color = sanitize_hex_color( $rail_color );
		if ( ! $rail_color ) {
			$rail_color = sanitize_hex_color( $fallback['rail_color'] );
		}

		return array(
			'event_item_before_color'  => $before_color ? $before_color : '',
			'event_item_current_color' => $current_color ? $current_color : '',
			'card_background_color'    => $card_background_color ? $card_background_color : '',
			'rail_color'               => $rail_color ? $rail_color : '',
		);
	}

	private function get_timeline_color_style_attr( $settings ) {
		$settings = wp_parse_args(
			is_array( $settings ) ? $settings : array(),
			$this->get_default_options()
		);

		$colors = $this->normalize_event_item_color_settings(
			$settings['event_item_before_color'],
			$settings['event_item_current_color'],
			$settings['card_background_color'],
			$settings['rail_color'],
			$settings
		);

		$declarations = array();
		if ( $colors['event_item_before_color'] ) {
			$declarations[] = '--stc-event-item-before-color: ' . $colors['event_item_before_color'];
		}
		if ( $colors['event_item_current_color'] ) {
			$declarations[] = '--stc-event-item-current-color: ' . $colors['event_item_current_color'];
		}
		if ( $colors['card_background_color'] ) {
			$declarations[] = '--stc-card-background-color: ' . $colors['card_background_color'];
		}
		if ( $colors['rail_color'] ) {
			$declarations[] = '--stc-rail-color: ' . $colors['rail_color'];
		}

		return $declarations ? implode( '; ', $declarations ) . ';' : '';
	}

	private function get_options() {
		$saved    = get_option( self::OPTION_NAME, array() );
		$defaults = $this->get_default_options();

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$options = wp_parse_args( $saved, $defaults );

		if ( ! array_key_exists( $options['layout'], $this->get_allowed_layouts() ) ) {
			$options['layout'] = $defaults['layout'];
		}

		if ( ! array_key_exists( $options['title_alignment'], $this->get_allowed_title_alignments() ) ) {
			$options['title_alignment'] = $defaults['title_alignment'];
		}

		$options['alternate_card_gap'] = $this->normalize_alternate_card_gap( $options['alternate_card_gap'], $defaults['alternate_card_gap'] );

		$options = array_merge(
			$options,
			$this->normalize_event_item_color_settings(
				$options['event_item_before_color'],
				$options['event_item_current_color'],
				$options['card_background_color'],
				$options['rail_color'],
				$options
			)
		);

		if ( '' === trim( $options['timeline_title'] ) ) {
			$options['timeline_title'] = $defaults['timeline_title'];
		}

		$options['default_timeline_id'] = absint( $options['default_timeline_id'] );
		if ( $options['default_timeline_id'] && ! $this->is_valid_timeline_id( $options['default_timeline_id'] ) ) {
			$options['default_timeline_id'] = 0;
		}

		return $options;
	}

	public function sanitize_options( $input ) {
		$defaults = $this->get_default_options();
		$input    = is_array( $input ) ? $input : array();

		$title = isset( $input['timeline_title'] ) ? sanitize_text_field( wp_unslash( $input['timeline_title'] ) ) : $defaults['timeline_title'];
		$title = trim( $title );
		if ( '' === $title ) {
			$title = $defaults['timeline_title'];
		}

		$layout          = isset( $input['layout'] ) ? sanitize_key( wp_unslash( $input['layout'] ) ) : $defaults['layout'];
		$allowed_layouts = $this->get_allowed_layouts();
		if ( ! array_key_exists( $layout, $allowed_layouts ) ) {
			$layout = $defaults['layout'];
		}

		$title_alignment          = isset( $input['title_alignment'] ) ? sanitize_key( wp_unslash( $input['title_alignment'] ) ) : $defaults['title_alignment'];
		$allowed_title_alignments = $this->get_allowed_title_alignments();
		if ( ! array_key_exists( $title_alignment, $allowed_title_alignments ) ) {
			$title_alignment = $defaults['title_alignment'];
		}

		$alternate_card_gap = $this->normalize_alternate_card_gap(
			isset( $input['alternate_card_gap'] ) ? wp_unslash( $input['alternate_card_gap'] ) : $defaults['alternate_card_gap'],
			$defaults['alternate_card_gap']
		);

		$event_item_colors = $this->normalize_event_item_color_settings(
			isset( $input['event_item_before_color'] ) ? wp_unslash( $input['event_item_before_color'] ) : $defaults['event_item_before_color'],
			isset( $input['event_item_current_color'] ) ? wp_unslash( $input['event_item_current_color'] ) : $defaults['event_item_current_color'],
			isset( $input['card_background_color'] ) ? wp_unslash( $input['card_background_color'] ) : $defaults['card_background_color'],
			isset( $input['rail_color'] ) ? wp_unslash( $input['rail_color'] ) : $defaults['rail_color'],
			$defaults
		);

		$default_timeline_id = isset( $input['default_timeline_id'] ) ? absint( $input['default_timeline_id'] ) : 0;
		if ( $default_timeline_id && ! $this->is_valid_timeline_id( $default_timeline_id ) ) {
			$default_timeline_id = 0;
		}

		return array(
			'timeline_title'           => $title,
			'title_alignment'         => $title_alignment,
			'layout'                  => $layout,
			'alternate_card_gap'      => $alternate_card_gap,
			'event_item_before_color' => $event_item_colors['event_item_before_color'],
			'event_item_current_color' => $event_item_colors['event_item_current_color'],
			'card_background_color'   => $event_item_colors['card_background_color'],
			'rail_color'              => $event_item_colors['rail_color'],
			'default_timeline_id'      => $default_timeline_id,
		);
	}

	public function render_display_section_intro() {
		?>
		<p><?php esc_html_e( 'Choose the fallback timeline used by [simple_timeline_collections] when no id or slug is supplied. Each individual Timeline has its own title, title alignment, event alignment, alternating layout spacing, rail color, and event item color settings.', 'simple-timeline-collections' ); ?></p>
		<?php
	}

	public function render_default_timeline_field() {
		$options   = $this->get_options();
		$timelines = $this->get_timeline_posts();
		?>
		<select id="stc_default_timeline_id" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[default_timeline_id]">
			<option value="0"><?php esc_html_e( 'Use the first available timeline', 'simple-timeline-collections' ); ?></option>
			<?php foreach ( $timelines as $timeline ) : ?>
				<option value="<?php echo esc_attr( $timeline->ID ); ?>" <?php selected( $options['default_timeline_id'], $timeline->ID ); ?>>
					<?php echo esc_html( get_the_title( $timeline ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'This timeline is shown by the basic [simple_timeline_collections] shortcode. Use [simple_timeline_collections id="123"] or [simple_timeline_collections timeline="timeline-slug"] for a specific timeline.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	public function render_title_field() {
		$options = $this->get_options();
		?>
		<input
			type="text"
			id="stc_timeline_title"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[timeline_title]"
			value="<?php echo esc_attr( $options['timeline_title'] ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php esc_html_e( 'Used as the initial display title for newly migrated or fallback timelines. Individual timeline titles are managed on each Timeline edit screen.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	public function render_title_alignment_field() {
		$options = $this->get_options();
		?>
		<fieldset class="stc-settings-radio-group stc-settings-radio-group--inline">
			<legend class="screen-reader-text"><?php esc_html_e( 'Choose default timeline title alignment', 'simple-timeline-collections' ); ?></legend>
			<?php foreach ( $this->get_allowed_title_alignments() as $value => $label ) : ?>
				<label class="stc-settings-radio-option" for="stc_timeline_title_alignment_<?php echo esc_attr( $value ); ?>">
					<input
						type="radio"
						id="stc_timeline_title_alignment_<?php echo esc_attr( $value ); ?>"
						name="<?php echo esc_attr( self::OPTION_NAME ); ?>[title_alignment]"
						value="<?php echo esc_attr( $value ); ?>"
						<?php checked( $options['title_alignment'], $value ); ?>
					/>
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Used as the fallback/new timeline title alignment. Individual timelines can override this setting.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	public function render_layout_field() {
		$options = $this->get_options();
		?>
		<fieldset class="stc-settings-radio-group">
			<legend class="screen-reader-text"><?php esc_html_e( 'Choose default timeline event alignment', 'simple-timeline-collections' ); ?></legend>
			<?php foreach ( $this->get_allowed_layouts() as $value => $label ) : ?>
				<label class="stc-settings-radio-option" for="stc_timeline_layout_<?php echo esc_attr( $value ); ?>">
					<input
						type="radio"
						id="stc_timeline_layout_<?php echo esc_attr( $value ); ?>"
						name="<?php echo esc_attr( self::OPTION_NAME ); ?>[layout]"
						value="<?php echo esc_attr( $value ); ?>"
						<?php checked( $options['layout'], $value ); ?>
					/>
					<span><?php echo esc_html( $label ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Used as the fallback/new timeline event alignment. On small screens, all layout modes collapse to a single vertical timeline.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	public function render_alternate_card_gap_field() {
		$options = $this->get_options();

		$this->render_alternate_card_gap_control(
			self::OPTION_NAME . '[alternate_card_gap]',
			'stc_default_alternate_card_gap',
			$options['alternate_card_gap']
		);
		?>
		<p class="description">
			<?php esc_html_e( 'Used as the fallback/new timeline spacing between cards on the same side when Event Alignment is set to Alternate.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	private function render_alternate_card_gap_control( $field_name, $field_id, $value ) {
		$value = $this->normalize_alternate_card_gap( $value );
		?>
		<div class="stc-inline-number-field">
			<input
				type="number"
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				min="0"
				max="300"
				step="5"
				inputmode="numeric"
			/>
			<span aria-hidden="true"><?php esc_html_e( 'px', 'simple-timeline-collections' ); ?></span>
		</div>
		<p class="description">
			<?php esc_html_e( 'Controls the visual space between timeline cards on the same side in the desktop Alternate layout. This setting is ignored on small screens and does not change the chronological reading order.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	public function render_event_item_color_field() {
		$options = $this->get_options();
		$this->render_event_item_color_controls(
			self::OPTION_NAME . '[event_item_before_color]',
			self::OPTION_NAME . '[event_item_current_color]',
			self::OPTION_NAME . '[card_background_color]',
			self::OPTION_NAME . '[rail_color]',
			'stc_default_event_item_color',
			$options
		);
		?>
		<p class="description">
			<?php esc_html_e( 'Used as the fallback/new timeline event item colors, rail color, and card background. Individual timelines can override these settings.', 'simple-timeline-collections' ); ?>
		</p>
		<?php
	}

	private function render_event_item_color_controls( $before_name, $current_name, $card_background_name, $rail_name, $field_prefix, $settings ) {
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), $this->get_default_options() );
		$colors   = $this->normalize_event_item_color_settings(
			$settings['event_item_before_color'],
			$settings['event_item_current_color'],
			$settings['card_background_color'],
			$settings['rail_color'],
			$settings
		);

		$preview_before  = $colors['event_item_before_color'] ? $colors['event_item_before_color'] : '#2d7b83';
		$preview_current = $colors['event_item_current_color'] ? $colors['event_item_current_color'] : $preview_before;
		$preview_card    = $colors['card_background_color'] ? $colors['card_background_color'] : '#ffffff';
		$preview_rail    = $colors['rail_color'] ? $colors['rail_color'] : '#b5c2ce';
		?>
		<div class="stc-color-control-shell" data-stc-color-preview-group>
			<div class="stc-color-controls">
				<p class="stc-admin-form__field stc-color-controls__field">
					<label for="<?php echo esc_attr( $field_prefix . '_before' ); ?>"><?php esc_html_e( 'Event Item Before', 'simple-timeline-collections' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $field_prefix . '_before' ); ?>" name="<?php echo esc_attr( $before_name ); ?>" value="<?php echo esc_attr( $colors['event_item_before_color'] ); ?>" placeholder="#007c89" pattern="^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$" data-stc-color-input="before" data-stc-default-color="#2d7b83" />
					<span class="description"><?php esc_html_e( 'Optional custom hex color for the default event marker dot, including border, fill, and halo. Leave blank to inherit the theme-aware default.', 'simple-timeline-collections' ); ?></span>
				</p>

				<p class="stc-admin-form__field stc-color-controls__field">
					<label for="<?php echo esc_attr( $field_prefix . '_current' ); ?>"><?php esc_html_e( 'Event Item Current', 'simple-timeline-collections' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $field_prefix . '_current' ); ?>" name="<?php echo esc_attr( $current_name ); ?>" value="<?php echo esc_attr( $colors['event_item_current_color'] ); ?>" placeholder="#1a545b" pattern="^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$" data-stc-color-input="current" data-stc-default-color="<?php echo esc_attr( $preview_before ); ?>" />
					<span class="description"><?php esc_html_e( 'Optional custom hex color for the current and last item in the timeline. Leave blank to inherit the event item before color.', 'simple-timeline-collections' ); ?></span>
				</p>

				<p class="stc-admin-form__field stc-color-controls__field">
					<label for="<?php echo esc_attr( $field_prefix . '_rail' ); ?>"><?php esc_html_e( 'Rail Color', 'simple-timeline-collections' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $field_prefix . '_rail' ); ?>" name="<?php echo esc_attr( $rail_name ); ?>" value="<?php echo esc_attr( $colors['rail_color'] ); ?>" placeholder="#b5c2ce" pattern="^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$" data-stc-color-input="rail" data-stc-default-color="#b5c2ce" />
					<span class="description"><?php esc_html_e( 'Optional custom hex color for the dashed timeline rail. Leave blank to inherit the theme-aware default.', 'simple-timeline-collections' ); ?></span>
				</p>

				<p class="stc-admin-form__field stc-color-controls__field">
					<label for="<?php echo esc_attr( $field_prefix . '_card_background' ); ?>"><?php esc_html_e( 'Card Background', 'simple-timeline-collections' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $field_prefix . '_card_background' ); ?>" name="<?php echo esc_attr( $card_background_name ); ?>" value="<?php echo esc_attr( $colors['card_background_color'] ); ?>" placeholder="#ffffff" pattern="^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$" data-stc-color-input="card" data-stc-default-color="#ffffff" />
					<span class="description"><?php esc_html_e( 'Optional custom hex background color for the event card article element: .stc-timeline__card. Leave blank to inherit the theme-aware default.', 'simple-timeline-collections' ); ?></span>
				</p>
			</div>

			<div class="stc-preview-grid" data-stc-preview-grid>
				<section class="stc-preview-card" aria-labelledby="<?php echo esc_attr( $field_prefix . '_preview_colors_heading' ); ?>">
					<h4 id="<?php echo esc_attr( $field_prefix . '_preview_colors_heading' ); ?>"><?php esc_html_e( 'Event Item Colors', 'simple-timeline-collections' ); ?></h4>
					<div class="stc-preview-color-list">
						<div class="stc-preview-color-row">
							<div class="stc-preview-color-row__title"><?php esc_html_e( 'Event Item Before', 'simple-timeline-collections' ); ?></div>
							<div class="stc-preview-color-chip">
								<span class="stc-preview-color-chip__swatch" data-stc-color-swatch="before" style="background-color: <?php echo esc_attr( $preview_before ); ?>;"></span>
								<span class="stc-preview-color-chip__value" data-stc-color-value="before"><?php echo esc_html( $preview_before ); ?></span>
								<code class="stc-preview-color-chip__token">--stc-event-item-before-color</code>
							</div>
							<p class="description"><?php esc_html_e( 'Default marker border, fill, and halo', 'simple-timeline-collections' ); ?></p>
						</div>
						<div class="stc-preview-color-row">
							<div class="stc-preview-color-row__title"><?php esc_html_e( 'Event Item Current', 'simple-timeline-collections' ); ?></div>
							<div class="stc-preview-color-chip">
								<span class="stc-preview-color-chip__swatch" data-stc-color-swatch="current" style="background-color: <?php echo esc_attr( $preview_current ); ?>;"></span>
								<span class="stc-preview-color-chip__value" data-stc-color-value="current"><?php echo esc_html( $preview_current ); ?></span>
								<code class="stc-preview-color-chip__token">--stc-event-item-current-color</code>
							</div>
							<p class="description"><?php esc_html_e( 'Current/last marker', 'simple-timeline-collections' ); ?></p>
						</div>
						<div class="stc-preview-color-row">
							<div class="stc-preview-color-row__title"><?php esc_html_e( 'Rail Color', 'simple-timeline-collections' ); ?></div>
							<div class="stc-preview-color-chip">
								<span class="stc-preview-color-chip__swatch" data-stc-color-swatch="rail" style="background-color: <?php echo esc_attr( $preview_rail ); ?>;"></span>
								<span class="stc-preview-color-chip__value" data-stc-color-value="rail"><?php echo esc_html( $preview_rail ); ?></span>
								<code class="stc-preview-color-chip__token">--stc-rail-color</code>
							</div>
							<p class="description"><?php esc_html_e( 'Dashed timeline rail', 'simple-timeline-collections' ); ?></p>
						</div>
						<div class="stc-preview-color-row">
							<div class="stc-preview-color-row__title"><?php esc_html_e( 'Card Background', 'simple-timeline-collections' ); ?></div>
							<div class="stc-preview-color-chip">
								<span class="stc-preview-color-chip__swatch stc-preview-color-chip__swatch--light" data-stc-color-swatch="card" style="background-color: <?php echo esc_attr( $preview_card ); ?>;"></span>
								<span class="stc-preview-color-chip__value" data-stc-color-value="card"><?php echo esc_html( $preview_card ); ?></span>
								<code class="stc-preview-color-chip__token">--stc-card-background-color</code>
							</div>
							<p class="description"><?php esc_html_e( 'Event card article background', 'simple-timeline-collections' ); ?></p>
						</div>
					</div>
				</section>

				<section class="stc-preview-card" aria-labelledby="<?php echo esc_attr( $field_prefix . '_preview_map_heading' ); ?>">
					<h4 id="<?php echo esc_attr( $field_prefix . '_preview_map_heading' ); ?>"><?php esc_html_e( 'Rendered selector map', 'simple-timeline-collections' ); ?></h4>
					<div class="stc-selector-map" data-stc-selector-map style="--stc-preview-before: <?php echo esc_attr( $preview_before ); ?>; --stc-preview-current: <?php echo esc_attr( $preview_current ); ?>; --stc-preview-card-bg: <?php echo esc_attr( $preview_card ); ?>; --stc-preview-rail: <?php echo esc_attr( $preview_rail ); ?>;">
						<div class="stc-selector-map__rail" aria-hidden="true">
							<span class="stc-selector-map__marker stc-selector-map__marker--before"></span>
							<span class="stc-selector-map__marker stc-selector-map__marker--card"></span>
							<span class="stc-selector-map__marker stc-selector-map__marker--current"></span>
						</div>
						<div class="stc-selector-map__cards">
							<div class="stc-selector-card">
								<strong><?php esc_html_e( 'Default marker', 'simple-timeline-collections' ); ?></strong>
								<code>.stc-timeline__item::before</code>
							</div>
							<div class="stc-selector-card">
								<strong><?php esc_html_e( 'Timeline rail', 'simple-timeline-collections' ); ?></strong>
								<code>.stc-timeline__item::after</code>
							</div>
							<div class="stc-selector-card stc-selector-card--card" data-stc-selector-card-preview>
								<strong><?php esc_html_e( 'Event card', 'simple-timeline-collections' ); ?></strong>
								<code>.stc-timeline__card</code>
							</div>
							<div class="stc-selector-card">
								<strong><?php esc_html_e( 'Current marker', 'simple-timeline-collections' ); ?></strong>
								<code>.stc-timeline__item.is-current::before</code>
							</div>
						</div>
					</div>
					<div class="stc-preview-card__footer">
						<strong><?php esc_html_e( 'Advanced path', 'simple-timeline-collections' ); ?></strong>
						<p class="description"><?php esc_html_e( 'A future Advanced Custom CSS field can target these stc-prefixed selectors without adding settings for every possible style need.', 'simple-timeline-collections' ); ?></p>
					</div>
				</section>
			</div>
		</div>
		<?php
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap stc-settings-page">
			<h1><?php esc_html_e( 'Simple Timeline Collections Settings', 'simple-timeline-collections' ); ?></h1>
			<p class="stc-settings-page__intro">
				<?php esc_html_e( 'Manage global fallback behavior here. Create and configure individual timelines from Simple Timeline Collections > All Timelines.', 'simple-timeline-collections' ); ?>
			</p>

			<div class="stc-settings-card">
				<form action="options.php" method="post">
					<?php
					settings_fields( 'stc_timeline_settings' );
					do_settings_sections( 'stc-timeline-settings' );
					submit_button( __( 'Save Timeline Settings', 'simple-timeline-collections' ) );
					?>
				</form>
			</div>

			<div class="stc-settings-card stc-settings-card--secondary">
				<h2><?php esc_html_e( 'Timeline Shortcode Usage', 'simple-timeline-collections' ); ?></h2>
				<p><?php esc_html_e( 'Use the default timeline:', 'simple-timeline-collections' ); ?></p>
				<code>[simple_timeline_collections]</code>
				<p><?php esc_html_e( 'Use a specific timeline by ID:', 'simple-timeline-collections' ); ?></p>
				<code>[simple_timeline_collections id="123"]</code>
				<p><?php esc_html_e( 'Use a specific timeline by slug:', 'simple-timeline-collections' ); ?></p>
				<code>[simple_timeline_collections timeline="program-history"]</code>
				<p><?php esc_html_e( 'Override event item colors and card background with custom hex values:', 'simple-timeline-collections' ); ?></p>
				<code>[simple_timeline_collections timeline="program-history" event_item_before="#007c89" event_item_current="#1a545b" card_background="#ffffff"]</code>
				<p class="description"><?php esc_html_e( 'A timeline-specific shortcode is also displayed on each Timeline edit screen and in the Timelines list table.', 'simple-timeline-collections' ); ?></p>
			</div>
		</div>
		<?php
	}

	public function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=' . self::TIMELINE_POST_TYPE . '&page=stc-timeline-settings' ) ),
			esc_html__( 'Settings', 'simple-timeline-collections' )
		);

		array_unshift( $links, $settings_link );
		return $links;
	}

	public function add_meta_boxes() {
		add_meta_box(
			'stc_timeline_settings',
			__( 'Timeline Settings', 'simple-timeline-collections' ),
			array( $this, 'render_timeline_settings_meta_box' ),
			self::TIMELINE_POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'stc_timeline_usage',
			__( 'Shortcode & Events', 'simple-timeline-collections' ),
			array( $this, 'render_timeline_usage_meta_box' ),
			self::TIMELINE_POST_TYPE,
			'side',
			'default'
		);

		add_meta_box(
			'stc_event_details',
			__( 'Timeline Event Details', 'simple-timeline-collections' ),
			array( $this, 'render_event_details_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_timeline_settings_meta_box( $post ) {
		wp_nonce_field( self::TIMELINE_NONCE_ACTION, self::TIMELINE_NONCE_NAME );

		$options             = $this->get_options();
		$display_title       = get_post_meta( $post->ID, self::META_TIMELINE_TITLE, true );
		$title_alignment     = get_post_meta( $post->ID, self::META_TIMELINE_TITLE_ALIGNMENT, true );
		$layout              = get_post_meta( $post->ID, self::META_TIMELINE_LAYOUT, true );
		$alternate_card_gap  = get_post_meta( $post->ID, self::META_TIMELINE_ALTERNATE_CARD_GAP, true );
		$event_item_before_color   = get_post_meta( $post->ID, self::META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX, true );
		$event_item_current_color  = get_post_meta( $post->ID, self::META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX, true );
		$card_background_color     = get_post_meta( $post->ID, self::META_TIMELINE_CARD_BACKGROUND_COLOR_HEX, true );
		$rail_color                = get_post_meta( $post->ID, self::META_TIMELINE_RAIL_COLOR_HEX, true );

		if ( '' === $display_title ) {
			$display_title = get_the_title( $post ) ? get_the_title( $post ) : $options['timeline_title'];
		}

		if ( ! array_key_exists( $title_alignment, $this->get_allowed_title_alignments() ) ) {
			$title_alignment = $options['title_alignment'];
		}

		if ( ! array_key_exists( $layout, $this->get_allowed_layouts() ) ) {
			$layout = $options['layout'];
		}

		$alternate_card_gap = $this->normalize_alternate_card_gap( $alternate_card_gap, $options['alternate_card_gap'] );

		$event_item_colors = $this->normalize_event_item_color_settings(
			$event_item_before_color,
			$event_item_current_color,
			$card_background_color,
			$rail_color,
			$options
		);
		?>
		<div class="stc-admin-form" aria-describedby="stc-timeline-settings-help">
			<p id="stc-timeline-settings-help" class="description">
				<?php esc_html_e( 'Configure this specific timeline. Events assigned to this timeline will use these settings unless the shortcode overrides them.', 'simple-timeline-collections' ); ?>
			</p>

			<p class="stc-admin-form__field">
				<label for="stc_timeline_display_title"><?php esc_html_e( 'Timeline Display Title', 'simple-timeline-collections' ); ?></label>
				<input type="text" id="stc_timeline_display_title" name="stc_timeline_display_title" value="<?php echo esc_attr( $display_title ); ?>" class="regular-text" />
				<span class="description"><?php esc_html_e( 'This appears above the timeline. Leave blank to use the Timeline post title.', 'simple-timeline-collections' ); ?></span>
			</p>

			<div class="stc-admin-form__fieldset" role="group" aria-labelledby="stc-title-align-heading">
				<h3 id="stc-title-align-heading"><?php esc_html_e( 'Timeline Title Alignment', 'simple-timeline-collections' ); ?></h3>
				<fieldset class="stc-settings-radio-group stc-settings-radio-group--inline">
					<legend class="screen-reader-text"><?php esc_html_e( 'Choose timeline title alignment', 'simple-timeline-collections' ); ?></legend>
					<?php foreach ( $this->get_allowed_title_alignments() as $value => $label ) : ?>
						<label class="stc-settings-radio-option" for="stc_timeline_meta_title_alignment_<?php echo esc_attr( $value ); ?>">
							<input
								type="radio"
								id="stc_timeline_meta_title_alignment_<?php echo esc_attr( $value ); ?>"
								name="stc_timeline_title_alignment"
								value="<?php echo esc_attr( $value ); ?>"
								<?php checked( $title_alignment, $value ); ?>
							/>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
			</div>

			<div class="stc-admin-form__fieldset" role="group" aria-labelledby="stc-event-align-heading">
				<h3 id="stc-event-align-heading"><?php esc_html_e( 'Event Alignment', 'simple-timeline-collections' ); ?></h3>
				<fieldset class="stc-settings-radio-group">
					<legend class="screen-reader-text"><?php esc_html_e( 'Choose timeline event alignment', 'simple-timeline-collections' ); ?></legend>
					<?php foreach ( $this->get_allowed_layouts() as $value => $label ) : ?>
						<label class="stc-settings-radio-option" for="stc_timeline_meta_layout_<?php echo esc_attr( $value ); ?>">
							<input
								type="radio"
								id="stc_timeline_meta_layout_<?php echo esc_attr( $value ); ?>"
								name="stc_timeline_layout"
								value="<?php echo esc_attr( $value ); ?>"
								<?php checked( $layout, $value ); ?>
							/>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<p class="description"><?php esc_html_e( 'On small screens, all layout modes collapse to a single vertical timeline.', 'simple-timeline-collections' ); ?></p>

				<div class="stc-admin-form__subfield" role="group" aria-labelledby="stc-alternate-gap-heading">
					<h4 id="stc-alternate-gap-heading"><?php esc_html_e( 'Alternating Layout Spacing', 'simple-timeline-collections' ); ?></h4>
					<?php
					$this->render_alternate_card_gap_control(
						'stc_timeline_alternate_card_gap',
						'stc_timeline_meta_alternate_card_gap',
						$alternate_card_gap
					);
					?>
				</div>
			</div>

			<div class="stc-admin-form__fieldset" role="group" aria-labelledby="stc-event-item-color-heading">
				<h3 id="stc-event-item-color-heading"><?php esc_html_e( 'Event Item Colors', 'simple-timeline-collections' ); ?></h3>
				<?php
				$this->render_event_item_color_controls(
					'stc_timeline_event_item_before_color',
					'stc_timeline_event_item_current_color',
					'stc_timeline_card_background_color',
					'stc_timeline_rail_color',
					'stc_timeline_meta_event_item_color',
					$event_item_colors
				);
				?>
				<p class="description"><?php esc_html_e( 'Use custom hex colors for the default marker, current/last marker, dashed rail, and card background for this timeline.', 'simple-timeline-collections' ); ?></p>
			</div>
		</div>
		<?php
	}

	public function render_timeline_usage_meta_box( $post ) {
		if ( 'auto-draft' === $post->post_status ) {
			?>
			<p><?php esc_html_e( 'Save this timeline to generate its shortcode and start adding events.', 'simple-timeline-collections' ); ?></p>
			<?php
			return;
		}

		$timeline_id = absint( $post->ID );
		$slug        = $post->post_name;
		$add_url     = $this->get_add_event_url_for_timeline( $timeline_id );
		$events      = $this->get_events_for_timeline( $timeline_id, 5 );
		?>
		<p><strong><?php esc_html_e( 'Shortcode by ID', 'simple-timeline-collections' ); ?></strong></p>
		<code>[simple_timeline_collections id="<?php echo esc_attr( $timeline_id ); ?>"]</code>

		<?php if ( $slug ) : ?>
			<p><strong><?php esc_html_e( 'Shortcode by slug', 'simple-timeline-collections' ); ?></strong></p>
			<code>[simple_timeline_collections timeline="<?php echo esc_attr( $slug ); ?>"]</code>
		<?php endif; ?>

		<p>
			<a class="button button-secondary" href="<?php echo esc_url( $add_url ); ?>">
				<?php esc_html_e( 'Add Event to This Timeline', 'simple-timeline-collections' ); ?>
			</a>
		</p>

		<?php if ( $events ) : ?>
			<p><strong><?php esc_html_e( 'Recent events', 'simple-timeline-collections' ); ?></strong></p>
			<ul class="stc-admin-event-list">
				<?php foreach ( $events as $event_id ) : ?>
					<li><a href="<?php echo esc_url( get_edit_post_link( $event_id ) ); ?>"><?php echo esc_html( get_the_title( $event_id ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'No events have been assigned to this timeline yet.', 'simple-timeline-collections' ); ?></p>
		<?php endif; ?>
		<?php
	}

	public function render_event_details_meta_box( $post ) {
		wp_nonce_field( self::EVENT_NONCE_ACTION, self::EVENT_NONCE_NAME );

		$event_date        = get_post_meta( $post->ID, self::META_DATE, true );
		$event_desc        = get_post_meta( $post->ID, self::META_DESC, true );
		$event_cta_url     = get_post_meta( $post->ID, self::META_CTA_URL, true );
		$event_cta_text    = get_post_meta( $post->ID, self::META_CTA_TEXT, true );
		$event_cta_new     = get_post_meta( $post->ID, self::META_CTA_NEW, true );
		$event_timeline_id = get_post_meta( $post->ID, self::META_EVENT_TIMELINE, true );

		if ( ! $event_timeline_id && isset( $_GET['stc_timeline_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$requested_timeline_id = absint( wp_unslash( $_GET['stc_timeline_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $this->is_valid_timeline_id( $requested_timeline_id ) ) {
				$event_timeline_id = $requested_timeline_id;
			}
		}

		if ( ! $event_timeline_id ) {
			$event_timeline_id = $this->get_default_timeline_id();
		}

		$timelines = $this->get_timeline_posts();
		?>
		<div class="stc-admin-form" aria-describedby="stc-event-help">
			<p id="stc-event-help" class="description">
				<?php esc_html_e( 'Assign this event to a timeline, then add the event date, description, and optional call-to-action.', 'simple-timeline-collections' ); ?>
			</p>

			<p class="stc-admin-form__field">
				<label for="stc_event_timeline_id">
					<?php esc_html_e( 'Timeline', 'simple-timeline-collections' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<select id="stc_event_timeline_id" name="stc_event_timeline_id" required>
					<option value=""><?php esc_html_e( 'Choose timeline', 'simple-timeline-collections' ); ?></option>
					<?php foreach ( $timelines as $timeline ) : ?>
						<option value="<?php echo esc_attr( $timeline->ID ); ?>" <?php selected( absint( $event_timeline_id ), $timeline->ID ); ?>>
							<?php echo esc_html( get_the_title( $timeline ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( empty( $timelines ) ) : ?>
					<span class="description"><?php esc_html_e( 'Create a timeline before adding events.', 'simple-timeline-collections' ); ?></span>
				<?php else : ?>
					<span class="description"><?php esc_html_e( 'The event displays only inside the selected timeline collection.', 'simple-timeline-collections' ); ?></span>
				<?php endif; ?>
			</p>

			<p class="stc-admin-form__field">
				<label for="stc_event_date">
					<?php esc_html_e( 'Event Date', 'simple-timeline-collections' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<input type="date" id="stc_event_date" name="stc_event_date" value="<?php echo esc_attr( $event_date ); ?>" required />
				<span class="description"><?php esc_html_e( 'Used to sort and display the timeline.', 'simple-timeline-collections' ); ?></span>
			</p>

			<p class="stc-admin-form__field">
				<label for="stc_event_description">
					<?php esc_html_e( 'Event Description', 'simple-timeline-collections' ); ?>
					<span class="required" aria-hidden="true">*</span>
				</label>
				<textarea id="stc_event_description" name="stc_event_description" rows="7" required><?php echo esc_textarea( $event_desc ); ?></textarea>
				<span class="description"><?php esc_html_e( 'Short, plain-language summary. Basic formatting is allowed on output.', 'simple-timeline-collections' ); ?></span>
			</p>

			<div class="stc-admin-form__fieldset" role="group" aria-labelledby="stc-cta-heading">
				<h3 id="stc-cta-heading"><?php esc_html_e( 'Optional Event CTA', 'simple-timeline-collections' ); ?></h3>

				<p class="stc-admin-form__field">
					<label for="stc_event_cta_url"><?php esc_html_e( 'CTA URL', 'simple-timeline-collections' ); ?></label>
					<input type="url" id="stc_event_cta_url" name="stc_event_cta_url" value="<?php echo esc_url( $event_cta_url ); ?>" placeholder="https://example.com" />
				</p>

				<p class="stc-admin-form__field">
					<label for="stc_event_cta_text"><?php esc_html_e( 'CTA Text', 'simple-timeline-collections' ); ?></label>
					<input type="text" id="stc_event_cta_text" name="stc_event_cta_text" value="<?php echo esc_attr( $event_cta_text ); ?>" placeholder="<?php esc_attr_e( 'Learn more', 'simple-timeline-collections' ); ?>" />
					<span class="description"><?php esc_html_e( 'Leave blank to use “Learn more.”', 'simple-timeline-collections' ); ?></span>
				</p>

				<p class="stc-admin-form__checkbox">
					<input type="checkbox" id="stc_event_cta_new_tab" name="stc_event_cta_new_tab" value="1" <?php checked( '1', $event_cta_new ); ?> />
					<label for="stc_event_cta_new_tab"><?php esc_html_e( 'Open CTA in a new tab', 'simple-timeline-collections' ); ?></label>
				</p>
			</div>
		</div>
		<?php
	}

	public function save_timeline_meta( $post_id ) {
		if ( ! isset( $_POST[ self::TIMELINE_NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::TIMELINE_NONCE_NAME ] ) ), self::TIMELINE_NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$display_title = isset( $_POST['stc_timeline_display_title'] ) ? sanitize_text_field( wp_unslash( $_POST['stc_timeline_display_title'] ) ) : '';
		$display_title = trim( $display_title );

		$title_alignment          = isset( $_POST['stc_timeline_title_alignment'] ) ? sanitize_key( wp_unslash( $_POST['stc_timeline_title_alignment'] ) ) : '';
		$allowed_title_alignments = $this->get_allowed_title_alignments();
		if ( ! array_key_exists( $title_alignment, $allowed_title_alignments ) ) {
			$title_alignment = $this->get_options()['title_alignment'];
		}

		$layout          = isset( $_POST['stc_timeline_layout'] ) ? sanitize_key( wp_unslash( $_POST['stc_timeline_layout'] ) ) : '';
		$allowed_layouts = $this->get_allowed_layouts();
		if ( ! array_key_exists( $layout, $allowed_layouts ) ) {
			$layout = $this->get_options()['layout'];
		}

		$options            = $this->get_options();
		$alternate_card_gap = $this->normalize_alternate_card_gap(
			isset( $_POST['stc_timeline_alternate_card_gap'] ) ? wp_unslash( $_POST['stc_timeline_alternate_card_gap'] ) : $options['alternate_card_gap'],
			$options['alternate_card_gap']
		);

		$posted_event_item_before_color = isset( $_POST['stc_timeline_event_item_before_color'] )
			? sanitize_hex_color( wp_unslash( $_POST['stc_timeline_event_item_before_color'] ) )
			: $options['event_item_before_color'];

		$posted_event_item_current_color = isset( $_POST['stc_timeline_event_item_current_color'] )
			? sanitize_hex_color( wp_unslash( $_POST['stc_timeline_event_item_current_color'] ) )
			: $options['event_item_current_color'];

		$posted_card_background_color = isset( $_POST['stc_timeline_card_background_color'] )
			? sanitize_hex_color( wp_unslash( $_POST['stc_timeline_card_background_color'] ) )
			: $options['card_background_color'];

		$posted_rail_color = isset( $_POST['stc_timeline_rail_color'] )
			? sanitize_hex_color( wp_unslash( $_POST['stc_timeline_rail_color'] ) )
			: $options['rail_color'];

		$event_item_colors = $this->normalize_event_item_color_settings(
			$posted_event_item_before_color,
			$posted_event_item_current_color,
			$posted_card_background_color,
			$posted_rail_color,
			$options
		);

		$this->update_or_delete_meta( $post_id, self::META_TIMELINE_TITLE, $display_title );
		update_post_meta( $post_id, self::META_TIMELINE_TITLE_ALIGNMENT, $title_alignment );
		update_post_meta( $post_id, self::META_TIMELINE_LAYOUT, $layout );
		update_post_meta( $post_id, self::META_TIMELINE_ALTERNATE_CARD_GAP, $alternate_card_gap );
		$this->update_or_delete_meta( $post_id, self::META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX, $event_item_colors['event_item_before_color'] );
		$this->update_or_delete_meta( $post_id, self::META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX, $event_item_colors['event_item_current_color'] );
		$this->update_or_delete_meta( $post_id, self::META_TIMELINE_CARD_BACKGROUND_COLOR_HEX, $event_item_colors['card_background_color'] );
		$this->update_or_delete_meta( $post_id, self::META_TIMELINE_RAIL_COLOR_HEX, $event_item_colors['rail_color'] );
	}

	public function save_event_meta( $post_id ) {
		if ( ! isset( $_POST[ self::EVENT_NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::EVENT_NONCE_NAME ] ) ), self::EVENT_NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$date = isset( $_POST['stc_event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['stc_event_date'] ) ) : '';
		if ( $date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date = '';
		}

		$timeline_id = isset( $_POST['stc_event_timeline_id'] ) ? absint( wp_unslash( $_POST['stc_event_timeline_id'] ) ) : 0;
		if ( ! $this->is_valid_timeline_id( $timeline_id ) ) {
			$timeline_id = $this->get_default_timeline_id();
		}

		$description = isset( $_POST['stc_event_description'] ) ? wp_kses_post( wp_unslash( $_POST['stc_event_description'] ) ) : '';
		$cta_url     = isset( $_POST['stc_event_cta_url'] ) ? esc_url_raw( wp_unslash( $_POST['stc_event_cta_url'] ) ) : '';
		$cta_text    = isset( $_POST['stc_event_cta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['stc_event_cta_text'] ) ) : '';
		$cta_new     = isset( $_POST['stc_event_cta_new_tab'] ) ? '1' : '0';

		$this->update_or_delete_meta( $post_id, self::META_DATE, $date );
		$this->update_or_delete_meta( $post_id, self::META_DESC, $description );
		$this->update_or_delete_meta( $post_id, self::META_CTA_URL, $cta_url );
		$this->update_or_delete_meta( $post_id, self::META_CTA_TEXT, $cta_text );
		$this->update_or_delete_meta( $post_id, self::META_CTA_NEW, $cta_new );

		if ( $timeline_id ) {
			update_post_meta( $post_id, self::META_EVENT_TIMELINE, $timeline_id );
		} else {
			delete_post_meta( $post_id, self::META_EVENT_TIMELINE );
		}
	}

	private function update_or_delete_meta( $post_id, $meta_key, $value ) {
		if ( '' === $value || null === $value ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $value );
	}

	public function custom_title_placeholder( $title, $post ) {
		if ( isset( $post->post_type ) && self::POST_TYPE === $post->post_type ) {
			return __( 'Event title', 'simple-timeline-collections' );
		}

		if ( isset( $post->post_type ) && self::TIMELINE_POST_TYPE === $post->post_type ) {
			return __( 'Timeline name', 'simple-timeline-collections' );
		}

		return $title;
	}

	public function register_frontend_assets() {
		wp_enqueue_style(
			'simple-timeline-collections',
			plugin_dir_url( __FILE__ ) . 'assets/css/simple-timeline-collections.css',
			array(),
			self::VERSION
		);

		wp_register_script(
			'simple-timeline-collections',
			plugin_dir_url( __FILE__ ) . 'assets/js/simple-timeline-collections.js',
			array(),
			self::VERSION,
			true
		);
	}

	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$is_plugin_screen   = in_array( $screen->post_type, array( self::POST_TYPE, self::TIMELINE_POST_TYPE ), true );
		$is_settings_screen = 'stc_timeline_page_stc-timeline-settings' === $screen->id;

		if ( ! $is_plugin_screen && ! $is_settings_screen ) {
			return;
		}

		wp_enqueue_style(
			'simple-timeline-collections-admin',
			plugin_dir_url( __FILE__ ) . 'assets/css/simple-timeline-collections-admin.css',
			array(),
			self::VERSION
		);


		wp_enqueue_script(
			'simple-timeline-collections-admin',
			plugin_dir_url( __FILE__ ) . 'assets/js/simple-timeline-collections-admin.js',
			array(),
			self::VERSION,
			true
		);
	}

	private function current_user_can_add_timeline_event() {
		return current_user_can( 'edit_others_posts' );
	}

	private function get_add_event_url_for_timeline( $timeline_id ) {
		$timeline_id = absint( $timeline_id );

		if ( ! $timeline_id || ! $this->is_valid_timeline_id( $timeline_id ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'post_type'       => self::POST_TYPE,
				'stc_timeline_id' => $timeline_id,
			),
			admin_url( 'post-new.php' )
		);
	}

	public function render_shortcode( $atts ) {
		$options = $this->get_options();

		$atts = shortcode_atts(
			array(
				'id'            => '',
				'timeline'      => '',
				'heading'       => '',
				'heading_level' => '2',
				'heading_align' => '',
				'layout'             => '',
				'event_item_before'  => '',
				'event_item_current' => '',
				'card_background'    => '',
				'rail_color'         => '',
				'alternate_card_gap' => '',
				'limit'              => '-1',
				'order'         => 'ASC',
				'show_heading'  => 'true',
				'class'         => '',
			),
			$atts,
			'simple_timeline_collections'
		);

		$timeline_requested = '' !== trim( (string) $atts['id'] ) || '' !== trim( (string) $atts['timeline'] );
		$timeline_id        = $this->resolve_timeline_id( $atts['id'], $atts['timeline'] );

		if ( ! $timeline_id && ! $timeline_requested ) {
			$timeline_id = $this->get_default_timeline_id();
		}

		if ( $timeline_requested && ! $timeline_id ) {
			return current_user_can( 'edit_posts' ) ? '<p class="stc-timeline-empty">' . esc_html__( 'Requested timeline not found.', 'simple-timeline-collections' ) . '</p>' : '';
		}

		$allowed_layouts          = $this->get_allowed_layouts();
		$allowed_title_alignments = $this->get_allowed_title_alignments();
		$timeline_settings        = $timeline_id ? $this->get_timeline_settings( $timeline_id ) : $options;
		$layout                   = '' !== trim( $atts['layout'] ) ? sanitize_key( $atts['layout'] ) : $timeline_settings['layout'];
		$layout                   = array_key_exists( $layout, $allowed_layouts ) ? $layout : $timeline_settings['layout'];
		$title_alignment          = '' !== trim( $atts['heading_align'] ) ? sanitize_key( $atts['heading_align'] ) : $timeline_settings['title_alignment'];
		$title_alignment          = array_key_exists( $title_alignment, $allowed_title_alignments ) ? $title_alignment : $timeline_settings['title_alignment'];
		$event_item_before_color  = '' !== trim( (string) $atts['event_item_before'] ) ? $atts['event_item_before'] : $timeline_settings['event_item_before_color'];
		$event_item_current_color = '' !== trim( (string) $atts['event_item_current'] ) ? $atts['event_item_current'] : $timeline_settings['event_item_current_color'];
		$card_background_color    = '' !== trim( (string) $atts['card_background'] ) ? $atts['card_background'] : $timeline_settings['card_background_color'];
		$rail_color               = '' !== trim( (string) $atts['rail_color'] ) ? $atts['rail_color'] : $timeline_settings['rail_color'];
		$alternate_card_gap       = '' !== trim( (string) $atts['alternate_card_gap'] ) ? $atts['alternate_card_gap'] : $timeline_settings['alternate_card_gap'];
		$alternate_card_gap       = $this->normalize_alternate_card_gap( $alternate_card_gap, $timeline_settings['alternate_card_gap'] );

		$event_item_colors = $this->normalize_event_item_color_settings(
			$event_item_before_color,
			$event_item_current_color,
			$card_background_color,
			$rail_color,
			$timeline_settings
		);
		$timeline_style_attr = trim( $this->get_timeline_color_style_attr( $event_item_colors ) . ' --stc-alternate-card-gap: ' . absint( $alternate_card_gap ) . 'px;' );
		$heading             = '' !== trim( $atts['heading'] ) ? sanitize_text_field( $atts['heading'] ) : $timeline_settings['timeline_title'];
		$limit                    = intval( $atts['limit'] );
		$order                    = strtoupper( sanitize_key( $atts['order'] ) );
		$order                    = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';
		$heading_level            = min( 6, max( 2, intval( $atts['heading_level'] ) ) );
		$show_heading             = filter_var( $atts['show_heading'], FILTER_VALIDATE_BOOLEAN );
		$extra_class              = sanitize_html_class( $atts['class'] );

		$query_args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => self::META_DATE,
			'orderby'        => 'meta_value',
			'order'          => $order,
			'meta_query'     => array(
				array(
					'key'     => self::META_DATE,
					'compare' => 'EXISTS',
				),
			),
		);

		if ( $timeline_id ) {
			$query_args['meta_query'][] = array(
				'key'     => self::META_EVENT_TIMELINE,
				'value'   => $timeline_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return current_user_can( 'edit_posts' ) ? '<p class="stc-timeline-empty">' . esc_html__( 'No timeline events found for this timeline yet.', 'simple-timeline-collections' ) . '</p>' : '';
		}

		$heading_id     = 'stc-timeline-heading-' . wp_unique_id();
		$timeline_class = $timeline_id ? ' stc-timeline-wrap--timeline-' . absint( $timeline_id ) : '';
		$compact_class  = 'alternate' === $layout ? ' stc-timeline-wrap--compact-alternate' : '';
		$classes        = trim( 'stc-timeline-wrap stc-timeline-wrap--layout-' . $layout . ' stc-timeline-wrap--title-align-' . $title_alignment . $timeline_class . $compact_class . ' ' . $extra_class );
		$posts          = $query->posts;
		$total          = count( $posts );
		$timeline_slug  = $timeline_id ? get_post_field( 'post_name', $timeline_id ) : '';
		$add_event_url  = ( $timeline_id && $this->current_user_can_add_timeline_event() ) ? $this->get_add_event_url_for_timeline( $timeline_id ) : '';

		wp_enqueue_script( 'simple-timeline-collections' );

		ob_start();
		?>
		<section class="<?php echo esc_attr( $classes ); ?>" <?php echo $timeline_style_attr ? 'style="' . esc_attr( $timeline_style_attr ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-timeline-id="<?php echo esc_attr( $timeline_id ); ?>" data-timeline-slug="<?php echo esc_attr( $timeline_slug ); ?>" data-timeline-layout="<?php echo esc_attr( $layout ); ?>" data-title-alignment="<?php echo esc_attr( $title_alignment ); ?>" data-stc-alternate-card-gap="<?php echo esc_attr( $alternate_card_gap ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
			<?php if ( $show_heading ) : ?>
				<h<?php echo esc_attr( $heading_level ); ?> id="<?php echo esc_attr( $heading_id ); ?>" class="stc-timeline-heading">
					<?php echo esc_html( $heading ); ?>
				</h<?php echo esc_attr( $heading_level ); ?>>
			<?php else : ?>
				<span id="<?php echo esc_attr( $heading_id ); ?>" class="screen-reader-text"><?php echo esc_html( $heading ); ?></span>
			<?php endif; ?>

			<ol class="stc-timeline" role="list">
				<?php foreach ( $posts as $index => $post ) : ?>
					<?php
					$event_date     = get_post_meta( $post->ID, self::META_DATE, true );
					$event_desc     = get_post_meta( $post->ID, self::META_DESC, true );
					$event_cta_url  = get_post_meta( $post->ID, self::META_CTA_URL, true );
					$event_cta_text = get_post_meta( $post->ID, self::META_CTA_TEXT, true );
					$event_cta_new  = get_post_meta( $post->ID, self::META_CTA_NEW, true );
					$item_class     = ( $index + 1 === $total ) ? 'stc-timeline__item is-current' : 'stc-timeline__item';
					$item_side      = 'alternate' === $layout ? ( 0 === $index % 2 ? 'right' : 'left' ) : $layout;
					$timestamp      = $event_date ? strtotime( $event_date ) : false;
					$display_date      = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '';
					$cta_text          = $event_cta_text ? $event_cta_text : __( 'Learn more', 'simple-timeline-collections' );
					$edit_event_url    = ( $this->current_user_can_add_timeline_event() && current_user_can( 'edit_post', $post->ID ) ) ? get_edit_post_link( $post->ID, '' ) : '';
					$delete_event_url  = ( $this->current_user_can_add_timeline_event() && current_user_can( 'delete_post', $post->ID ) ) ? get_delete_post_link( $post->ID ) : '';
					$has_event_actions = $edit_event_url || $delete_event_url;
					$actions_toggle_id = wp_unique_id( 'stc-event-actions-toggle-' );
					$actions_menu_id   = wp_unique_id( 'stc-event-actions-menu-' );
					$event_title       = get_the_title( $post );
					?>
					<li class="<?php echo esc_attr( $item_class ); ?>" data-stc-timeline-side="<?php echo esc_attr( $item_side ); ?>">
						<article class="stc-timeline__card">
							<?php if ( $display_date || $has_event_actions ) : ?>
								<div class="stc-timeline__meta-row">
									<?php if ( $display_date ) : ?>
										<time class="stc-timeline__date" datetime="<?php echo esc_attr( $event_date ); ?>">
											<?php echo esc_html( $display_date ); ?>
										</time>
									<?php endif; ?>

									<?php if ( $has_event_actions ) : ?>
										<div class="stc-timeline__actions" data-stc-event-actions>
											<button
												type="button"
												id="<?php echo esc_attr( $actions_toggle_id ); ?>"
												class="stc-timeline__actions-toggle"
												aria-haspopup="menu"
												aria-expanded="false"
												aria-controls="<?php echo esc_attr( $actions_menu_id ); ?>"
												aria-label="<?php echo esc_attr( sprintf(
													/* translators: %s: timeline event title. */
													__( 'Event actions for %s', 'simple-timeline-collections' ),
													$event_title
												) ); ?>"
											>
												<span aria-hidden="true">&#8942;</span>
											</button>

											<ul id="<?php echo esc_attr( $actions_menu_id ); ?>" class="stc-timeline__actions-menu" role="menu" aria-labelledby="<?php echo esc_attr( $actions_toggle_id ); ?>" hidden>
												<?php if ( $edit_event_url ) : ?>
													<li role="none">
														<a role="menuitem" href="<?php echo esc_url( $edit_event_url ); ?>">
															<?php esc_html_e( 'Edit', 'simple-timeline-collections' ); ?>
														</a>
													</li>
												<?php endif; ?>

												<?php if ( $delete_event_url ) : ?>
													<li role="none">
														<a role="menuitem" class="is-danger" href="<?php echo esc_url( $delete_event_url ); ?>" data-stc-confirm="<?php echo esc_attr( sprintf(
															/* translators: %s: timeline event title. */
															__( 'Move “%s” to the Trash?', 'simple-timeline-collections' ),
															$event_title
														) ); ?>">
															<?php esc_html_e( 'Delete', 'simple-timeline-collections' ); ?>
														</a>
													</li>
												<?php endif; ?>
											</ul>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<h<?php echo esc_attr( min( 6, $heading_level + 1 ) ); ?> class="stc-timeline__title">
								<?php echo esc_html( $event_title ); ?>
							</h<?php echo esc_attr( min( 6, $heading_level + 1 ) ); ?>>

							<?php if ( $event_desc ) : ?>
								<div class="stc-timeline__description">
									<?php echo wp_kses_post( wpautop( $event_desc ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( $event_cta_url ) : ?>
						<?php
						$cta_aria_label = sprintf(
							/* translators: 1: Call-to-action link text, 2: timeline event title. */
							__( '%1$s: %2$s', 'simple-timeline-collections' ),
							$cta_text,
							$event_title
						);
						?>
						<p class="stc-timeline__cta-wrap">
							<a class="stc-timeline__cta wp-element-button" href="<?php echo esc_url( $event_cta_url ); ?>" aria-label="<?php echo esc_attr( $cta_aria_label ); ?>" <?php echo '1' === $event_cta_new ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
								<?php echo esc_html( $cta_text ); ?>
							</a>
						</p>
					<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php if ( $add_event_url ) : ?>
				<p class="stc-timeline-add-event">
					<a class="stc-timeline-add-event__button" href="<?php echo esc_url( $add_event_url ); ?>">
						<?php esc_html_e( 'Add Event to This Timeline', 'simple-timeline-collections' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</section>
		<?php

		wp_reset_postdata();
		return ob_get_clean();
	}

	public function set_event_admin_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['stc_event_timeline'] = __( 'Timeline', 'simple-timeline-collections' );
				$new_columns['stc_event_date']     = __( 'Event Date', 'simple-timeline-collections' );
				$new_columns['stc_event_cta']      = __( 'CTA', 'simple-timeline-collections' );
			}
		}

		return $new_columns;
	}

	public function render_event_admin_columns( $column, $post_id ) {
		if ( 'stc_event_timeline' === $column ) {
			$timeline_id = absint( get_post_meta( $post_id, self::META_EVENT_TIMELINE, true ) );
			if ( $timeline_id && $this->is_valid_timeline_id( $timeline_id ) ) {
				$edit_link = get_edit_post_link( $timeline_id );
				if ( $edit_link ) {
					echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( get_the_title( $timeline_id ) ) . '</a>';
				} else {
					echo esc_html( get_the_title( $timeline_id ) );
				}
			} else {
				echo '&mdash;';
			}
		}

		if ( 'stc_event_date' === $column ) {
			$date      = get_post_meta( $post_id, self::META_DATE, true );
			$timestamp = $date ? strtotime( $date ) : false;
			echo $timestamp ? esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ) : '&mdash;';
		}

		if ( 'stc_event_cta' === $column ) {
			$url = get_post_meta( $post_id, self::META_CTA_URL, true );
			echo $url ? esc_html__( 'Yes', 'simple-timeline-collections' ) : '&mdash;';
		}
	}

	public function set_event_sortable_columns( $columns ) {
		$columns['stc_event_date'] = 'stc_event_date';
		return $columns;
	}

	public function render_events_timeline_filter( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		$selected  = isset( $_GET['stc_timeline_filter'] ) ? absint( wp_unslash( $_GET['stc_timeline_filter'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$timelines = $this->get_timeline_posts();
		?>
		<label class="screen-reader-text" for="stc_timeline_filter"><?php esc_html_e( 'Filter by timeline', 'simple-timeline-collections' ); ?></label>
		<select name="stc_timeline_filter" id="stc_timeline_filter">
			<option value="0"><?php esc_html_e( 'All timelines', 'simple-timeline-collections' ); ?></option>
			<?php foreach ( $timelines as $timeline ) : ?>
				<option value="<?php echo esc_attr( $timeline->ID ); ?>" <?php selected( $selected, $timeline->ID ); ?>><?php echo esc_html( get_the_title( $timeline ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function sort_and_filter_admin_events( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );
		$timeline_filter = isset( $_GET['stc_timeline_filter'] ) ? absint( wp_unslash( $_GET['stc_timeline_filter'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $timeline_filter && $this->is_valid_timeline_id( $timeline_filter ) ) {
			$meta_query[] = array(
				'key'     => self::META_EVENT_TIMELINE,
				'value'   => $timeline_filter,
				'compare' => '=',
				'type'    => 'NUMERIC',
			);
			$query->set( 'meta_query', $meta_query );
		}

		if ( 'stc_event_date' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', self::META_DATE );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	public function set_timeline_admin_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['stc_timeline_shortcode'] = __( 'Shortcode', 'simple-timeline-collections' );
				$new_columns['stc_timeline_layout']    = __( 'Layout', 'simple-timeline-collections' );
				$new_columns['stc_timeline_events']    = __( 'Events', 'simple-timeline-collections' );
			}
		}

		return $new_columns;
	}

	public function render_timeline_admin_columns( $column, $post_id ) {
		if ( 'stc_timeline_shortcode' === $column ) {
			echo '<code>[simple_timeline_collections id="' . esc_attr( $post_id ) . '"]</code>';
		}

		if ( 'stc_timeline_layout' === $column ) {
			$settings = $this->get_timeline_settings( $post_id );
			$layouts  = $this->get_allowed_layouts();
			echo isset( $layouts[ $settings['layout'] ] ) ? esc_html( $layouts[ $settings['layout'] ] ) : '&mdash;';
		}

		if ( 'stc_timeline_events' === $column ) {
			$count = $this->count_events_for_timeline( $post_id );
			echo esc_html( number_format_i18n( $count ) );
		}
	}

	private function get_timeline_posts() {
		return get_posts(
			array(
				'post_type'      => self::TIMELINE_POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
	}

	private function get_events_for_timeline( $timeline_id, $limit = -1 ) {
		$event_query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => $limit,
				'fields'         => 'ids',
				'meta_key'       => self::META_DATE,
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => self::META_EVENT_TIMELINE,
						'value'   => absint( $timeline_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		$ids = $event_query->posts;
		wp_reset_postdata();

		return $ids;
	}

	private function count_events_for_timeline( $timeline_id ) {
		$event_query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => self::META_EVENT_TIMELINE,
						'value'   => absint( $timeline_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		$count = absint( $event_query->found_posts );
		wp_reset_postdata();

		return $count;
	}

	private function get_default_timeline_id() {
		$options = $this->get_options();

		if ( $options['default_timeline_id'] && $this->is_valid_timeline_id( $options['default_timeline_id'] ) ) {
			return absint( $options['default_timeline_id'] );
		}

		$timelines = $this->get_timeline_posts();
		if ( ! empty( $timelines ) ) {
			return absint( $timelines[0]->ID );
		}

		return 0;
	}

	private function is_valid_timeline_id( $timeline_id ) {
		$timeline_id = absint( $timeline_id );
		if ( ! $timeline_id ) {
			return false;
		}

		$post = get_post( $timeline_id );
		return $post && self::TIMELINE_POST_TYPE === $post->post_type && 'trash' !== $post->post_status;
	}

	private function resolve_timeline_id( $id = '', $timeline = '' ) {
		$id = absint( $id );
		if ( $id && $this->is_valid_timeline_id( $id ) ) {
			return $id;
		}

		$timeline = trim( (string) $timeline );
		if ( '' === $timeline ) {
			return 0;
		}

		if ( is_numeric( $timeline ) ) {
			$timeline_id = absint( $timeline );
			return $this->is_valid_timeline_id( $timeline_id ) ? $timeline_id : 0;
		}

		$timeline_post = get_page_by_path( sanitize_title( $timeline ), OBJECT, self::TIMELINE_POST_TYPE );
		if ( $timeline_post && self::TIMELINE_POST_TYPE === $timeline_post->post_type && 'trash' !== $timeline_post->post_status ) {
			return absint( $timeline_post->ID );
		}

		return 0;
	}

	private function get_timeline_settings( $timeline_id ) {
		$options     = $this->get_options();
		$timeline_id = absint( $timeline_id );

		$title = get_post_meta( $timeline_id, self::META_TIMELINE_TITLE, true );
		if ( '' === trim( $title ) ) {
			$title = get_the_title( $timeline_id );
		}
		if ( '' === trim( $title ) ) {
			$title = $options['timeline_title'];
		}

		$title_alignment = get_post_meta( $timeline_id, self::META_TIMELINE_TITLE_ALIGNMENT, true );
		if ( ! array_key_exists( $title_alignment, $this->get_allowed_title_alignments() ) ) {
			$title_alignment = $options['title_alignment'];
		}

		$layout = get_post_meta( $timeline_id, self::META_TIMELINE_LAYOUT, true );
		if ( ! array_key_exists( $layout, $this->get_allowed_layouts() ) ) {
			$layout = $options['layout'];
		}

		$alternate_card_gap = $this->normalize_alternate_card_gap(
			get_post_meta( $timeline_id, self::META_TIMELINE_ALTERNATE_CARD_GAP, true ),
			$options['alternate_card_gap']
		);

		$event_item_colors = $this->normalize_event_item_color_settings(
			get_post_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_BEFORE_COLOR_HEX, true ),
			get_post_meta( $timeline_id, self::META_TIMELINE_EVENT_ITEM_CURRENT_COLOR_HEX, true ),
			get_post_meta( $timeline_id, self::META_TIMELINE_CARD_BACKGROUND_COLOR_HEX, true ),
			get_post_meta( $timeline_id, self::META_TIMELINE_RAIL_COLOR_HEX, true ),
			$options
		);

		return array(
			'timeline_title'           => $title,
			'title_alignment'         => $title_alignment,
			'layout'                  => $layout,
			'alternate_card_gap'      => $alternate_card_gap,
			'event_item_before_color' => $event_item_colors['event_item_before_color'],
			'event_item_current_color' => $event_item_colors['event_item_current_color'],
			'card_background_color'   => $event_item_colors['card_background_color'],
			'rail_color'              => $event_item_colors['rail_color'],
			'default_timeline_id'      => $options['default_timeline_id'],
		);
	}
}

}

function stc_simple_timeline_collections_activate() {
	if ( class_exists( 'STC_Simple_Timeline_Collections' ) ) {
		$instance = STC_Simple_Timeline_Collections::instance();

		if ( method_exists( $instance, 'register_timeline_post_type' ) ) {
			$instance->register_timeline_post_type();
		}

		if ( method_exists( $instance, 'register_event_post_type' ) ) {
			$instance->register_event_post_type();
		}

		if ( method_exists( $instance, 'maybe_run_upgrade' ) ) {
			$instance->maybe_run_upgrade( true );
		}
	}

	flush_rewrite_rules();
}

function stc_simple_timeline_collections_deactivate() {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'stc_simple_timeline_collections_activate' );
register_deactivation_hook( __FILE__, 'stc_simple_timeline_collections_deactivate' );

if ( class_exists( 'STC_Simple_Timeline_Collections' ) ) {
	STC_Simple_Timeline_Collections::instance();
}
