<?php
// FROM HASH: 8a04d4666a1f77da6a441e3fb5a257a1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('embed_resolver.less');
	$__finalCompiled .= '

<div class="embed fauxBlockLink" data-embed-content="' . $__templater->escape($__templater->method($__vars['content'], 'getEntityContentTypeId', array())) . '" data-embed-content-url="' . $__templater->escape($__vars['contentUrl']) . '">
	' . $__templater->filter($__templater->method($__vars['content'], 'renderEmbed', array()), array(array('raw', array()),), true) . '
</div>

<script' . ($__templater->method($__vars['xf']['request'], 'isRocketLoaderDisabled', array()) ? ' data-cfasync="false"' : '') . '>
	window.addEventListener(\'message\', receiveMessage, false);

	function receiveMessage(event)
	{
		if (event.data.type !== \'getHeight\')
		{
			return;
		}

		let allImages = document.querySelectorAll(\'img\');

		if (allImages.length)
		{
			let loaded = 0;

			for (let i = 0; i < allImages.length; i++)
			{
				// remove lazy loading in a lazy way...
				allImages[i].removeAttribute(\'loading\');

				if (allImages[i].complete)
				{
					loaded++;
					if (loaded === allImages.length)
					{
						const height = document.body.scrollHeight;
						event.source.postMessage({ messageId: event.data.messageId, height: height }, event.origin);
					}
				}
				else
				{
					allImages[i].onload = function()
					{
						loaded++;
						if (loaded === allImages.length)
						{
							const height = document.body.scrollHeight;
							event.source.postMessage({ messageId: event.data.messageId, height: height }, event.origin);
						}
					}
				}
			}
		}
		else
		{
			const height = document.body.scrollHeight;
			event.source.postMessage({ messageId: event.data.messageId, height: height }, event.origin);
		}
	}
</script>';
	return $__finalCompiled;
}
);