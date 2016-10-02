<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\acp;

/**
 * VigLink ACP module
 */
class viglink_module
{
	/** @var string $u_action Custom form action */
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\config\config $config Config object */
		$config = $phpbb_container->get('config');

		/** @var \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		/** @var \phpbb\request\request $request Request object */
		$request  = $phpbb_container->get('request');

		/** @var \phpbb\template\template $template Template object */
		$template = $phpbb_container->get('template');

		$language->add_lang('viglink_module_acp', 'phpbb/viglink');

		$this->tpl_name = 'acp_viglink';
		$this->page_title = $language->lang('ACP_VIGLINK_SETTINGS');

		$submit = $request->is_set_post('submit');

		if ($mode !== 'settings')
		{
			return;
		}

		$form_key = 'acp_viglink';
		add_form_key($form_key);

		$error = array();

		// Get stored config/default values
		$cfg_array = array(
			'viglink_enabled' => isset($config['viglink_enabled']) ? $config['viglink_enabled'] : 0,
			'viglink_api_key' => isset($config['viglink_api_key']) ? $config['viglink_api_key'] : '',
		);

		// Error if the form is invalid
		if ($submit && !check_form_key($form_key))
		{
			$error[] = $language->lang('FORM_INVALID');
		}

		// Do not process form if invalid
		if (sizeof($error))
		{
			$submit = false;
		}

		if ($submit)
		{
			// Get the VigLink form field values
			$cfg_array['viglink_enabled'] = $request->variable('viglink_enabled', 0);
			$cfg_array['viglink_api_key'] = $request->variable('viglink_api_key', '');

			// Error if the input is not a valid VigLink API Key
			if ($cfg_array['viglink_api_key'] != '' && !preg_match('/^[A-Za-z0-9]{32}$/', $cfg_array['viglink_api_key']))
			{
				$error[] = $language->lang('ACP_VIGLINK_API_KEY_INVALID', $cfg_array['viglink_api_key']);
			}

			// If no errors, set the config values
			if (!sizeof($error))
			{
				foreach ($cfg_array as $cfg => $value)
				{
					$config->set($cfg, $value);
				}

				trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
		}

		if (!isset($config['questionnaire_unique_id']))
		{
			$config->set('questionnaire_unique_id', unique_id());
		}

		// Set a general error message if VigLink has been disabled by phpBB
		if (!$config['allow_viglink_global'])
		{
			$error[] = $language->lang('ACP_VIGLINK_DISABLED_GLOBAL');
		}
		else if (!$config['allow_viglink_phpbb'] && !$cfg_array['viglink_api_key'])
		{
			$error[] = $language->lang('ACP_VIGLINK_DISABLED_PHPBB');
		}

		$template->assign_vars(array(
			'S_ERROR'				=> (bool) sizeof($error),
			'ERROR_MSG'				=> implode('<br />', $error),

			'VIGLINK_ENABLED'		=> $cfg_array['viglink_enabled'],
			'VIGLINK_API_KEY'		=> $cfg_array['viglink_api_key'],

			'U_VIGLINK_CONVERT'		=> 'https://www.phpbb.com/viglink/convert.php?subId=' .  md5($config['questionnaire_unique_id'] . $config['sitename']) . '&amp;key=' . $config['phpbb_viglink_api_key'],
			'U_ACTION'				=> $this->u_action,
		));
	}
}
