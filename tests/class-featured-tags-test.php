<?php

use Featured_Tags\Admin;

/**
 * Test the Admin class.
 * Which is responsible for the admin UI side of the plugin.
 */
class Test_Featured_Tags_Admin extends WP_UnitTestCase {
	/**
	 * Test the registration of the post status "queued", making sure it's in the registered post statuses.
	 *
	 * @return void
	 */
	public function test_register_post_status() {
		$this->admin->register_post_status();
		$this->assertTrue( in_array( 'queued', get_post_stati(), true ), 'The post status "queued" should be registered.' );
	}
}
