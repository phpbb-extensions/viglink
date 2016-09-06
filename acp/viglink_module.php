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
		global $config, $request,  $template, $user;

		$user->add_lang_ext('phpbb/viglink', 'viglink_module_acp');

		$this->tpl_name = 'acp_viglink';
		$this->page_title = $user->lang('ACP_VIGLINK_SETTINGS');

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
			$error[] = $user->lang('FORM_INVALID');
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
				$error[] = $user->lang('ACP_VIGLINK_API_KEY_INVALID', $cfg_array['viglink_api_key']);
			}

			// If no errors, set the config values
			if (!sizeof($error))
			{
				foreach ($cfg_array as $cfg => $value)
				{
					$config->set($cfg, $value);
				}

				trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
		}

		// Set a general error message if VigLink has been disabled by phpBB
		if (!$config['allow_viglink_global'])
		{
			$error[] = $user->lang('ACP_VIGLINK_DISABLED_GLOBAL');
		}
		else if (!$config['allow_viglink_phpbb'] && !$cfg_array['viglink_api_key'])
		{
			$error[] = $user->lang('ACP_VIGLINK_DISABLED_PHPBB');
		}

		$template->assign_vars(array(
			'S_ERROR'			=> (bool) sizeof($error),
			'ERROR_MSG'			=> implode('<br />', $error),

			'VIGLINK_ENABLED'	=> $cfg_array['viglink_enabled'],
			'VIGLINK_API_KEY'	=> $cfg_array['viglink_api_key'],

			'U_ACTION'			=> $this->u_action,
		));
	}
}
