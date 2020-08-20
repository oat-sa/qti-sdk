<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\DurationStore;
use qtismtest\QtiSmTestCase;

class DurationStoreTest extends QtiSmTestCase
{
    public function testInstantiation()
    {
        $data = [];
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));
        $durations = new DurationStore($data);

        $this->assertInstanceOf(DurationStore::class, $durations);
        $this->assertTrue($durations['duration1']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($durations['duration2']->equals(new QtiDuration('PT1S')));
        $this->assertTrue($durations['duration3']->equals(new QtiDuration('PT2S')));

        $this->assertTrue(isset($durations['duration1']));
    }

    public function testInstantiationWrongBaseType()
    {
        $data = [];
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The DurationStore only aims at storing OutcomeVariable objects with a 'duration' baseType");
        $durations = new DurationStore($data);
    }

    public function testInstantiationWrongCardinality()
    {
        $data = [];
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT2S')]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The DurationStore only aims at storing OutcomeVariable objects with a 'single' cardinality");
        $durations = new DurationStore($data);
    }

    public function testWrongVariableTypeInstantiation()
    {
        $data = [];
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new ResponseVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The DurationStore only aims at storing OutcomeVariable objects');
        $durations = new DurationStore($data);
    }

    public function testSetVariable()
    {
        $durations = new DurationStore();
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S')));
        $this->assertInstanceOf(OutcomeVariable::class, $durations->getVariable('duration1'));
        $this->assertTrue($durations['duration1']->equals(new QtiDuration('PT0S')));
    }

    public function testSetVariableWrongBaseType()
    {
        $durations = new DurationStore();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The DurationStore only aims at storing OutcomeVariable objects with a 'duration' baseType");
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier')));
    }

    public function testSetVariableWrongCardinality()
    {
        $durations = new DurationStore();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The DurationStore only aims at storing OutcomeVariable objects with a 'single' cardinality");
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, [new QtiDuration('PT0S')])));
    }

    public function testSetVariableWrongVariableType()
    {
        $durations = new DurationStore();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The DurationStore only aims at storing OutcomeVariable objects');
        $durations->setVariable(new ResponseVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S')));
    }
}
