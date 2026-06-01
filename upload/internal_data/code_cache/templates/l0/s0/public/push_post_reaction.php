<?php
// FROM HASH: 6e85f2869e60b3161eee1d66e6265215
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '' . ($__templater->escape($__vars['user']['username']) ?: $__templater->escape($__vars['alert']['username'])) . ' reacted to <a ' . '' . '>your post</a> in the thread ' . ($__templater->func('prefix', array('thread', $__vars['content']['Thread'], 'plain', ), true) . $__templater->escape($__vars['content']['Thread']['title'])) . ' with ' . $__templater->func('reaction_title', array($__vars['extra']['reaction_id'], ), true) . '.' . '
<push:url>' . $__templater->func('link', array('canonical:posts', $__vars['content'], ), true) . '</push:url>
<push:tag>post_reaction_' . $__templater->escape($__vars['content']['post_id']) . '_' . $__templater->escape($__vars['extra']['reaction_id']) . '</push:tag>';
	return $__finalCompiled;
}
);