<?php
namespace qtism\runtime\expressions;

use qtism\data\expressions\NumberIncorrect;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * The NumberIncorrectProcessor aims at processing NumberIncorrect
 * Outcome Processing only expressions.
 * 
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates the number of 
 * items in a given sub-set, for which at least one of the defined response variables does 
 * not match its associated correctResponse. Only items for which all declared response 
 * variables have correct responses defined and have been attempted at least once are 
 * considered. The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberIncorrectProcessor extends ItemSubsetProcessor {
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof NumberIncorrect) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The NumberIncorrectProcessor class only accepts NumberIncorrect expressions to be processed.";
			throw new InvalidArgumentException($expression);
		}
	}
	
	/**
	 * Process the related NumberIncorrect expression.
	 * 
	 * @return integer The number of items in the given sub-set for which at least one of the defined response does not match its associated correct response.
	 * @throws ExpressionProcessingException
	 */
	public function process() {
	    $testSession = $this->getState();
	    $itemSubset = $this->getItemSubset();
	    $numberIncorrect = 0;
	    
	    foreach ($itemSubset as $item) {
	        $itemSessions = $testSession->getAssessmentItemSessions($item->getIdentifier());
	        
	        foreach ($itemSessions as $itemSession) {
	            if ($itemSession->isCorrect() === false) {
	                $numberIncorrect++;
	            }
	        }
	    }
	    
	    return $numberIncorrect;
	}
}