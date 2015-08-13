<?php
/**
*
* VigLink extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\viglink\tests\event;

class listener_test extends \phpbb_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\viglink\event\listener */
	protected $listener;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/**
	 * Setup test environment
	 */
	public function setUp()
	{
		parent::setUp();

		// Load/Mock classes required by the event listener class
		$this->config = new \phpbb\config\config(array('viglink_enabled' => 1));
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
	}

	/**
	 * Create the event listener
	 */
	protected function set_listener()
	{
		$this->listener = new \phpbb\viglink\event\listener(
			$this->config,
			$this->template
		);
	}

	/**
	 * Test the event listener is constructed correctly
	 */
	public function test_construct()
	{
		$this->set_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	/**
	 * Test the event listener is subscribing events
	 */
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.viewtopic_post_row_after',
		), array_keys(\phpbb\viglink\event\listener::getSubscribedEvents()));
	}

	public function display_viglink_data()
	{
		return array(
			array( // User has a key, use their own key
				true, // allow people to use their own key
				true, // allow people to use phpBB's key
				'user_key_1234567890',
				'phpbb_key_1234567890',
				array(
					'viglink_enabled' => true,
					'viglink_api_key' => 'user_key_1234567890',
				)
			),
			array( // User has no key, use phpBB's key
				true, // allow people to use their own key
				true, // allow people to use phpBB's key
				'',
				'phpbb_key_1234567890',
				array(
					'viglink_enabled' => true,
					'viglink_api_key' => 'phpbb_key_1234567890',
				)
			),
			array( // User has a key, but is disallowed, use phpBB's key
				false, // disallow people using their own keys
				true, // allow people to use phpBB's key
				'user_key_1234567890',
				'phpbb_key_1234567890',
				array(
					'viglink_enabled' => true,
					'viglink_api_key' => 'phpbb_key_1234567890',
				)
			),
			array( // User has a key, all are disallowed, disable viglink
				false, // disallow people using their own keys
				false, // disallow people using phpBB's key
				'user_key_1234567890',
				'phpbb_key_1234567890',
				array(
					'viglink_enabled' => false,
					'viglink_api_key' => '',
				)
			),
			array( // User has a key, but is disallowed, phpBB key missing, disable viglink
				false, // disallow people using their own keys
				true, // allow people to use phpBB's key
				'user_key_1234567890',
				'',
				array(
					'viglink_enabled' => false,
					'viglink_api_key' => '',
				)
			),
		);
	}

	/**
	 * Test the display_viglink event
	 *
	 * @dataProvider display_viglink_data
	 */
	public function test_display_viglink($allow_viglink_global, $allow_viglink_phpbb, $user_api_key, $phpbb_api_key, $expected)
	{
		$this->config['viglink_api_key'] = $user_api_key;
		$this->config['phpbb_viglink_api_key'] = $phpbb_api_key;

		$this->config['allow_viglink_global'] = $allow_viglink_global;
		$this->config['allow_viglink_phpbb'] = $allow_viglink_phpbb;

		$this->set_listener();

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'VIGLINK_ENABLED'	=> $expected['viglink_enabled'],
				'VIGLINK_API_KEY'	=> $expected['viglink_api_key'],
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewtopic_post_row_after', array($this->listener, 'display_viglink'));
		$dispatcher->dispatch('core.viewtopic_post_row_after');
	}
}
