<?php

use Featured_Tags\Featured_Tags;

/**
 * Test the Featured Tags plugin setup.
 */
class Test_Featured_Tags extends WP_UnitTestCase {
	/**
	 * Test the registration of the featured tag meta key.
	 *
	 * @return void
	 */
	public function test_setup_data_registers_featured_term_meta() {
		Featured_Tags::instance()->setup_data();

		$registered_meta = get_registered_meta_keys( 'term', 'post_tag' );

		$this->assertArrayHasKey( 'featured', $registered_meta );
		$this->assertSame( 'boolean', $registered_meta['featured']['type'] );
	}
}
