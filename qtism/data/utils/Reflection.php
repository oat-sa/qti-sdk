<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *   
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package 
 */


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
