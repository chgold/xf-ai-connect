<?php
// FROM HASH: 382dc9e16745e3f7f4e00512ee0b019c
return array(
'extensions' => array('body' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	' . '<p>There is content awaiting approval at ' . (((('<a href="' . $__templater->func('link', array('canonical:index', ), true)) . '">') . $__templater->escape($__vars['xf']['options']['boardTitle'])) . '</a>') . '.</p>' . '
';
	return $__finalCompiled;
},
'header' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<h2>
		<a href="' . $__templater->escape($__vars['link']) . '">' . $__templater->escape($__vars['title']) . '</a>
	</h2>
';
	return $__finalCompiled;
},
'content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'footer' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<table cellpadding="10" cellspacing="0" border="0" width="100%" class="linkBar">
		<tr>
			<td>
				<a href="' . $__templater->escape($__vars['link']) . '" class="button">' . 'View content' . '</a>
			</td>
			<td align="' . ($__vars['xf']['isRtl'] ? 'left' : 'right') . '">
				<a href="' . $__templater->func('link', array('canonical:approval-queue', ), true) . '" class="buttonFake">' . 'View approval queue' . '</a>
			</td>
		</tr>
	</table>
';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<mail:subject>' . 'Content awaiting approval' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['title']) . '</mail:subject>

' . $__templater->renderExtension('body', $__vars, $__extensions) . '

' . $__templater->renderExtension('header', $__vars, $__extensions) . '

' . $__templater->renderExtension('content', $__vars, $__extensions) . '

' . $__templater->renderExtension('footer', $__vars, $__extensions);
	return $__finalCompiled;
}
);