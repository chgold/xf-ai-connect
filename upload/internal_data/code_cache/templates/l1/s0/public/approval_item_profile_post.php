<?php
// FROM HASH: 0f245da323d7c0473c62555084be8178
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'approval_queue_macros::item_message_type', array(
		'content' => $__vars['content'],
		'user' => $__vars['content']['User'],
		'messageHtml' => $__templater->func('bb_code', array($__vars['content']['message'], 'profile_post', $__vars['content'], ), false),
		'typePhraseHtml' => 'Profile post',
		'spamDetails' => $__vars['spamDetails'],
		'unapprovedItem' => $__vars['unapprovedItem'],
		'handler' => $__vars['handler'],
		'headerPhraseHtml' => 'Posted on profile <a href="' . $__templater->func('link', array('profile-posts', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['ProfileUser']['username']) . '</a>',
	), $__vars);
	return $__finalCompiled;
}
);