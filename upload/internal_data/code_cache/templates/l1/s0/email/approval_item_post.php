<?php
// FROM HASH: ae7cb9e4d71da9f8f73189d908ee4b70
return array(
'extends' => function($__templater, array $__vars) { return 'approval_item_create'; },
'extensions' => array('content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['content']['message'], 'post', $__vars['content'], ), true) . '</div>

	<p>' . 'By ' . $__templater->func('username_link_email', array($__vars['content']['User'], $__vars['content']['username'], ), true) . '' . '</p>
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