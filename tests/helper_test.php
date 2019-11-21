<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\tests;

class helper_test extends \phpbb_test_case
{
	/** @var \phpbb\cache\driver\driver_interface|\PHPUnit_Framework_MockObject_MockObject */
	protected $cache;

	/** @var string Path to test fixtures */
	protected $path;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log|\PHPUnit_Framework_MockObject_MockObject */
	protected $log;

	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\driver_interface')
			->disableOriginalConstructor()
			->getMock();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Get viglink_helper mock object
	 *
	 * @return \phpbb\viglink\acp\viglink_helper
	 */
	public function get_viglink_helper()
	{
		return new \phpbb\viglink\acp\viglink_helper(
			$this->cache,
			new \phpbb\config\config(array()),
			new \phpbb\file_downloader(),
			$this->language,
			$this->log,
			new \phpbb\user($this->language, '\phpbb\datetime')
		);
	}

	/**
	 * Test the log_viglink_error() method
	 */
	public function test_log_viglink_error()
	{
		$message = 'Test message';

		$this->log->expects($this->once())
			->method('add')
			->with(
				$this->equalTo('critical'),
				ANONYMOUS,
				'',
				$this->equalTo('LOG_VIGLINK_CHECK_FAIL'),
				false,
				array($message)
			);

		$viglink_helper = $this->get_viglink_helper();
		$viglink_helper->log_viglink_error($message);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function test_exceptions()
	{
		$viglink_helper = $this->get_viglink_helper();

		$this->cache->expects($this->once())
			->method('get')
			->with($this->anything())
			->willReturn(false);

		// Throw an exception when cache is required, but there is no cache data
		$viglink_helper->set_viglink_services(false, true);
	}
}
