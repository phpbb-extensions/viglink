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
	/** @var \phpbb\log\log $log */
	protected $log;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service   $cache
	 * @param \phpbb\config\config   $config
	 * @param \phpbb\file_downloader $file_downloader
	 * @param \phpbb\log\log         $log
	 * @param \phpbb\user            $user
	 */
	public function __construct(\phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\file_downloader $file_downloader, \phpbb\log\log $log, \phpbb\user $user)
	{
		parent::__construct($cache, $config, $file_downloader, $user);
		$this->log = $log;
	}

	/**
	 * Obtains the latest VigLink services information from phpBB
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache  Force the use of the cache. Override $force_update.
	 *
	 * @throws \RuntimeException
	 *
	 * @return void
	 */
	public function set_viglink_services($force_update = false, $force_cache = false)
	{
		$cache_file = '_versioncheck_viglink_' . $this->use_ssl;

		$info = $this->cache->get($cache_file);

		if ($info === false && $force_cache)
		{
			throw new \RuntimeException($this->user->lang('VERSIONCHECK_FAIL'));
		}
		else if ($info === false || $force_update)
		{
			try
			{
				$info = $this->file_downloader->get($this->host, $this->path, $this->file, $this->use_ssl ? 443 : 80);
			}
			catch (\phpbb\exception\runtime_exception $exception)
			{
				$prepare_parameters = array_merge(array($exception->getMessage()), $exception->get_parameters());
				throw new \RuntimeException(call_user_func_array(array($this->user, 'lang'), $prepare_parameters));
			}
			$error_string = $this->file_downloader->get_error_string();

			if (!empty($error_string))
			{
				throw new \RuntimeException($error_string);
			}

			if ($info === 0)
			{
				$this->set_viglink_configs(array(
					'allow_viglink_phpbb'	=> false,
					'allow_viglink_global'	=> false,
				));
			}
			else
			{
				$info = 1;
			}

			$this->cache->put($cache_file, $info, 86400); // 24 hours
		}
	}

	/**
	 * Sets VigLink service configs as determined by phpBB
	 *
	 * @param array $data Array of VigLink file data.
	 *
	 * @return void
	 */
	protected function set_viglink_configs($data)
	{
		$viglink_configs = array(
			'allow_viglink_phpbb',
			'allow_viglink_global',
			'phpbb_viglink_api_key',
		);

		foreach ($viglink_configs as $cfg_name)
		{
			if (array_key_exists($cfg_name, $data) && ($data[$cfg_name] != $this->config[$cfg_name] || !isset($this->config[$cfg_name])))
			{
				$this->config->set($cfg_name, $data[$cfg_name]);
			}
		}

		$this->config->set('viglink_last_gc', time(), false);
	}

	/**
	 * Log a VigLink error message to the error log
	 *
	 * @param string $message The error message
	 */
	public function log_viglink_error($message)
	{
		$user_id = empty($this->user->data) ? ANONYMOUS : $this->user->data['user_id'];
		$user_ip = empty($this->user->ip) ? '' : $this->user->ip;

		$this->log->add('critical', $user_id, $user_ip, 'LOG_VIGLINK_CHECK_FAIL', false, array($message));
	}
}
