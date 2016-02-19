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
* Event listener
*/
class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\viglink\acp\viglink_helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\request\request $request phpBB request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\viglink\acp\viglink_helper $viglink_helper Viglink helper object
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\viglink\acp\viglink_helper $viglink_helper)
	{
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->helper = $viglink_helper;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_main_notice'		=> 'set_viglink_services',
			'core.acp_help_phpbb_submit_before'	=> 'update_viglink_settings',
		);
	}

	/**
	* Check if phpBB is allowing VigLink services to run.
	* VigLink will be disabled if phpBB is disallowing it to run.
	*
	* @return null
	* @access public
	*/
	public function set_viglink_services()
	{
		try
		{
			$this->helper->set_viglink_services();
		}
		catch (\RuntimeException $e)
		{
			// fail silently
		}
	}

	/**
	 * Update viglink settings
	 *
	 * @param array $event Event data
	 */
	public function update_viglink_settings($event)
	{
		$viglink_setting = $this->request->variable('enable-viglink', false);

		if (!empty($event['submit']))
		{
			$this->config->set('allow_viglink_phpbb', $viglink_setting);
		}

		$this->template->assign_vars(array(
			'S_ENABLE_VIGLINK'		=> !empty($this->config['enable_viglink']),
		));
	}
}
