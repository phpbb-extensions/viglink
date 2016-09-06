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
	 * Check whether or not the extension can be enabled.
	 * The current phpBB version should meet or exceed
	 * the minimum version required by this extension:
	 *
	 * Requires phpBB 3.2.0-b1 or greater
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.2.0-b1', '>=');
	}

	/**
	 * Check phpBB's VigLink switches and set them during install
	 *
	 * @param	mixed	$old_state	The return value of the previous call
	 *								of this method, or false on the first call
	 *
	 * @return	mixed				Returns false after last step, otherwise
	 *								temporary state which is passed as an
	 *								argument to the next step
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			/* @var \phpbb\cache\service $cache Cache service object */
			$cache = $this->container->get('cache');

			/* @var \phpbb\config\config $config Config object */
			$config = $this->container->get('config');

			/* @var \phpbb\file_downloader $file_downloader File downloader object*/
			$file_downloader = $this->container->get('file_downloader');

			/* @var \phpbb\user $user user object */
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
		}

		return parent::enable_step($old_state);
	}
}
