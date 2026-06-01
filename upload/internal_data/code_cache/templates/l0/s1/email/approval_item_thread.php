<?php
// FROM HASH: 8bad789d9c27fb3a64468cab585147cc
return array(
'extends' => function($__templater, array $__vars) { return 'approval_item_create'; },
'extensions' => array('header' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<h2>
		' . 'Thread <a href="' . $__templater->func('link', array('canonical:threads', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['title']) . '</a> posted in forum <a href="' . $__templater->func('link', array('canonical:forums', $__vars['content']['Forum'], ), true) . '">' . $__templater->escape($__vars['content']['Forum']['title']) . '</a>' . '
	</h2>
';
	return $__finalCompiled;
},
'content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['content']['FirstPost']['message'], 'post', $__vars['content']['FirstPost'], ), true) . '</div>

	<p>' . 'By ' . $__templater->func('username_link_email', array($__vars['content']['User'], $__vars['content']['username'], ), true) . '' . '</p>
';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . $__templater->renderExtension('header', $__vars, $__extensions) . '

' . $__templater->renderExtension('content', $__vars, $__extensions);
	return $__finalCompiled;
}
);