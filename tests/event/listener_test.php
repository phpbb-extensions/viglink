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
	/** @var \phpbb\viglink\event\listener */
	protected $listener;

	/**
	* Setup test environment
	*
	* @access public
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
		$this->template = new \phpbb\viglink\tests\mock\template();
	}

	/**
	* Create the event listener
	*
	* @access protected
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
	*
	* @access public
	*/
	public function test_construct()
	{
		$this->set_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	/**
	* Test the event listener is subscribing events
	*
	* @access public
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.viewtopic_post_row_after',
		), array_keys(\phpbb\viglink\event\listener::getSubscribedEvents()));
	}

	/**
	* Test the load_viglink event
	*
	* @access public
	*/
	public function test_load_viglink()
	{
		$this->set_listener();

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewtopic_post_row_after', array($this->listener, 'display_viglink'));
		$dispatcher->dispatch('core.viewtopic_post_row_after');

		$this->assertEquals(array(
			'VIGLINK_ENABLED'	=> $this->config['viglink_enabled'],
			'VIGLINK_API_KEY'	=> $this->config['viglink_api_key'],
		), $this->template->get_template_vars());
	}
}
