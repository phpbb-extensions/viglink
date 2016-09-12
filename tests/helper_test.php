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

		$this->path = __DIR__ . '/fixtures/';
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
		/** @var $viglink_helper \PHPUnit_Framework_MockObject_MockObject|\phpbb\viglink\acp\viglink_helper */
		$viglink_helper = $this
			->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				$config,
				new \phpbb\file_downloader(),
				$this->log,
				new \phpbb\user($this->language, '\phpbb\datetime'),
			))
			->getMock()
		;

		return $viglink_helper;
	}

	/**
	 * Test data for test_set_viglink_services
	 */
	public function set_viglink_services_data()
	{
		return array(
			array(
				'3.0.0', // Board version is less than current version on branch 1, get setting from latest version
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'3.1.0-a1', // Board version is less than current version on branch 1, get setting from latest version
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'3.1.0', // Board version is equal to current version on branch 1, get setting from latest version
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'3.2.0', // Board version is equal to current version on branch 2
				array(
					'allow_viglink_phpbb'		=> false,
					'allow_viglink_global'		=> false,
					'phpbb_viglink_api_key'		=> 'bar',
				),
			),
			array(
				'4.0.0', // Current version data not available, existing values unchanged
				array(
					'allow_viglink_phpbb'		=> true,
					'allow_viglink_global'		=> true,
					'phpbb_viglink_api_key'		=> '',
				),
			),
		);
	}

	/**
	 * Test the set_viglink_services method
	 *
	 * @dataProvider set_viglink_services_data
	 */
	public function test_set_viglink_services($current_version, $expected)
	{
		$config = new \phpbb\config\config(array(
			'version' => $current_version,
			'allow_viglink_global' => 1,
			'allow_viglink_phpbb'  => 1,
			'phpbb_viglink_api_key'=> '',
		));

		$viglink_helper = $this->get_viglink_helper($config);

		$versions = json_decode(file_get_contents($this->path . 'viglink.json'), true);

		$viglink_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions['stable']));

		$viglink_helper->set_viglink_services();

		foreach ($expected as $config_name => $expected_value)
		{
			$this->assertEquals($expected_value, $config[$config_name]);
		}
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
