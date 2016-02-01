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
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\viglink\cron\viglink */
	protected $cron_task;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\viglink\acp\viglink_helper */
	protected $viglink_helper;

	public function setUp()
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array('viglink_last_gc' => 0));
		$this->viglink_helper = $this->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->disableOriginalConstructor()
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
		$this->viglink_helper->expects($this->once())
			->method('set_viglink_services')
			->will($this->returnCallback(array($this, 'set_config')));

		// Run the cron task
		$this->cron_task->run();

		// Assert the viglink_last_gc value has been updated
		$this->assertNotEquals($viglink_last_gc, $this->config['viglink_last_gc']);
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
		$this->assertSame($expected, $this->cron_task->should_run());
	}

	/**
	 * Test the cron task is runnable
	 */
	public function test_is_runnable()
	{
		$this->assertTrue($this->cron_task->is_runnable());
	}
}
