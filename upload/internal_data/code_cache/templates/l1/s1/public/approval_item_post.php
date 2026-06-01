<?php
// FROM HASH: 1d193b5ff3c7f788b3d1823671a8bfac
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'approval_queue_macros::item_message_type', array(
		'content' => $__vars['content'],
		'user' => $__vars['content']['User'],
		'messageHtml' => $__templater->func('bb_code', array($__vars['content']['message'], 'post', $__vars['content'], ), false),
		'typePhraseHtml' => 'Post',
		'spamDetails' => $__vars['spamDetails'],
		'unapprovedItem' => $__vars['unapprovedItem'],
		'handler' => $__vars['handler'],
		'headerPhraseHtml' => 'Posted in thread <a href="' . $__templater->func('link', array('posts', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['Thread']['title']) . '</a> in forum <a href="' . $__templater->func('link', array('forums', $__vars['content']['Thread']['Forum'], ), true) . '">' . $__templater->escape($__vars['content']['Thread']['Forum']['title']) . '</a>',
	), $__vars) . '
';
	return $__finalCompiled;
}
);