<?php

namespace qtismtest\data\state;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ExternalScored;
use qtism\data\state\MatchTable;
use qtism\data\state\MatchTableEntry;
use qtism\data\state\MatchTableEntryCollection;
use qtism\data\state\OutcomeDeclaration;
use qtismtest\QtiSmTestCase;

class OutcomeDeclarationTest extends QtiSmTestCase
{
    /** @var OutcomeDeclaration */
    private $subject;

    public function setUp()
    {
        $this->subject = new OutcomeDeclaration('SCORE', BaseType::FLOAT, Cardinality::SINGLE);
    }

    public function testSetInterpretationWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Interpretation must be a string, 'integer' given."
        );

        $this->subject->setInterpretation(999);
    }

    public function testSetLongInterpretationWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "LongInterpretation must be a string, 'integer' given."
        );

        $this->subject->setLongInterpretation(999);
    }

    public function testSetNormalMinimumWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "NormalMinimum must be a number or (boolean) false, 'string' given."
        );

        $this->subject->setNormalMinimum('string');
    }

    public function testSetNormalMaximumWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "NormalMaximum must be a number or (boolean) false, 'string' given."
        );

        $this->subject->setNormalMaximum('string');
    }

    public function testSetMasteryValueWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "MasteryValue must be a number or (boolean) false, 'string' given."
        );

        $this->subject->setMasteryValue('string');
    }

    public function getComponentsWithLookupTable()
    {
        $this->subject->setLookupTable(
            new MatchTable(
                new MatchTableEntryCollection(
                    new MatchTableEntry(3, 3.33)
                )
            )
        );

        $components = $this->getComponents();
        $last = $components[count($components) - 1];
        $this->assertInstanceOf('qtism\\data\\state\\MatchTable', $last);
    }

    public function testExternalScoredAccessors()
    {
        $this->assertFalse($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored(ExternalScored::getConstantByName('human'));

        $this->assertTrue($this->subject->isExternallyScored());
        $this->assertTrue($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored(ExternalScored::getConstantByName('externalMachine'));

        $this->assertTrue($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertTrue($this->subject->isScoredByExternalMachine());

        $this->subject->setExternalScored();

        $this->assertFalse($this->subject->isExternallyScored());
        $this->assertFalse($this->subject->isScoredByHuman());
        $this->assertFalse($this->subject->isScoredByExternalMachine());
    }
}
