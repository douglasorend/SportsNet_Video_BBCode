<?php
/**********************************************************************************
* Subs-BBCode-SportsNet.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF')) 
	die('Hacking attempt...');

function BBCode_SportsNet(&$bbc)
{
	// Format: [sportsnet width=x height=x]{video URL}[/sportsnet]
	$bbc[] = array(
		'tag' => 'sportsnet',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('match' => '(\d+)'),
			'height' => array('optional' => true, 'match' => '(\d+)'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
		),
		'validate' => 'BBCode_SportsNet_Validate',
		'content' => '{width}|{height}|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [sportsnet]{video URL}[/sportsnet]
	$bbc[] = array(
		'tag' => 'sportsnet',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_SportsNet_Validate',
		'content' => '0|0|0',
		'disabled_content' => '$1',
	);
}

function BBCode_SportsNet_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'sportsnet',
		'code' => 'sportsnet',
		'description' => 'sportsnet',
		'before' => '[sportsnet]',
		'after' => '[/sportsnet]',
	);
}

function BBCode_SportsNet_Validate(&$tag, &$data, &$disabled)
{
	global $context, $modSettings, $txt;
	
	if (empty($data))
		return ($tag['content'] = $txt['sportsnet_invalid']);
	list($width, $height, $frameborder) = explode('|', $tag['content']);
	if (empty($width) && !empty($modSettings['sportsnet_default_width']))
		$width = $modSettings['sportsnet_default_width'];
	if (empty($height) && !empty($modSettings['sportsnet_default_height']))
		$height = $modSettings['sportsnet_default_height'];
	$data = strtr(trim($data), array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	$md5 = md5($data);
	if (!preg_match('#(http|https):\/\/www\.sportsnet\.ca/#', $data))
		return ($tag['content'] = $txt['sportsnet_invalid']);
	if (!preg_match('#\?(|row=(\d+)&amp;)row_ids?=(\d+)#', $data))
		return ($tag['content'] = $txt['sportsnet_invalid']);
	if (($results = cache_get_data('sportsnet_' . $md5, 86400)) == null)
	{
		$content = @file_get_contents($data);
		$pattern = '#meta name="twitter:player" content="(.+?)"#i' . ($context['utf8'] ? 'u' : '');
		preg_match($pattern, $content, $codes);
		$results = (isset($codes[1]) ? $codes[1] : '');
		if (!empty($results))
			cache_put_data('sportsnet_' . $md5, $results, 86400);
	}
	if (!empty($results))
		$tag['content'] = '<div' . ((empty($width) || empty($height)) ? '' : ' style="max-width: ' . $width . 'px; max-height: ' . $height . 'px;"') . '><div class="sportsnet-wrapper"><iframe class="sportsnet-player" type="text/html" src="' . $results .'" allowfullscreen frameborder="' . $frameborder . '"></iframe></div></div>';
	else
		$tag['content'] = $txt['sportsnet_cannot_get_video'];
}

function BBCode_SportsNet_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'sportsnet_default_width');
	$config_vars[] = array('int', 'sportsnet_default_height');
}

function BBCode_SportsNet_Theme()
{
	global $context, $settings;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/BBCode-SportsNet.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
}

function BBCode_Sportsnet_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[sportsnet]$0[/sportsnet]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(https?\:\/\/)(?:www\.)?sportsnet\.ca/?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*/\?row=(\d+)&amp;row_ids?=(\d+)+\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false)
		$message = preg_replace('#\[sportsnet.*\](.*)\[\/sportsnet\]#i', '[url]$1[/url]', $message);
}

?>