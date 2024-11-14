<?php
/**
 * The main class for the WP Featured Tags plugin.
 *
 * @package Featured_Tags
 */

namespace Featured_Tags;

/**
 * The main class for the WP Featured Tags plugin.
 *
 * This class is responsible for loading the settings, initializing the admin and manager classes,
 * and running the plugin.
 */
class Featured_Tags {
	/**
	 * The instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Returns the instance of the class.
	 *
	 * @return Featured_Tags
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes the admin and manager classes.
	 *
	 * @return void
	 */
	public function run(): void {
		// Set up the data for the plugin.
		add_action( 'init', array( $this, 'setup_data' ) );

		// Create a featured tag REST endpoint.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Add a new column to the tag terms table.
		add_filter( 'manage_edit-post_tag_columns', array( $this, 'add_tag_column' ) );

		// Add a new sortable column to the tag terms table.
		add_filter( 'manage_edit-post_tag_sortable_columns', array( $this, 'add_sortable_column' ) );

		// Make the featured column sortable.
		add_action( 'pre_get_terms', array( $this, 'sort_featured_tag_column' ) );

		// Render the featured tag column.
		add_action( 'manage_post_tag_custom_column', array( $this, 'render_tag_column' ), 10, 3 );

		// Enqueue admin JS and CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Sets up the data for the plugin.
	 *
	 * @return void
	 */
	public function setup_data(): void {
		// Register the featured term meta.
		register_term_meta(
			'post_tag',
			'featured',
			array(
				'type'              => 'boolean',
				'description'       => 'Whether the tag is featured or not.',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
	}

	/**
	 * Registers the REST routes for the plugin.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'featured-tags/v1',
			'/featured-tag/(?P<term_id>\d+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => array(
					$this,
					'toggle_featured_tag',
				),
				'args'                => array(
					'term_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param ) {
							return is_numeric( $param );
						},
						'sanitize_callback' => function ( $param ) {
							return absint( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Toggles the featured tag for a term.
	 *
	 * @param array $request The request data.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_featured_tag( $request ): \WP_REST_Response|\WP_Error {
		$term_id = $request['term_id'];

		$featured = get_term_meta( $term_id, 'featured', true );

		$response = update_term_meta( $term_id, 'featured', ! $featured );

		return rest_ensure_response( $response );
	}

	/**
	 * Adds a new column to the tag terms table.
	 *
	 * @param array $columns The columns in the tag terms table.
	 *
	 * @return array
	 */
	public function add_tag_column( $columns ): array {
		$columns['featured_tag'] = __( 'Featured', 'featured-tags' );

		return $columns;
	}

	/**
	 * Adds a new sortable column to the tag terms table.
	 *
	 * @param array $columns The columns in the tag terms table.
	 *
	 * @return array
	 */
	public function add_sortable_column( $columns ): array {
		$columns['featured_tag'] = 'featured_tag';

		return $columns;
	}

	/**
	 * Modifies the terms query to sort by the featured tag status.
	 *
	 * @param WP_Term_Query $query The terms query.
	 *
	 * @return void
	 */
	public function sort_featured_tag_column( $query ): void {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->query_vars['orderby'] ?? '';

		if ( 'featured_tag' === $orderby ) {
			$query->meta_query = new \WP_Meta_Query(
				array(
					'relation' => 'OR',
					array(
						'key'     => 'featured',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'featured',
						'compare' => 'NOT EXISTS',
					),
				)
			);

			$query->query_vars['orderby'] = 'meta_value';
		}
	}

	/**
	 * Renders the featured tag column.
	 *
	 * @param string  $content     The content of the column.
	 * @param string  $column_name The name of the column.
	 * @param integer $term_id     The ID of the term.
	 *
	 * @return void
	 */
	public function render_tag_column( $content, $column_name, $term_id ): void {
		// Only render the column if it's the featured column.
		if ( 'featured_tag' !== $column_name ) {
			return;
		}

		$featured = get_term_meta( $term_id, 'featured', true ) ? 'yes' : 'no';

		printf(
			'<a href="#" data-term-id="%1$s" data-featured="%2$s" title="%3$s">%2$s</a>',
			esc_attr( $term_id ),
			esc_attr( $featured ),
			esc_attr__( 'Toggle featured status', 'featured-tags' )
		);
	}

	/**
	 * Enqueues the admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets(): void {
		$deps = require FEATURED_TAGS_PATH . 'build/admin.asset.php';

		wp_enqueue_style(
			'featured-tags-admin',
			FEATURED_TAGS_PATH . 'build/admin.css',
			array(),
			$deps['version']
		);

		wp_enqueue_script(
			'featured-tags-admin',
			FEATURED_TAGS_PATH . 'build/admin.js',
			$deps['dependencies'],
			$deps['version'],
			true
		);

		wp_enqueue_style( 'dashicons' );
	}
}
