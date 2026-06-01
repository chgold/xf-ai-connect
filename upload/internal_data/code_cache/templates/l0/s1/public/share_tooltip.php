<?php
// FROM HASH: 4abde374f2eb19b58c343c448121c139
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="tooltip-content-inner">
	<div class="block-minorHeader">' . $__templater->escape($__vars['tooltipTitle']) . '</div>
	';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
				' . $__templater->callMacro(null, 'share_page_macros::buttons', array(
		'iconic' => true,
		'hideLink' => true,
		'pageUrl' => $__vars['contentUrl'],
		'pageTitle' => $__vars['contentTitle'],
		'pageDesc' => $__vars['contentDesc'],
	), $__vars) . '
			';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
		<div class="block-body block-row block-row--separated block-row--separated--mergePrev">
			' . $__compilerTemp1 . '
		</div>
	';
	}
	$__finalCompiled .= '
	<div class="block-body block-row block-row--separated">
		' . $__templater->callMacro(null, 'share_page_macros::share_clipboard_input', array(
		'label' => 'Copy link',
		'text' => $__vars['contentUrl'],
		'successText' => 'Link copied to clipboard.',
	), $__vars) . '
	</div>
	';
	if ($__vars['xf']['options']['embedCodeShare'] AND $__vars['contentEmbedCode']) {
		$__finalCompiled .= '
		<div class="block-body block-row block-row--separated">
			' . $__templater->callMacro(null, 'share_page_macros::share_clipboard_input', array(
			'label' => 'Copy embed code HTML',
			'text' => $__vars['contentEmbedCode'],
			'successText' => 'Embed code HTML copied to clipboard.',
		), $__vars) . '
		</div>
	';
	}
	$__finalCompiled .= '
</div>';
	return $__finalCompiled;
}
);