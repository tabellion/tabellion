<?php

function __autoload($class_name)
{
	require_once __DIR__ . '/lib/' . $class_name . '.php';
}

require_once __DIR__ . '/configuration/identification.php';
require_once __DIR__ . '/configuration/options.php';
require_once __DIR__ . '/lib/lib_debug.php';
