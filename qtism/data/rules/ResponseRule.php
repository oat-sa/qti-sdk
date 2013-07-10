<?php

namespace qtism\data\rules;

use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 * 
 * A response rule is either a responseCondition, a simple action or a 
 * responseProcessingFragment. Response rules define the light-weight programming 
 * language necessary for deriving outcomes from responses (i.e., scoring). Note 
 * that this programming language contains a minimal number of control structures, 
 * more complex scoring rules must be coded in other languages and referred to 
 * using a customOperator.
 * 
 * Result rules are followed in the order given. Variables updated by a rule take
 * their new value when evaluated as part of any following rules.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface ResponseRule {
	
}