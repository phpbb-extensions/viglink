<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\viglink\acp;

/**
* @package module_install
*/
class viglink_info
{
	function module()
	{
		return array(
			'filename'	=> '\phpbb\viglink\acp\viglink_module',
			'title'		=> 'ACP_VIGLINK_SETTINGS',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_VIGLINK_SETTINGS',
					'auth' => 'ext_phpbb/viglink && acl_a_board',
					'cat' => array('ACP_BOARD_CONFIGURATION')
				),
			),
		);
	}
}
