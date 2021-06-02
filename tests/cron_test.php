<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\tests;

class cron_test extends \phpbb_test_case
{
	/** @var \phpbb\cache\driver\dummy */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\viglink\cron\viglink */
	protected $cron_task;

	/** @var \phpbb\db\driver\mysqli|\PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	/** @var \phpbb\file_downloader|\PHPUnit\Framework\MockObject\MockObject */
	protected $file_downloader;

	/** @var \phpbb\language\language|\PHPUnit\Framework\MockObject\MockObject */
	protected $language;

	/** @var \phpbb\log\log||\PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var \phpbb\viglink\acp\viglink_helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $viglink_helper;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array('viglink_last_gc' => 0, 'viglink_enabled' => 1));
		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->db = $this->getMockBuilder('\phpbb\db\driver\mysqli')
			->disableOriginalConstructor()
			->getMock();
		$this->file_downloader = $this->getMockBuilder('\phpbb\file_downloader')
			->disableOriginalConstructor()
			->setMethods(array('get'))
			->getMock();
		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->cache = new \phpbb\cache\driver\dummy();
		$this->viglink_helper = $this->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setConstructorArgs(array($this->cache, $this->config, $this->file_downloader, $this->language, $this->log, $this->user))
			->setMethods(array('set_viglink_services', 'log_viglink_error'))
			->getMock();

		$this->cron_task = new \phpbb\viglink\cron\viglink($this->config, $this->viglink_helper);
	}

	public function set_config()
	{
		$this->config->set('viglink_last_gc', time(), false);
	}

	/**
	 * Test the cron task runs correctly
	 */
	public function test_run()
	{
		// Get the viglink_last_gc
		$viglink_last_gc = $this->config['viglink_last_gc'];

		// Test set_viglink_services() is called once
		$this->viglink_helper->expects(self::once())
			->method('set_viglink_services')
			->willReturnCallback(array($this, 'set_config'));

		// Run the cron task
		$this->cron_task->run();

		// Assert the viglink_last_gc value has been updated
		self::assertNotEquals($viglink_last_gc, $this->config['viglink_last_gc']);
	}

	/**
	 * Test the cron task fails correctly
	 */
	public function test_run_fails()
	{
		$this->viglink_helper->expects(self::once())
			->method('set_viglink_services')
			->willThrowException(new \RuntimeException);

		$this->viglink_helper->expects(self::once())
			->method('log_viglink_error');

		// Run the cron task
		$this->cron_task->run();
	}

	/**
	 * Data set for test_should_run
	 *
	 * @return array Array of test data
	 */
	public function should_run_data()
	{
		return array(
			array(time(), false),
			array(strtotime('23 hours ago'), false),
			array(strtotime('25 hours ago'), true),
			array('', true),
			array(0, true),
			array(null, true),
		);
	}

	/**
	 * Test cron task should run after 24 hours
	 *
	 * @dataProvider should_run_data
	 */
	public function test_should_run($time, $expected)
	{
		// Set the last cron run time
		$this->config['viglink_last_gc'] = $time;

		// Assert we get the expected result from should_run()
		self::assertSame($expected, $this->cron_task->should_run());
	}

	/**
	 * Test the cron task is runnable
	 */
	public function test_is_runnable()
	{
		self::assertTrue($this->cron_task->is_runnable());
	}

	private function get_viglink_helper()
	{
		return new \phpbb\viglink\acp\viglink_helper(
			$this->cache,
			$this->config,
			$this->file_downloader,
			$this->language,
			$this->log,
			$this->user
		);
	}

	public function test_disable_viglink()
	{
		$this->file_downloader->expects(self::once())
			->method('get')
			->willReturn('1');

		$viglink_helper = $this->get_viglink_helper();
		self::assertEquals('', $this->config['allow_viglink_phpbb']);
		$viglink_helper->set_viglink_services(true);
		self::assertEquals(true, $this->config['allow_viglink_phpbb']);

		// Change method to return false
		$this->file_downloader = $this->getMockBuilder('\phpbb\file_downloader')
			->disableOriginalConstructor()
			->setMethods(array('get'))
			->getMock();
		$this->file_downloader->expects(self::once())
			->method('get')
			->willReturn('0');

		$viglink_helper = $this->get_viglink_helper();
		$viglink_helper->set_viglink_services(true);
		self::assertEquals(false, $this->config['allow_viglink_phpbb']);
	}
}
