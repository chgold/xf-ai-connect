<?php
// FROM HASH: b1bd3fe96343f967ec56ba39984d6e6e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Help');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'metadata_macros::canonical_url', array(
		'canonicalUrl' => $__templater->func('link', array('canonical:help', ), false),
	), $__vars) . '

';
	$__templater->wrapTemplate('help_wrapper', $__vars);
	$__finalCompiled .= '

';
	if (!$__templater->test($__vars['pages'], 'empty', array())) {
		$__finalCompiled .= '
	<div class="block">
		<div class="block-container">
			<div class="block-body">
				';
		if ($__templater->isTraversable($__vars['pages'])) {
			foreach ($__vars['pages'] AS $__vars['page']) {
				$__finalCompiled .= '
					';
				if (((($__vars['page']['page_id'] == 'trophies') AND $__vars['xf']['options']['enableTrophies'])) OR ($__vars['page']['page_id'] != 'trophies')) {
					$__finalCompiled .= '
						<div class="block-row block-row--separated">
							<h3 class="block-textHeader">
								<a href="' . $__templater->escape($__vars['page']['public_url']) . '">' . $__templater->escape($__vars['page']['title']) . '</a>
							</h3>
							' . $__templater->escape($__vars['page']['description']) . '
						</div>
					';
				}
				$__finalCompiled .= '
				';
			}
		}
		$__finalCompiled .= '
			</div>
		</div>
	</div>
';
	} else {
		$__finalCompiled .= '
	<div class="blockMessage">' . 'No items have been created yet.' . '</div>
';
	}
	return $__finalCompiled;
}
);