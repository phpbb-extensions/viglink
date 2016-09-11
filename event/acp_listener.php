<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ACP Event listener
 */
class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config $config Config object */
	protected $config;

	/** @var \phpbb\request\request_interface $request Request interface */
	protected $request;

	/** @var \phpbb\template\template $template Template object */
	protected $template;

	/** @var \phpbb\language\language $language Language object */
	protected $language;

	/** @var \phpbb\viglink\acp\viglink_helper $helper VigLink helper object */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\language\language $language
	 * @param \phpbb\request\request_interface $request phpBB request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\viglink\acp\viglink_helper $viglink_helper Viglink helper object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\viglink\acp\viglink_helper $viglink_helper)
	{
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->helper = $viglink_helper;
	}

	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_main_notice'				=> 'set_viglink_services',
			'core.acp_help_phpbb_submit_before'	=> 'update_viglink_settings',
		);
	}

	/**
	 * Check if phpBB is allowing VigLink services to run.
	 *
	 * VigLink will be disabled if phpBB is disallowing it to run.
	 *
	 * @return void
	 */
	public function set_viglink_services()
	{
		try
		{
			$this->helper->set_viglink_services();
		}
		catch (\RuntimeException $e)
		{
			$this->helper->log_viglink_error($e->getMessage());
		}
	}

	/**
	 * Update VigLink settings
	 *
	 * @param array $event Event data
	 *
	 * @return void
	 */
	public function update_viglink_settings($event)
	{
		$this->language->add_lang('viglink_module_acp', 'phpbb/viglink');

		$viglink_setting = $this->request->variable('enable-viglink', false);

		if (!empty($event['submit']))
		{
			$this->config->set('viglink_enabled', $viglink_setting);
		}

		$this->template->assign_vars(array(
			'S_ENABLE_VIGLINK'		=> !empty($this->config['viglink_enabled']),
		));
	}
}
