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

class acp_listener_test extends \phpbb_test_case
{
	/** @var \phpbb\viglink\event\acp_listener */
	protected $acp_listener;

	/** @var \phpbb\cache\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language|\PHPUnit\Framework\MockObject\MockObject */
	protected $language;

	/** @var \phpbb\log\log|\PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var \phpbb\request\request|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\template\template|\PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\viglink\acp\viglink_helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $helper;

	protected $path;

	protected $phpbb_root_path;
	protected $php_ext;

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher;
		parent::setUp();

		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\driver_interface')
			->disableOriginalConstructor()
			->getMock();

		$this->config = new \phpbb\config\config(array());

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();

		$this->helper = $this
			->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setConstructorArgs(array(
				$this->cache,
				$this->config,
				new \phpbb\file_downloader(),
				$this->language,
				$this->log,
				new \phpbb\user($this->language, '\phpbb\datetime'),
			))
			->getMock()
		;

		$this->path = __DIR__ . '/../fixtures/';
		$this->request = $this->getMockBuilder('\phpbb\request\request_interface')
			->disableOriginalConstructor()
			->getMock();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()
			->getMock();
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->data['user_type'] = USER_NORMAL;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
	}

	/**
	* Create the event listener
	*/
	protected function set_listener()
	{
		$this->acp_listener = new \phpbb\viglink\event\acp_listener(
			$this->config,
			$this->language,
			$this->request,
			$this->template,
			$this->user,
			$this->helper,
			$this->phpbb_root_path,
			$this->php_ext
		);
	}

	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$this->set_listener();
		self::assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->acp_listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		self::assertEquals(array(
			'core.acp_main_notice',
			'core.acp_help_phpbb_submit_before',
		), array_keys(\phpbb\viglink\event\acp_listener::getSubscribedEvents()));
	}

	public function test_set_viglink_services()
	{
		$this->helper->expects(self::once())
			->method('set_viglink_services');
		$this->helper->expects(self::never())
			->method('log_viglink_error');

		$this->set_listener();

		$this->acp_listener->set_viglink_services();
	}

	public function test_set_viglink_services_errors()
	{
		$this->helper->expects(self::once())
			->method('set_viglink_services')
			->willThrowException(new \RuntimeException);
		$this->helper->expects(self::once())
			->method('log_viglink_error');

		$this->set_listener();

		$this->acp_listener->set_viglink_services();
	}

	public function data_update_viglink_settings()
	{
		return array(
			array(
				array('viglink_enabled' => true),
				array(''),
				'',
				true,
			),
			array(
				array('viglink_enabled' => true),
				array(''),
				'0',
				true,
			),
			array(
				array('viglink_enabled' => true),
				array('submit' => true),
				'0',
				'0',
			),
			array(
				array('viglink_enabled' => true),
				array('submit' => false),
				'0',
				true,
			),
			array(
				array('viglink_enabled' => false),
				array('submit' => true),
				true,
				true,
			),
		);
	}

	/**
	 * @dataProvider data_update_viglink_settings
	 */
	public function test_update_viglink_settings($predefined_config, $event_ary, $request_return, $expected_setting)
	{
		$this->config = new \phpbb\config\config($predefined_config);
		$this->request->expects(self::once())
			->method('variable')
			->willReturn($request_return);
		$this->set_listener();

		$this->acp_listener->update_viglink_settings($event_ary);

		self::assertEquals($this->config['viglink_enabled'], $expected_setting);
	}
}
