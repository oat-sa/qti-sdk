<?php
namespace qtism\runtime\rules;

/**
 * From IMS QTI:
 * 
 * If the expression given in a responseIf or responseElseIf evaluates to true then 
 * the sub-rules contained within it are followed and any following responseElseIf or 
 * responseElse parts are ignored for this response condition.
 * 
 * If the expression given in a responseIf or responseElseIf does not evaluate to true 
 * then consideration passes to the next responseElseIf or, if there are no more 
 * responseElseIf parts then the sub-rules of the responseElse are followed (if 
 * specified).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseConditionProcessor extends AbstractConditionProcessor {
	
	public function getQtiNature() {
		return 'response';
	}
}