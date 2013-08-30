<?php

namespace qtism\runtime\tests;

use qtism\common\collections\AbstractCollection;
use InvalidArgumentException as InvalidArgumentException;
use \OutOfBoundsException;

/**
 * A collection that aims at storing Route objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SelectableRouteCollection extends AbstractCollection {

	/**
	 * Check if $value is a Route object
	 * 
	 * @throws InvalidArgumentException If $value is not a Route object.
	 */
	protected function checkType($value) {
		if (!$value instanceof SelectableRoute) {
			$msg = "SelectableRouteCollection class only accept SelectableRoute objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Swap Route at position $key1 with the Route
	 * at position $key2.
	 *
	 * @param int $position1 A RouteItem position.
	 * @param int $position2 A RouteItem position.
	 * @throws OutOfBoundsException If $position1 or $position2 are not poiting to any Route.
	 */
	public function swap($position1, $position2) {
	    $routes = &$this->getDataPlaceHolder();
	
	    if (isset($routes[$position1]) === false) {
	        $msg = "No Route object at position '${position1}'.";
	        throw new OutOfBoundsException($msg);
    	}
    	
    	if (isset($routes[$position2]) === false) {
    	    $msg = "No Route object at position '${position2}'.";
    	    throw new OutOfBoundsException($msg);
    	}
    	
    	$temp = $routes[$position2];
    	$routes[$position2] = $routes[$position1];
    	$routes[$position1] = $temp;
	}
	
	/**
	 * Insert the SelectableRoute object $route at $position.
	 *
	 * @param SelectableRoute $route A SelectableRoute object.
	 * @param integer $position An integer index where $route must be placed.
	 */
	public function insertAt(SelectableRoute $route, $position) {
	    $data = &$this->getDataPlaceHolder();
	    if ($position === 0) {
	        array_unshift($data, $route);
	    }
	    else if ($position === (count($data))) {
	        array_push($data, $route);
	    }
	    else {
	        array_splice($data, $position, 0, array($route));
	    }
	}
}