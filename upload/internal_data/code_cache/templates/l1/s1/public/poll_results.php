<?php
// FROM HASH: 96ba7fb57c3f75cb3e624832c001d89d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('View results' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['poll']['question']));
	$__finalCompiled .= '

';
	$__templater->setPageParam('head.' . 'metaNoindex', $__templater->preEscaped('<meta name="robots" content="noindex" />'));
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		' . $__templater->callMacro(null, 'poll_macros::result', array(
		'poll' => $__vars['poll'],
		'showFooter' => false,
	), $__vars) . '
	</div>
</div>';
	return $__finalCompiled;
}
);