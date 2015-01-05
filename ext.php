<?php
/**
*
* VigLink extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\viglink;

/**
* Extension class for custom enable/disable/purge actions
*/
class ext extends \phpbb\extension\base
{
	/**
	* Check phpBB's VigLink switches and set them during install
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				/* @var $cache \phpbb\cache\service */
				$cache = $this->container->get('cache');
				/* @var $config \phpbb\config\config */
				$config = $this->container->get('config');
				/* @var $file_downloader \phpbb\file_downloader */
				$file_downloader = $this->container->get('file_downloader');
				/* @var $user \phpbb\user */
				$user = $this->container->get('user');

				$viglink_helper = new \phpbb\viglink\acp\viglink_helper($cache, $config, $file_downloader, $user);
				try
				{
					$viglink_helper->set_viglink_services();
				}
				catch (\RuntimeException $e)
				{
					// fail silently
				}
				return 'viglink';

			break;

			default:

				// Run parent enable step method
				return parent::enable_step($old_state);

			break;
		}
	}
}
