<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_VIGLINK_SETTINGS'			=> 'VigLink settings',
	'ACP_VIGLINK_SETTINGS_EXPLAIN'	=> 'VigLink is a service that can convert links from forum posts into links that earn revenue. Use of this feature requires a <a href="http://www.viglink.com/products/convert/">VigLink Convert</a> account and API key.',
	'ACP_VIGLINK_ENABLE'			=> 'Enable VigLink Convert',
	'ACP_VIGLINK_ENABLE_EXPLAIN'	=> 'Enables use of VigLink services.',
	'ACP_VIGLINK_API_KEY'			=> 'VigLink Convert API key',
	'ACP_VIGLINK_API_KEY_EXPLAIN'	=> 'Enter a valid VigLink Convert API key. To obtain a key, sign up for “VigLink Convert” at <a href="http://www.viglink.com/products/convert/">VigLink.com</a>. Leave this field blank to support phpBB when VigLink is enabled.',
	'ACP_VIGLINK_API_KEY_INVALID'	=> '“%s” is not a valid VigLink Convert API key.',
	'ACP_VIGLINK_DISABLED_GLOBAL'	=> 'VigLink services have been disabled by phpBB.',
	'ACP_VIGLINK_DISABLED_PHPBB'	=> 'VigLink services have been disabled by phpBB. You may still use VigLink to earn revenue using your own API key.',
));
