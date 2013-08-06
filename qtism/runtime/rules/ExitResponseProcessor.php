<?php

namespace qtism\runtime\rules;

use qtism\data\rules\ExitResponse;
use qtism\data\rules\Rule;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The exit response rule terminates response processing immediately (for this 
 * invocation).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitResponseProcessor extends RuleProcessor {
	
	/**
	 * Set the ExitResponse object to be processed.
	 * 
	 * @param Rule $rule An ExitResponse object.
	 * @throws InvalidArgumentException If $rule is not an ExitResponse object.
	 */
	public function setRule(Rule $rule) {
		if ($rule instanceof ExitResponse) {
			parent::setRule($rule);
		}
		else {
			$msg = "The ExitResponseProcessor only accepts ExitResponse objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Process the ExitResponse rule. It simply throws a RuleProcessingException with
	 * the special code RuleProcessingException::EXIT_RESPONSE to simulate the
	 * response processing termination.
	 * 
	 * @throws RuleProcessingException with code = RuleProcessingException::EXIT_RESPONSE In any case.
	 */
	public function process() {
		$msg = "Termination of Response Processing.";
		throw new RuleProcessingException($msg, $this, RuleProcessingException::EXIT_RESPONSE);
	}
}