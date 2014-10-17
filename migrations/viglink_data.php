<?php
/**
*
* VigLink extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\viglink\migrations;

class viglink_data extends \phpbb\db\migration\migration
{
	/**
	* Skip this migration if VigLink data already exists
	*
	* @return bool True if data exists, false otherwise
	* @access public
	*/
	public function effectively_installed()
	{
		return isset($this->config['viglink_api_key']);
	}

	/**
	* Add VigLink API Key config to the database.
	*
	* @todo Add phpBB API key value
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			// Basic config options
			array('config.add', array('viglink_enabled', 1)),
			array('config.add', array('viglink_api_key', '')),

			// Special config options for phpBB use
			array('config.add', array('allow_viglink_phpbb', 1)),
			array('config.add', array('allow_viglink_global', 1)),
			array('config.add', array('phpbb_viglink_api_key', '')),

			// Add the ACP module to Board Configuration
			array('module.add', array(
				'acp',
				'ACP_BOARD_CONFIGURATION',
				array(
					'module_basename'	=> '\phpbb\viglink\acp\viglink_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
