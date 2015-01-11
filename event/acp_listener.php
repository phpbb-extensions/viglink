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
	/** @var \phpbb\viglink\acp\viglink_helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\viglink\acp\viglink_helper $viglink_helper Viglink helper object
	 * @access public
	 */
	public function __construct(\phpbb\viglink\acp\viglink_helper $viglink_helper)
	{
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
}
