<?php
// FROM HASH: 800450e6500b35ddc68ccd3ca072d1de
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('embed_resolver.less');
	$__finalCompiled .= '

<div class="embed fauxBlockLink" data-embed-content="' . $__templater->escape($__templater->method($__vars['content'], 'getEntityContentTypeId', array())) . '" data-embed-content-url="' . $__templater->escape($__vars['contentUrl']) . '">
	' . $__templater->filter($__templater->method($__vars['content'], 'renderEmbed', array()), array(array('raw', array()),), true) . '
</div>';
	return $__finalCompiled;
}
);