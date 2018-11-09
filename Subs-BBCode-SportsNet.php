<?php
/**********************************************************************************
* Subs-BBCode-Sports.php
***********************************************************************************
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/

function BBCode_SportsNet(&$bbc)
{
	// Format: [sportsnet width=x height=x]{playlist ID}[/sportsnet]
	$bbc[] = array(
		'tag' => 'sportsnet',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('value' => ' width="$1"', 'match' => '(\d+)'),
			'height' => array('value' => ' height="$1"', 'match' => '(\d+)'),
		),
		'validate' => 'BBCode_SportsNet_Validate',
		'content' => '<iframe class="youtube-player" type="text/html"{width}{height} src="$1" allowfullscreen frameborder="0"></iframe>',
		'disabled_content' => '$1',
	);

	// Format: [sportsnet]{playlist ID}[/sportsnet]
	$bbc[] = array(
		'tag' => 'sportsnet',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_SportsNet_Validate',
		'content' => '<iframe class="youtube-player" type="text/html" width="640" height="400" src="$1" allowfullscreen frameborder="0"></iframe>',
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
	global $context;
	
	$data = strtr($data, array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	$md5 = md5($data);
	if (($data = cache_get_data('sportsnet_' . $md5, 86400)) == null)
	{
		$content = file_get_contents($data);
		$pattern = '#meta name="twitter:player" content="(.+?)"#i' . ($context['utf8'] ? 'u' : '');
		preg_match($pattern, $content, $codes, PREG_PATTERN_ORDER);
		$data = (isset($codes[1]) ? $codes[1] : '');
		cache_put_data('sportsnet_' . $md5, $data, 86400);
	}
}

?>