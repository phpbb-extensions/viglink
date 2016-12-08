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
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\cache\service */
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

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
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
	 * @param \phpbb\config\config $config
	 * @return \phpbb\viglink\acp\viglink_helper|\PHPUnit_Framework_MockObject_MockObject
	 */
	public function get_viglink_helper($config)
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

		$config = new \phpbb\config\config(array());

		$this->log->expects($this->any())
			->method('add')
			->with(
				$this->equalTo('critical'),
				ANONYMOUS,
				'',
				$this->equalTo('LOG_VIGLINK_CHECK_FAIL'),
				false,
				array($message)
			);

		$viglink_helper = $this->get_viglink_helper($config);
		$viglink_helper->log_viglink_error($message);
	}
}
