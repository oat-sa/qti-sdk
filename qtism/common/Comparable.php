<?php

namespace qtism\common;

/**
 * An interface aiming at providing the same behaviour as
 * Oracle Java's Object.equals method.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @link http://docs.oracle.com/javase/7/docs/api/java/lang/Object.html#equals(java.lang.Object)
 */
interface Comparable {
	
	/**
	 * Indicates whether some object is equal to this one. (c.f. JavaSE doc)
	 * 
	 * @param mixed $obj An object to compare.
	 * @return boolean Whether the object to compare is equal to this one.
	 * @link http://docs.oracle.com/javase/7/docs/api/java/lang/Object.html#equals(java.lang.Object)
	 */
	public function equals($obj);
	
}