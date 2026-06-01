<?php
// FROM HASH: 8b771487533d31a4de18c7e3127fad1d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<mail:subject>' . 'Reported content' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['report']['title']) . '</mail:subject>

' . '<p>The following content has been reported by ' . $__templater->escape($__vars['reporter']['username']) . ' at ' . (((('<a href="' . $__templater->func('link', array('canonical:index', ), true)) . '">') . $__templater->escape($__vars['xf']['options']['boardTitle'])) . '</a>') . '.</p>' . '

<h2><a href="' . $__templater->func('link', array('canonical:reports', $__vars['report'], ), true) . '">' . $__templater->escape($__templater->method($__vars['report'], 'getTitle', array())) . '</a></h2>

<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['message'], 'report', $__vars['report'], ), true) . '</div>

<p>' . 'The following reason was given:' . '</p>
<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['comment']->{'message'}, 'report_comment', $__vars['report'], ), true) . '</div>

<table cellpadding="10" cellspacing="0" border="0" width="100%" class="linkBar">
	<tr>
		<td>
			<a href="' . $__templater->func('link', array('canonical:reports', $__vars['report'], ), true) . '" class="button">' . 'View this report' . '</a>
		</td>
		<td align="' . ($__vars['xf']['isRtl'] ? 'left' : 'right') . '">
			<a href="' . $__templater->func('link', array('canonical:reports', ), true) . '" class="buttonFake">' . 'View all reports' . '</a>
		</td>
	</tr>
</table>';
	return $__finalCompiled;
}
);