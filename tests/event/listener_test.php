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

	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $template;

	/**
	* Setup test environment
	*/
	public function setUp()
	{
		parent::setUp();

		// Load/Mock classes required by the event listener class
		$this->config = new \phpbb\config\config(array(
			'allow_viglink_global' => 1,
			'allow_viglink_phpbb' => 1,
			'viglink_api_key' => '12345678901234567890123456789012',
		));
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

	/**
	* Test the load_viglink event
	*/
	public function test_load_viglink()
	{
		$this->set_listener();

		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'VIGLINK_ENABLED'	=> $this->config['viglink_enabled'],
				'VIGLINK_API_KEY'	=> $this->config['viglink_api_key'],
			));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewtopic_post_row_after', array($this->listener, 'display_viglink'));
		$dispatcher->dispatch('core.viewtopic_post_row_after');
	}
}
