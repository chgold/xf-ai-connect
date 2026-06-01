<?php
// FROM HASH: 20e61b7b9463a4fe194eb7b945414cf0
return array(
'macros' => array('head' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'app' => '!',
		'jsState' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__vars['cssUrls'] = array('public:normalize.css', 'public:fa.css', );
	$__finalCompiled .= '
	';
	if ($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array())) {
		$__finalCompiled .= '
		';
		$__vars['cssUrls'] = $__templater->func('array_merge', array($__vars['cssUrls'], array('public:variations.less', ), ), false);
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '
	';
	$__vars['cssUrls'] = $__templater->func('array_merge', array($__vars['cssUrls'], array('public:core.less', $__vars['app'] . ':app.less', ), ), false);
	$__finalCompiled .= '

	<link rel="stylesheet" href="' . $__templater->func('css_url', array($__vars['cssUrls'], ), true) . '" />

	<!--XF:CSS-->

	';
	if ($__vars['xf']['fullJs']) {
		$__finalCompiled .= '
		<script' . ($__templater->method($__vars['xf']['request'], 'isRocketLoaderDisabled', array()) ? ' data-cfasync="false"' : '') . ' src="' . $__templater->func('js_url', array('xf/preamble.js', ), true) . '"></script>
	';
	} else {
		$__finalCompiled .= '
		<script' . ($__templater->method($__vars['xf']['request'], 'isRocketLoaderDisabled', array()) ? ' data-cfasync="false"' : '') . ' src="' . $__templater->func('js_url', array('xf/preamble.min.js', ), true) . '"></script>
	';
	}
	$__finalCompiled .= '

	' . $__templater->func('core_js') . '

	<script' . ($__templater->method($__vars['xf']['request'], 'isRocketLoaderDisabled', array()) ? ' data-cfasync="false"' : '') . '>
		window.addEventListener(\'DOMContentLoaded\', function() {
			XF.extendObject(true, XF.config, {
				// ' . '
				userId: ' . $__templater->escape($__vars['xf']['visitor']['user_id']) . ',
				enablePush: ' . ($__vars['xf']['options']['enablePush'] ? 'true' : 'false') . ',
				pushAppServerKey: \'' . $__templater->escape($__vars['xf']['options']['pushKeysVAPID']['publicKey']) . '\',
				url: {
					fullBase: \'' . $__templater->filter($__templater->func('base_url', array(null, true, ), false), array(array('escape', array('js', )),), true) . '\',
					basePath: \'' . $__templater->filter($__templater->func('base_url', array(null, false, ), false), array(array('escape', array('js', )),), true) . '\',
					css: \'' . $__templater->filter($__templater->func('css_url', array(array('__SENTINEL__', ), false, ), false), array(array('escape', array('js', )),), true) . '\',
					js: \'' . $__templater->filter($__templater->func('js_url', array('__SENTINEL__', ), false), array(array('escape', array('js', )),), true) . '\',
					icon: \'' . $__templater->filter($__templater->func('icon_url', array('__VARIANT__', '__NAME__', ), false), array(array('escape', array('js', )),), true) . '\',
					iconInline: \'' . $__templater->filter($__templater->func('icon_url', array('__VARIANT__', '__NAME__', true, ), false), array(array('escape', array('js', )),), true) . '\',
					keepAlive: \'' . $__templater->filter($__templater->func('link_type', array($__vars['app'], 'login/keep-alive', ), false), array(array('escape', array('js', )),), true) . '\'
				},
				cookie: {
					path: \'' . $__templater->filter($__vars['xf']['cookie']['path'], array(array('escape', array('js', )),), true) . '\',
					domain: \'' . $__templater->filter($__vars['xf']['cookie']['domain'], array(array('escape', array('js', )),), true) . '\',
					prefix: \'' . $__templater->filter($__vars['xf']['cookie']['prefix'], array(array('escape', array('js', )),), true) . '\',
					secure: ' . ($__vars['xf']['cookie']['secure'] ? 'true' : 'false') . ',
					consentMode: \'' . $__templater->escape($__templater->method($__vars['xf']['cookieConsent'], 'getMode', array())) . '\',
					consented: ' . $__templater->filter($__templater->method($__vars['xf']['cookieConsent'], 'getConsentedGroups', array()), array(array('json', array()),array('raw', array()),), true) . '
				},
				cacheKey: \'' . $__templater->filter($__templater->func('cache_key', array(), false), array(array('escape', array('js', )),), true) . '\',
				csrf: \'' . $__templater->filter($__templater->func('csrf_token', array(), false), array(array('escape', array('js', )),), true) . '\',
				js: {\'<!--XF:JS:JSON-->\'},
				fullJs: ' . ($__vars['xf']['fullJs'] ? 'true' : 'false') . ',
				css: {\'<!--XF:CSS:JSON-->\'},
				time: {
					now: ' . $__templater->escape($__vars['xf']['time']) . ',
					today: ' . $__templater->escape($__vars['xf']['timeDetails']['today']) . ',
					todayDow: ' . $__templater->escape($__vars['xf']['timeDetails']['todayDow']) . ',
					tomorrow: ' . $__templater->escape($__vars['xf']['timeDetails']['tomorrow']) . ',
					yesterday: ' . $__templater->escape($__vars['xf']['timeDetails']['yesterday']) . ',
					week: ' . $__templater->escape($__vars['xf']['timeDetails']['week']) . ',
					month: ' . $__templater->escape($__vars['xf']['timeDetails']['month']) . ',
					year: ' . $__templater->escape($__vars['xf']['timeDetails']['year']) . '
				},
				style: {
					light: \'' . ($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) ? $__templater->filter($__templater->method($__vars['xf']['style'], 'getStyleTypeVariation', array('light', )), array(array('escape', array('js', )),), true) : '') . '\',
					dark: \'' . ($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) ? $__templater->filter($__templater->method($__vars['xf']['style'], 'getStyleTypeVariation', array('dark', )), array(array('escape', array('js', )),), true) : '') . '\',
					defaultColorScheme: \'' . $__templater->filter($__templater->func('property_variation', array('styleType', 'default', ), false), array(array('escape', array('js', )),), true) . '\'
				},
				borderSizeFeature: \'' . $__templater->func('property', array('borderSizeFeature', ), true) . '\',
				fontAwesomeWeight: \'' . $__templater->func('fa_weight', array(), true) . '\',
				enableRtnProtect: ' . ($__vars['xf']['enableRtnProtect'] ? 'true' : 'false') . ',
				';
	if ($__vars['xf']['serviceWorkerPath'] !== null) {
		$__finalCompiled .= '
					serviceWorkerPath: \'' . $__templater->filter($__vars['xf']['serviceWorkerPath'], array(array('escape', array('js', )),), true) . '\',
				';
	}
	$__finalCompiled .= '
				enableFormSubmitSticky: ' . ($__templater->func('property', array('formSubmitSticky', ), false) ? 'true' : 'false') . ',
				imageOptimization: \'' . $__templater->escape($__vars['xf']['options']['imageOptimization']) . '\',
				imageOptimizationQuality: ' . ($__vars['xf']['app']['config']['maxImageResizeQuality'] / 100) . ',
				uploadMaxFilesize: ' . $__templater->escape($__vars['xf']['uploadMaxFilesize']) . ',
				uploadMaxWidth: ' . ($__templater->escape($__vars['xf']['options']['attachmentMaxDimensions']['width']) ?: 0) . ',
				uploadMaxHeight: ' . ($__templater->escape($__vars['xf']['options']['attachmentMaxDimensions']['height']) ?: 0) . ',
				allowedVideoExtensions: ' . $__templater->filter($__vars['xf']['allowedVideoExtensions'], array(array('json', array()),array('raw', array()),), true) . ',
				allowedAudioExtensions: ' . $__templater->filter($__vars['xf']['allowedAudioExtensions'], array(array('json', array()),array('raw', array()),), true) . ',
				shortcodeToEmoji: ' . ($__vars['xf']['options']['shortcodeToEmoji'] ? 'true' : 'false') . ',
				visitorCounts: {
					conversations_unread: \'' . $__templater->filter($__vars['xf']['visitor']['conversations_unread'], array(array('number', array()),), true) . '\',
					alerts_unviewed: \'' . $__templater->filter($__vars['xf']['visitor']['alerts_unviewed'], array(array('number', array()),), true) . '\',
					total_unread: \'' . $__templater->filter(($__vars['xf']['visitor']['conversations_unread'] + $__vars['xf']['visitor']['alerts_unviewed']), array(array('number', array()),), true) . '\',
					title_count: ' . ($__templater->func('in_array', array($__vars['xf']['options']['displayVisitorCount'], array('title_count', 'title_and_icon', ), ), false) ? 'true' : 'false') . ',
					icon_indicator: ' . ($__templater->func('in_array', array($__vars['xf']['options']['displayVisitorCount'], array('icon_indicator', 'title_and_icon', ), ), false) ? 'true' : 'false') . '
				},
				jsMt: ' . $__templater->filter($__templater->func('js_mt', array(), false), array(array('json', array()),array('raw', array()),), true) . ',
				jsState: ' . ($__vars['jsState'] ? $__templater->filter($__vars['jsState'], array(array('json', array()),array('raw', array()),), true) : '{}') . ',
				publicMetadataLogoUrl: \'' . ($__templater->func('property', array('publicMetadataLogoUrl', ), false) ? $__templater->func('base_url', array($__templater->func('property', array('publicMetadataLogoUrl', ), false), true, ), true) : '') . '\',
				publicPushBadgeUrl: \'' . ($__templater->func('property', array('publicPushBadgeUrl', ), false) ? $__templater->func('base_url', array($__templater->func('property', array('publicPushBadgeUrl', ), false), true, ), true) : '') . '\'
			});

			XF.extendObject(XF.phrases, {
				// ' . '
				date_x_at_time_y:     "' . $__templater->filter('{date} at {time}', array(array('escape', array('js', )),), true) . '",
				day_x_at_time_y:      "' . $__templater->filter('{day} at {time}', array(array('escape', array('js', )),), true) . '",
				yesterday_at_x:       "' . $__templater->filter('Yesterday at {time}', array(array('escape', array('js', )),), true) . '",
				x_minutes_ago:        "' . $__templater->filter('{minutes} minutes ago', array(array('escape', array('js', )),), true) . '",
				one_minute_ago:       "' . $__templater->filter('1 minute ago', array(array('escape', array('js', )),), true) . '",
				a_moment_ago:         "' . $__templater->filter('A moment ago', array(array('escape', array('js', )),), true) . '",
				today_at_x:           "' . $__templater->filter('Today at {time}', array(array('escape', array('js', )),), true) . '",
				in_a_moment:          "' . $__templater->filter('In a moment', array(array('escape', array('js', )),), true) . '",
				in_a_minute:          "' . $__templater->filter('In a minute', array(array('escape', array('js', )),), true) . '",
				in_x_minutes:         "' . $__templater->filter('In {minutes} minutes', array(array('escape', array('js', )),), true) . '",
				later_today_at_x:     "' . $__templater->filter('Later today at {time}', array(array('escape', array('js', )),), true) . '",
				tomorrow_at_x:        "' . $__templater->filter('Tomorrow at {time}', array(array('escape', array('js', )),), true) . '",
				short_date_x_minutes: "' . $__templater->filter('{minutes}m', array(array('escape', array('js', )),), true) . '",
				short_date_x_hours:   "' . $__templater->filter('{hours}h', array(array('escape', array('js', )),), true) . '",
				short_date_x_days:    "' . $__templater->filter('{days}d', array(array('escape', array('js', )),), true) . '",

				day0: "' . $__templater->filter('Sunday', array(array('escape', array('js', )),), true) . '",
				day1: "' . $__templater->filter('Monday', array(array('escape', array('js', )),), true) . '",
				day2: "' . $__templater->filter('Tuesday', array(array('escape', array('js', )),), true) . '",
				day3: "' . $__templater->filter('Wednesday', array(array('escape', array('js', )),), true) . '",
				day4: "' . $__templater->filter('Thursday', array(array('escape', array('js', )),), true) . '",
				day5: "' . $__templater->filter('Friday', array(array('escape', array('js', )),), true) . '",
				day6: "' . $__templater->filter('Saturday', array(array('escape', array('js', )),), true) . '",

				dayShort0: "' . $__templater->filter('Sun', array(array('escape', array('js', )),), true) . '",
				dayShort1: "' . $__templater->filter('Mon', array(array('escape', array('js', )),), true) . '",
				dayShort2: "' . $__templater->filter('Tue', array(array('escape', array('js', )),), true) . '",
				dayShort3: "' . $__templater->filter('Wed', array(array('escape', array('js', )),), true) . '",
				dayShort4: "' . $__templater->filter('Thu', array(array('escape', array('js', )),), true) . '",
				dayShort5: "' . $__templater->filter('Fri', array(array('escape', array('js', )),), true) . '",
				dayShort6: "' . $__templater->filter('Sat', array(array('escape', array('js', )),), true) . '",

				month0: "' . $__templater->filter('January', array(array('escape', array('js', )),), true) . '",
				month1: "' . $__templater->filter('February', array(array('escape', array('js', )),), true) . '",
				month2: "' . $__templater->filter('March', array(array('escape', array('js', )),), true) . '",
				month3: "' . $__templater->filter('April', array(array('escape', array('js', )),), true) . '",
				month4: "' . $__templater->filter('May', array(array('escape', array('js', )),), true) . '",
				month5: "' . $__templater->filter('June', array(array('escape', array('js', )),), true) . '",
				month6: "' . $__templater->filter('July', array(array('escape', array('js', )),), true) . '",
				month7: "' . $__templater->filter('August', array(array('escape', array('js', )),), true) . '",
				month8: "' . $__templater->filter('September', array(array('escape', array('js', )),), true) . '",
				month9: "' . $__templater->filter('October', array(array('escape', array('js', )),), true) . '",
				month10: "' . $__templater->filter('November', array(array('escape', array('js', )),), true) . '",
				month11: "' . $__templater->filter('December', array(array('escape', array('js', )),), true) . '",

				active_user_changed_reload_page: "' . $__templater->filter('The active user has changed. Reload the page for the latest version.', array(array('escape', array('js', )),), true) . '",
				server_did_not_respond_in_time_try_again: "' . $__templater->filter('The server did not respond in time. Please try again.', array(array('escape', array('js', )),), true) . '",
				oops_we_ran_into_some_problems: "' . $__templater->filter('Oops! We ran into some problems.', array(array('escape', array('js', )),), true) . '",
				oops_we_ran_into_some_problems_more_details_console: "' . $__templater->filter('Oops! We ran into some problems. Please try again later. More error details may be in the browser console.', array(array('escape', array('js', )),), true) . '",
				file_too_large_to_upload: "' . $__templater->filter('The file is too large to be uploaded.', array(array('escape', array('js', )),), true) . '",
				uploaded_file_is_too_large_for_server_to_process: "' . $__templater->filter('The uploaded file is too large for the server to process.', array(array('escape', array('js', )),), true) . '",
				files_being_uploaded_are_you_sure: "' . $__templater->filter('Files are still being uploaded. Are you sure you want to submit this form?', array(array('escape', array('js', )),), true) . '",
				attach: "' . $__templater->filter('Attach files', array(array('escape', array('js', )),), true) . '",
				rich_text_box: "' . $__templater->filter('Rich text box', array(array('escape', array('js', )),), true) . '",
				close: "' . $__templater->filter('Close', array(array('escape', array('js', )),), true) . '",
				link_copied_to_clipboard: "' . $__templater->filter('Link copied to clipboard.', array(array('escape', array('js', )),), true) . '",
				text_copied_to_clipboard: "' . $__templater->filter('Text copied to clipboard.', array(array('escape', array('js', )),), true) . '",
				loading: "' . $__templater->filter('Loading' . $__vars['xf']['language']['ellipsis'], array(array('escape', array('js', )),), true) . '",
				you_have_exceeded_maximum_number_of_selectable_items: "' . $__templater->filter('You have exceeded the maximum number of selectable items.', array(array('escape', array('js', )),), true) . '",

				processing: "' . $__templater->filter('Processing', array(array('escape', array('js', )),), true) . '",
				\'processing...\': "' . $__templater->filter('Processing' . $__vars['xf']['language']['ellipsis'], array(array('escape', array('js', )),), true) . '",

				showing_x_of_y_items: "' . $__templater->filter('Showing {count} of {total} items', array(array('escape', array('js', )),), true) . '",
				showing_all_items: "' . $__templater->filter('Showing all items', array(array('escape', array('js', )),), true) . '",
				no_items_to_display: "' . $__templater->filter('No items to display', array(array('escape', array('js', )),), true) . '",

				number_button_up: "' . $__templater->filter('Increase', array(array('escape', array('js', )),), true) . '",
				number_button_down: "' . $__templater->filter('Decrease', array(array('escape', array('js', )),), true) . '",

				push_enable_notification_title: "' . $__templater->filter('Push notifications enabled successfully at ' . $__vars['xf']['options']['boardTitle'] . '', array(array('escape', array('js', )),), true) . '",
				push_enable_notification_body: "' . $__templater->filter('Thank you for enabling push notifications!', array(array('escape', array('js', )),), true) . '",

				pull_down_to_refresh: "' . $__templater->filter('Pull down to refresh', array(array('escape', array('js', )),), true) . '",
				release_to_refresh: "' . $__templater->filter('Release to refresh', array(array('escape', array('js', )),), true) . '",
				refreshing: "' . $__templater->filter('Refreshing' . $__vars['xf']['language']['ellipsis'], array(array('escape', array('js', )),), true) . '"
			});
		});
	</script>

	<!--XF:JS-->
';
	return $__finalCompiled;
}
),
'body' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'app' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<form style="display:none" hidden="hidden">
		<input type="text" name="_xfClientLoadTime" value="" id="_xfClientLoadTime" title="_xfClientLoadTime" tabindex="-1" />
	</form>

	';
	if ($__templater->method($__vars['xf']['visitor'], 'canSearch', array()) AND ($__templater->method($__vars['xf']['request'], 'getFullRequestUri', array()) === $__templater->func('link', array('full:index', ), false))) {
		$__finalCompiled .= '
		<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "WebSite",
			"url": "' . $__templater->filter($__templater->func('link', array('canonical:index', ), false), array(array('escape', array('js', )),), true) . '",
			"potentialAction": {
				"@type": "SearchAction",
				"target": {
					"@type": "EntryPoint",
					"urlTemplate": "' . (($__templater->filter($__templater->func('link', array('canonical:search/search', ), false), array(array('escape', array('js', )),), true) . ($__vars['xf']['options']['useFriendlyUrls'] ? '?' : '&')) . 'keywords={search_keywords}') . '"
				},
				"query-input": "required name=search_keywords"
			}
		}
		</script>
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '
';
	return $__finalCompiled;
}
);