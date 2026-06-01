<?php
// FROM HASH: 61881d892d3c23f05002fd788aeb3645
return array(
'extends' => function($__templater, array $__vars) { return 'approval_item_create'; },
'extensions' => array('content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['content']['message'], 'profile_post', $__vars['content'], ), true) . '</div>

	<p>
		' . 'Posted on profile <a href="' . $__templater->func('link', array('canonical:profile-posts', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['ProfileUser']['username']) . '</a>' . '
	</p>
';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . $__templater->renderExtension('content', $__vars, $__extensions);
	return $__finalCompiled;
}
);