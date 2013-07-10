<?php

namespace qtism;

function qtism_autoload($class) {
	// @todo Deal with the customOperator QTI Element.
	$explode = explode('\\', $class);
	$parts = array_splice($explode, 1);
	$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	
	if (is_readable($file)) {
		require_once($file);
	}
}

spl_autoload_register(__NAMESPACE__ . '\\qtism_autoload');