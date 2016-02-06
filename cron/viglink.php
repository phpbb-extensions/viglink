<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\cron;

/**
 * Viglink cron task.
 */
class viglink extends \phpbb\cron\task\base
{
	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Viglink helper object
	 * @var \phpbb\viglink\acp\viglink_helper
	 */
	protected $helper;

	/**
	 * Log object
	 * @var \phpbb\log\log
	 */
	protected $log;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config              $config         Config object
	 * @param \phpbb\viglink\acp\viglink_helper $viglink_helper Viglink helper object
	 * @param \phpbb\log\log                    $log            Log object
	 * @param \phpbb\user                       $user           User object
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\viglink\acp\viglink_helper $viglink_helper, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->config = $config;
		$this->helper = $viglink_helper;
		$this->log = $log;
		$this->user = $user;
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		try
		{
			$this->helper->set_viglink_services(true);
		}
		catch (\RuntimeException $e)
		{
			$user_id = (empty($this->user->data)) ? ANONYMOUS : $this->user->data['user_id'];
			$user_ip = (empty($this->user->ip)) ? '' : $this->user->ip;

			$this->log->add('critical', $user_id, $user_ip, 'LOG_VIGLINK_CHECK_FAIL', false, array($e->getMessage()));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function is_runnable()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function should_run()
	{
		return $this->config['viglink_last_gc'] < strtotime('24 hours ago');
	}
}
