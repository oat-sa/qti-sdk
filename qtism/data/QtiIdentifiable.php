<?php

namespace qtism\data;

use \SplSubject;

/**
 * Any QTI class which has an identifier that makes its instances
 * unique must implement this class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface QtiIdentifiable extends SplSubject {
	
	/**
	 * Get the identifier of the QTI class instance.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier();
}