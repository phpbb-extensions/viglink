<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\tests;

class ext_test extends \phpbb_test_case
{
	public function test_enableable_and_enable_steps()
	{
		$config = new \phpbb\config\config([]);
		$cache = new \phpbb\cache\driver\dummy();
		$downloader = $this->getMockBuilder('\phpbb\file_downloader')->setMethods(['get'])->getMock();
		$downloader->method('get')->willReturn('1');
		$language = $this->createMock('\phpbb\language\language');
		$log = $this->createMock('\phpbb\log\log');
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$container->method('get')->willReturnMap([
			['cache.driver', 1, $cache],
			['config', 1, $config],
			['file_downloader', 1, $downloader],
			['language', 1, $language],
			['log', 1, $log],
			['user', 1, $user],
		]);
		$finder = $this->getMockBuilder('\phpbb\finder')->disableOriginalConstructor()->getMock();
		$finder->method('extension_directory')->willReturnSelf();
		$finder->method('find_from_extension')->willReturn([]);
		$finder->method('get_classes_from_files')->willReturn([]);
		$migrator = $this->getMockBuilder('\phpbb\db\migrator')->disableOriginalConstructor()->getMock();
		$migrator->method('get_migrations')->willReturn([]);
		$migrator->method('finished')->willReturn(true);
		$ext = new \phpbb\viglink\ext($container, $finder, $migrator, 'phpbb/viglink', '');

		$this->assertTrue($ext->is_enableable());
		$this->assertSame('viglink', $ext->enable_step(false));
		$this->assertFalse($ext->enable_step('viglink'));
	}

	public function test_enable_logs_service_failure()
	{
		$cache = $this->createMock('\phpbb\cache\driver\driver_interface');
		$cache->method('get')->willReturn(false);
		$config = new \phpbb\config\config([]);
		$downloader = $this->getMockBuilder('\phpbb\file_downloader')->setMethods(['get'])->getMock();
		$downloader->method('get')->willThrowException(new \phpbb\exception\runtime_exception('FAIL'));
		$language = $this->createMock('\phpbb\language\language');
		$language->method('lang')->willReturn('FAIL');
		$log = $this->createMock('\phpbb\log\log');
		$log->expects($this->once())->method('add');
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$container->method('get')->willReturnMap([
			['cache.driver', 1, $cache], ['config', 1, $config], ['file_downloader', 1, $downloader],
			['language', 1, $language], ['log', 1, $log], ['user', 1, $user],
		]);
		$finder = $this->getMockBuilder('\phpbb\finder')->disableOriginalConstructor()->getMock();
		$migrator = $this->getMockBuilder('\phpbb\db\migrator')->disableOriginalConstructor()->getMock();
		$ext = new \phpbb\viglink\ext($container, $finder, $migrator, 'phpbb/viglink', '');

		$this->assertSame('viglink', $ext->enable_step(false));
	}
}
