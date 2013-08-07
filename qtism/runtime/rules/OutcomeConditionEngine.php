<?php
namespace qtism\runtime\rules;

/**
 * From IMS QTI:
 * 
 * If the expression given in the outcomeIf or outcomeElseIf evaluates to true then 
 * the sub-rules contained within it are followed and any following outcomeElseIf or 
 * outcomeElse parts are ignored for this outcome condition.
 * 
 * If the expression given in the outcomeIf or outcomeElseIf does not evaluate to true 
 * then consideration passes to the next outcomeElseIf or, if there are no more 
 * outcomeElseIf parts then the sub-rules of the outcomeElse are followed 
 * (if specified).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeConditionEngine extends AbstractConditionEngine {
	
	public function getQtiNature() {
		return 'outcome';
	}
}