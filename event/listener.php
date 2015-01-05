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
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\config\config        $config             Config object
	* @param \phpbb\template\template    $template           Template object
	* @return \phpbb\viglink\event\listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->template = $template;
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
			'core.viewtopic_post_row_after'		=> 'display_viglink',
		);
	}

	/**
	* Display VigLink js code
	*
	* @return null
	* @access public
	*/
	public function display_viglink()
	{
		if ($this->config['allow_viglink_global'] && $this->config['viglink_api_key'])
		{
			// Use custom API key if set and if VigLink is allowed for all
			$viglink_key = $this->config['viglink_api_key'];
		}
		else if ($this->config['allow_viglink_phpbb'] && $this->config['phpbb_viglink_api_key'])
		{
			// Use phpBB API key if VigLink is allowed for phpBB
			$viglink_key = $this->config['phpbb_viglink_api_key'];
		}
		else
		{
			$viglink_key = '';
		}

		$this->template->assign_vars(array(
			'VIGLINK_ENABLED'	=> ($this->config['viglink_enabled'] && $viglink_key) ? true : false,
			'VIGLINK_API_KEY'	=> $viglink_key,
		));
	}
}
