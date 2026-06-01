<?php
// FROM HASH: af287ae327e2b240c862493250ac7506
return array(
'extends' => function($__templater, array $__vars) { return 'approval_item_create'; },
'extensions' => array('content' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	<div class="message">' . $__templater->func('bb_code_type', array('emailHtml', $__vars['content']['message'], 'profile_post_comment', $__vars['content'], ), true) . '</div>

	<p>
		' . 'Posted on profile post by <a href="' . $__templater->func('link', array('canonical:profile-posts/comments', $__vars['content'], ), true) . '">' . $__templater->escape($__vars['content']['ProfilePost']['username']) . '</a>' . '
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