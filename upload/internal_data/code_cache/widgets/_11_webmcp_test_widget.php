<?php

return function($__templater, array $__vars, array $__options = [])
{
	$__widget = \XF::app()->widget()->widget('webmcp_test_widget', $__options)->render();

	return $__widget;
};