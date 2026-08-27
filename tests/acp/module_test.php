<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\acp;

class module_test extends \phpbb_test_case
{
	public static $valid_form = true;
	public static $download_response = false;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\template\template|\PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	protected function setUp(): void
	{
		parent::setUp();
		global $phpbb_container;

		self::$valid_form = true;
		self::$download_response = false;
		$uuid = 'uuid';
		$site_id = 'site';
		$this->config = new \phpbb\config\config([
			'viglink_enabled' => 1,
			'allow_viglink_phpbb' => 1,
			'questionnaire_unique_id' => $uuid,
			'viglink_api_siteid' => $site_id,
			'viglink_convert_account_url' => 'https://www.viglink.com/users/convertAccount?subId=' . md5($site_id . $uuid),
			'server_name' => 'example.com',
			'phpbb_viglink_api_key' => 'key',
		]);
		$language = $this->createMock('\phpbb\language\language');
		$language->method('lang')->willReturnArgument(0);
		$this->request = $this->createMock('\phpbb\request\request');
		$this->template = $this->createMock('\phpbb\template\template');
		$container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$container->method('get')->willReturnMap([
			['config', 1, $this->config],
			['language', 1, $language],
			['request', 1, $this->request],
			['template', 1, $this->template],
		]);
		$phpbb_container = $container;
	}

	public function test_module_info()
	{
		$info = (new viglink_info())->module();
		$this->assertSame('ACP_VIGLINK_SETTINGS', $info['title']);
		$this->assertArrayHasKey('settings', $info['modes']);
	}

	public function test_unknown_mode_stops_after_initialisation()
	{
		$this->request->method('is_set_post')->willReturn(false);
		$module = new viglink_module();
		$module->main(0, 'unknown');

		$this->assertSame('acp_viglink', $module->tpl_name);
	}

	public function test_settings_are_rendered()
	{
		$this->request->method('is_set_post')->willReturn(false);
		$this->template->expects($this->once())->method('assign_vars')->with($this->callback(function ($vars) {
			return !$vars['S_ERROR'] && $vars['VIGLINK_ENABLED'] === 1 && strpos($vars['U_VIGLINK_CONVERT'], 'subId=') !== false;
		}));
		$module = new viglink_module();
		$module->u_action = 'action';
		$module->main(0, 'settings');
	}

	public function test_invalid_form_is_rendered_as_error()
	{
		self::$valid_form = false;
		$this->request->method('is_set_post')->willReturn(true);
		$this->template->expects($this->once())->method('assign_vars')->with($this->callback(function ($vars) {
			return $vars['S_ERROR'] && strpos($vars['ERROR_MSG'], 'FORM_INVALID') !== false;
		}));
		(new viglink_module())->main(0, 'settings');
	}

	public function test_valid_form_updates_config()
	{
		$this->request->method('is_set_post')->willReturn(true);
		$this->request->method('variable')->with('viglink_enabled', 0)->willReturn(0);
		$this->expectException('\RuntimeException');
		try
		{
			(new viglink_module())->main(0, 'settings');
		}
		finally
		{
			$this->assertSame(0, $this->config['viglink_enabled']);
		}
	}

	public function test_missing_uuid_and_disabled_service_are_reported()
	{
		$this->config->delete('questionnaire_unique_id');
		$this->config['allow_viglink_phpbb'] = 0;
		$this->request->method('is_set_post')->willReturn(false);
		$this->template->expects($this->once())->method('assign_vars')->with($this->callback(function ($vars) {
			return $vars['S_ERROR'] && strpos($vars['ERROR_MSG'], 'ACP_VIGLINK_DISABLED_PHPBB') !== false;
		}));
		(new viglink_module())->main(0, 'settings');
		$this->assertSame('test-unique-id', $this->config['questionnaire_unique_id']);
	}

	public function test_convert_link_is_downloaded_and_sanitised()
	{
		$this->config['viglink_convert_account_url'] = '';
		self::$download_response = 'https://www.viglink.com/users/convertAccount?subId=new';
		$this->request->method('is_set_post')->willReturn(false);
		(new viglink_module())->main(0, 'settings');

		$this->assertSame(self::$download_response, $this->config['viglink_convert_account_url']);
	}
}

function add_form_key($form_key)
{
}

function check_form_key($form_key)
{
	return module_test::$valid_form;
}

function unique_id()
{
	return 'test-unique-id';
}

function adm_back_link($u_action)
{
	return '';
}

function trigger_error($message, $type = E_USER_NOTICE)
{
	throw new \RuntimeException($message);
}

function file_get_contents($url)
{
	return module_test::$download_response;
}
