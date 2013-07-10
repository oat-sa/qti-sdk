<?php

namespace qtism\data\utils;

use qtism\data\QtiComponent;
use \RuntimeException;
use \ReflectionClass;

/**
 * A utility class focusing on Reflection.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Reflection {
	
	/**
	 * Create a new instance of a QtiComponent. For instance,
	 * 
	 * <code>
	 * $timeLimits = Reflection::instantiateComponent('TimeLimits', array());
	 * // $timeLimits containts a qtism\\data\\TimeLimits object.
	 * </code>
	 * 
	 * @param string $className The name of a sub-class of QtiComponent.
	 * @param array $args An array of arguments that corresponds to the related constructor.
	 * @throws RuntimeException If $className cannot be resolved among the QTI Data Model.
	 * @return object A QtiComponent object.
	 */
	public static function instantiateComponent($className, array $args = array()) {
		$nsLookup = array('', 'expressions', 'expressions\\operators', 'rules', 'state');
			
		foreach ($nsLookup as $ns) {
		
			$expectedClass = 'qtism\\data' . (!empty($ns) ? '\\' : '') . $ns . '\\' . $className;
		
			if (class_exists($expectedClass)) {
				$class = new ReflectionClass($expectedClass);
					
				try {
					$component = $class->newInstanceArgs($args);
		
					return $component;
				}
				catch (ReflectionException $e) {
					throw new RuntimeException("The PHP class constructor related to the '${qtiComponent}' QTI class could not be called successfuly.", 0, $e);
				}
			}
		}
		
		throw new RuntimeException("The PHP class related to the '${expectedClass}' QTI class cannot be loaded.");
	}
}