<?php

namespace qtism\runtime\rules;

use qtism\data\QtiComponent;
use qtism\runtime\common\ProcessorFactory;
use qtism\runtime\common\Processable;
use qtism\data\rules\Rule;
use \RuntimeException;

/**
 * The RuleProcessorFactory class aims at providing a way to
 * build RuleProcessor objects on the demand given a given
 * Rule object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RuleProcessorFactory implements ProcessorFactory {
	
	/**
	 * Create a new RuleProcessorFactory object.
	 * 
	 */
	public function __construct() {
		
	}
	
	/**
	 * Create the RuleProcessor object able to process the  given $rule.
	 * 
	 * @param QtiComponent $rule A Rule object you want to get the related processor.
	 * @return Processable The related RuleProcessor object.
	 * @throws RuntimeException If no RuleProcessor can be found for the given $rule.
	 */
	public function createProcessor(QtiComponent $rule) {
		$qtiClassName = ucfirst($rule->getQtiClassName());
		$nsPackage = 'qtism\\runtime\\rules\\';
		$className =  $nsPackage . $qtiClassName . 'Processor';
		
		if (class_exists($className) === true) {
			return new $className($rule);
		}
		
		$msg = "The QTI rule class '${qtiClassName}' has no dedicated RuleProcessor class.";
		throw new RuntimeException($msg);
	}
}