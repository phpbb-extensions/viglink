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

class viglink_module_alt
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	public $u_action;

	/** @var array */
	public $new_config = array();

	function main($id, $mode)
	{
		global $config, $request,  $template, $user;

		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;

		$this->user->add_lang('acp/common');
		$this->user->add_lang_ext('phpbb/viglink', 'viglink_module_acp');

		$submit = ($this->request->is_set_post('submit')) ? true : false;

		$form_key = 'acp_viglink';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_VIGLINK_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_VIGLINK_LEGEND',
						'viglink_enable'		=> array('lang' => 'ACP_VIGLINK_ENABLE',	'validate' => 'bool',	'type' => 'radio:disabled_enabled', 'explain' => false),
						'viglink_api_key'		=> array('lang' => 'ACP_VIGLINK_API_KEY',	'validate' => 'string',	'type' => 'text:40:40', 'explain' => true),
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$this->new_config = $this->config;
		$cfg_array = ($this->request->is_set('config')) ? $this->request->variable('config', array('' => ''), true) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $this->user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				$this->config->set($config_name, $config_value);
			}
		}

		if ($submit)
		{
			trigger_error($this->user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->tpl_name = 'acp_viglink';
		$this->page_title = $display_vars['title'];

		$this->template->assign_vars(array(
				'L_TITLE'			=> $this->user->lang[$display_vars['title']],
				'L_TITLE_EXPLAIN'	=> $this->user->lang[$display_vars['title'] . '_EXPLAIN'],

				'S_ERROR'			=> (sizeof($error)) ? true : false,
				'ERROR_MSG'			=> implode('<br />', $error),

				'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> (isset($this->user->lang[$vars])) ? $this->user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($this->user->lang[$vars['lang_explain']])) ? $this->user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> (isset($this->user->lang[$vars['lang']])) ? $this->user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}
}
