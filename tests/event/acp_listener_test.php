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

require_once __DIR__ . '/../../../../../includes/functions.php';

class acp_listener_test extends \phpbb_test_case
{
	/** @var \phpbb\viglink\event\acp_listener */
	protected $acp_listener;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\viglink\acp\viglink_helper */
	protected $helper;

	protected $path;

	public function setUp()
	{
		parent::setUp();

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();

		$this->config = new \phpbb\config\config(array());

		$this->helper = $this
			->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				$this->config,
				new \phpbb\file_downloader(),
				new \phpbb\user('\phpbb\datetime'),
			))
			->getMock()
		;

		$this->path = __DIR__ . '/../fixtures/';
	}

	/**
	* Create the event listener
	*/
	protected function set_listener()
	{
		$this->acp_listener = new \phpbb\viglink\event\acp_listener(
			$this->helper
		);
	}

	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$this->set_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->acp_listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.acp_main_notice',
		), array_keys(\phpbb\viglink\event\acp_listener::getSubscribedEvents()));
	}

	/**
	* Test data for test_set_viglink_services
	*/
	public function set_viglink_services_data()
	{
		return array(
			array(
				'3.0.0', // Board version is less than current versions
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'3.2.0', // Board version is equal to current versions
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'4.0.0', // No current version data was available
				array(),
				array(
					'allow_viglink_global'		=> true,
					'allow_viglink_phpbb'		=> true,
					'phpbb_viglink_api_key'		=> '',
				),
			),
		);
	}

	/**
	* Test the set_viglink_services event
	*
	* @dataProvider set_viglink_services_data
	*/
	public function test_set_viglink_services($current_version, $expected)
	{
		$this->config = new \phpbb\config\config(array(
			'version' => $current_version,
			'allow_viglink_global' => 1,
			'allow_viglink_phpbb'  => 1,
			'phpbb_viglink_api_key'=> '',
		));

		$this->helper = $this
			->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				$this->config,
				new \phpbb\file_downloader(),
				new \phpbb\user('\phpbb\datetime'),
			))
			->getMock()
		;

		$this->set_listener();

		$versions = json_decode(file_get_contents($this->path . 'viglink.json'), true);

		$this->helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions['stable']));

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.acp_main_notice', array($this->acp_listener, 'set_viglink_services'));
		$dispatcher->dispatch('core.acp_main_notice');

		foreach ($expected as $config_name => $expected_value)
		{
			$this->assertEquals($expected_value, $this->config[$config_name]);
		}
	}
}
