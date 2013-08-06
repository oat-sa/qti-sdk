<?php

namespace qtism\runtime\rules;

use qtism\data\rules\ExitTest;
use qtism\data\rules\Rule;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The exit test rule terminates response processing immediately (for this 
 * invocation).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitTestProcessor extends RuleProcessor {
	
	/**
	 * Set the ExitTest object to be processed.
	 * 
	 * @param Rule $rule An ExitTest object.
	 * @throws InvalidArgumentException If $rule is not an ExitTest object.
	 */
	public function setRule(Rule $rule) {
		if ($rule instanceof ExitTest) {
			parent::setRule($rule);
		}
		else {
			$msg = "The ExitTestProcessor only accepts ExitTest objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the ExitTest rule. It simply throws a RuleProcessingException with
	 * the special code RuleProcessingException::EXIT_TEST to simulate the test termination.
	 * 
	 * @throws RuleProcessingException with code = RuleProcessingException::EXIT_TEST In any case.
	 */
	public function process() {
		$msg = "Termination of Test.";
		throw new RuleProcessingException($msg, $this, RuleProcessingException::EXIT_TEST);
	}
}