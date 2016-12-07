<?php
/**
*
* VigLink extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\viglink\tests\functional;

/**
* @group functional
*/
class viglink_test extends \phpbb_functional_test_case
{
	protected $sample_viglink_key = 'viglinkTestCodephpBB123456789012';

	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array('phpbb/viglink');
	}

	/**
	* Test VigLink ACP page and save settings
	*/
	public function test_set_acp_settings()
	{
		$this->login();
		$this->admin_login();

		// Add language files
		$this->add_lang('acp/board');
		$this->add_lang_ext('phpbb/viglink', 'viglink_module_acp');

		// Load ACP board settings page
		$crawler = self::request('GET', 'adm/index.php?i=\phpbb\viglink\acp\viglink_module&mode=settings&sid=' . $this->sid);
		$this->assertContainsLang('ACP_VIGLINK_SETTINGS', $crawler->text());

		// Set VigLink form values
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();
		$values['viglink_enabled'] = true;
		$form->setValues($values);

		// Submit form and test success
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('.successbox')->text());
	}

	/**
	* Test VigLink code appears as expected
	*/
	public function test_viglink_code()
	{
		$db = $this->get_db();
		$sql = 'SELECT config_value AS api_key
			FROM ' . CONFIG_TABLE . "
			WHERE config_name = 'phpbb_viglink_api_key'";
		$result = $db->sql_query($sql);
		$api_key = $db->sql_fetchfield('api_key');
		$db->sql_freeresult($result);

		// Assert VigLink appears on viewtopic pages
		$crawler = self::request('GET', 'viewtopic.php?f=2&t=1');
		$this->assertContains($api_key, $crawler->text());

		// Assert VigLink does not appear on other pages
		$crawler = self::request('GET', 'index.php');
		$this->assertNotContains($api_key, $crawler->text());
	}
}
