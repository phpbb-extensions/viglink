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
 * Class to handle allowing or disallowing VigLink services
 */
class viglink_helper extends \phpbb\version_helper
{
	/**
	 * Obtains the latest VigLink allow/disallow information from phpBB
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return null
	 * @throws \RuntimeException
	 */
	public function set_viglink_services($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than to the current version
		$versions = array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>=');
		});

		array_map(array($this, 'set_viglink_switches'), $versions);
	}

	/**
	 * Allows/Disallows VigLink services as determined by phpBB
	 *
	 * @param array $data Array of version file data.
	 * @return null
	 */
	protected function set_viglink_switches($data)
	{
		$viglink_switches = array(
			'allow_viglink_phpbb',
			'allow_viglink_global',
		);

		foreach ($viglink_switches as $switch)
		{
			if (isset($data[$switch]) && ($data[$switch] != $this->config[$switch]))
			{
				$this->config->set($switch, (int) $data[$switch]);
			}
		}
	}
}
