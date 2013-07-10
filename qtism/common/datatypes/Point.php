<?php

namespace qtism\common\datatypes;

use \InvalidArgumentException;
use qtism\common\Comparable;

/**
 * From IMS QTI:
 * 
 * A point value represents an integer tuple corresponding to a 
 * graphic point. The two integers correspond to the horizontal (x-axis) 
 * and vertical (y-axis) positions respectively. The up/down and 
 * left/right senses of the axes are context dependent.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Point implements Comparable {
	
	/**
	 * The position on the x-axis.
	 * 
	 * @var int
	 */
	private $x;
	
	/**
	 * The position on the y-axis.
	 * 
	 * @var int
	 */
	private $y;
	
	/**
	 * Create a new Point object.
	 * 
	 * @param int $x A position on the x-axis.
	 * @param int $y A position on the y-axis.
	 * @throws InvalidArgumentException If $x or $y are not integer values.
	 */
	public function __construct($x, $y) {
		$this->setX($x);
		$this->setY($y);
	}
	
	/**
	 * Set the position on the x-axis.
	 * 
	 * @param int $x A position on the x-axis.
	 * @throws InvalidArgumentException If $x is nto an integer value.
	 */
	public function setX($x) {
		if (is_int($x)) {
			$this->x = $x;
		}
		else {
			$msg = "The X argument must be an integer value, '" . gettype($x) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the position on the x-axis.
	 * 
	 * @return int A position on the x-axis.
	 */
	public function getX() {
		return $this->x;
	}
	
	/**
	 * Set the position on y-axis.
	 * 
	 * @param int $y A position on the y-axis.
	 * @throws InvalidArgumentException If $y is not an integer value.
	 */
	public function setY($y) {
		if (is_int($y)) {
			$this->y = $y;
		}
		else {
			$msg = "The Y argument must be an integer value, '" . gettype($x) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the position on the y-axis.
	 * 
	 * @return int A position on the y-axis.
	 */
	public function getY() {
		return $this->y;
	}
	
	/**
	 * Wheter a given $obj is equal to this Point;
	 * 
	 * @param mixed $obj An object.
	 * @return boolean Whether the equality is established.
	 */
	public function equals($obj) {
		return (gettype($obj) === 'object' &&
			$obj instanceof self &&
			$obj->getX() === $this->getX() &&
			$obj->getY() === $this->getY());
	}
	
	public function __toString() {
		return $this->getX() . ' ' . $this->getY();
	}
}