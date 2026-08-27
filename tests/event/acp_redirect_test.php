<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\event;

class acp_redirect_test extends \phpbb_test_case
{
	public static $redirect_url;

	public function test_founder_is_redirected_to_viglink_question()
	{
		self::$redirect_url = null;
		$config = new \phpbb\config\config(['viglink_ask_admin' => 0, 'viglink_ask_admin_last' => 0]);
		$language = $this->createMock('\phpbb\language\language');
		$request = $this->createMock('\phpbb\request\request_interface');
		$template = $this->createMock('\phpbb\template\template');
		$user = $this->getMockBuilder('\phpbb\user')->disableOriginalConstructor()->getMock();
		$user->data = ['user_type' => USER_FOUNDER];
		$helper = $this->getMockBuilder('\phpbb\viglink\acp\viglink_helper')->disableOriginalConstructor()->getMock();
		$helper->expects($this->once())->method('set_viglink_services');
		$listener = new acp_listener($config, $language, $request, $template, $user, $helper, 'phpBB/', 'php');

		$listener->set_viglink_services();

		$this->assertNotEmpty($config['viglink_ask_admin_last']);
		$this->assertStringContainsString('acp_help_phpbb', self::$redirect_url);
	}
}

function append_sid($url, $params)
{
	return $url . '?' . $params;
}

function redirect($url)
{
	acp_redirect_test::$redirect_url = $url;
}
