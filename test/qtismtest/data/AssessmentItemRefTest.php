<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\data\AssessmentItemRef;
use qtism\data\expressions\BaseValue;
use qtism\data\ItemSessionControl;
use qtism\data\rules\BranchRule;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreCondition;
use qtism\data\rules\PreConditionCollection;
use qtism\data\TimeLimits;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentItemRefTest
 *
 * @package qtismtest\data
 */
class AssessmentItemRefTest extends QtiSmTestCase
{
    public function testCreateAssessmentItemRefWrongIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $assessmentItemRef = new AssessmentItemRef('999', 'Nine Nine Nine');
    }

    public function testSetRequiredWrongType()
    {
        $assessmentItemRef = new AssessmentItemRef('nine', 'Nine Nine Nine');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Required must be a boolean, 'string' given.");

        $assessmentItemRef->setRequired('test');
    }

    public function testSetFixedWrongType()
    {
        $assessmentItemRef = new AssessmentItemRef('nine', 'Nine Nine Nine');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Fixed must be a boolean, 'string' given.");

        $assessmentItemRef->setFixed('test');
    }

    public function testClone()
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $itemSessionControl = new ItemSessionControl();
        $assessmentItemRef->setItemSessionControl($itemSessionControl);

        $timeLimits = new TimeLimits();
        $assessmentItemRef->setTimeLimits($timeLimits);

        $branchRule = new BranchRule(new BaseValue(BaseType::BOOLEAN, true), 'Q01');
        $preCondition = new PreCondition(new BaseValue(BaseType::BOOLEAN, true));

        $branchRules = new BranchRuleCollection([$branchRule]);
        $preConditions = new PreconditionCollection([$preCondition]);
        $assessmentItemRef->setBranchRules($branchRules);
        $assessmentItemRef->setPreConditions($preConditions);

        $cloneAssessmentItemRef = clone $assessmentItemRef;

        $this->assertNotSame($assessmentItemRef, $cloneAssessmentItemRef);
        $this->assertNotSame($itemSessionControl, $cloneAssessmentItemRef->getItemSessionControl());
        $this->assertNotSame($timeLimits, $cloneAssessmentItemRef->getTimeLimits());
        $this->assertNotSame($preConditions, $cloneAssessmentItemRef->getPreConditions());
        $this->assertNotSame($branchRules, $cloneAssessmentItemRef->getBranchRules());
    }
}
