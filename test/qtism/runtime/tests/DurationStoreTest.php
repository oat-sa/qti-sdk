<?php
use qtism\runtime\common\ResponseVariable;

use qtism\runtime\common\MultipleContainer;

use qtism\common\datatypes\QtiIdentifier;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\common\datatypes\QtiDuration;
use qtism\runtime\tests\DurationStore;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class DurationStoreTest extends QtiSmTestCase {
    
    public function testInstantiation() {
        $data = array();
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));
        $durations = new DurationStore($data);
        
        $this->assertInstanceOf('qtism\\runtime\\tests\\DurationStore', $durations);
        $this->assertTrue($durations['duration1']->equals(new QtiDuration('PT0S')));
        $this->assertTrue($durations['duration2']->equals(new QtiDuration('PT1S')));
        $this->assertTrue($durations['duration3']->equals(new QtiDuration('PT2S')));
        
        $this->assertTrue(isset($durations['duration1']));
    }
    
    public function testInstantiationWrongBaseType() {
        $data = array();
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));
        
        $this->setExpectedException('\\InvalidArgumentException', "The DurationStore only aims at storing OutcomeVariable objects with a 'duration' baseType, 'identifier' baseType given for variable 'duration2'.");
        $durations = new DurationStore($data);
    }
    
    public function testInstantiationWrongCardinality() {
        $data = array();
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new OutcomeVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT2S'))));
        
        $this->setExpectedException('\\InvalidArgumentException', "The DurationStore only aims at storing OutcomeVariable objects with a 'single' cardinality, 'multiple' cardinality given for variable 'duration3'.");
        $durations = new DurationStore($data);
    }
    
    public function testWrongVariableTypeInstantiation() {
        $data = array();
        $data[] = new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S'));
        $data[] = new ResponseVariable('duration2', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT1S'));
        $data[] = new OutcomeVariable('duration3', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT2S'));
        
        $this->setExpectedException('\\InvalidArgumentException', 'The DurationStore only aims at storing OutcomeVariable objects, qtism\runtime\common\ResponseVariable object given.');
        $durations = new DurationStore($data);
    }
    
    public function testSetVariable() {
        $durations = new DurationStore();
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S')));
        $this->assertInstanceOf('qtism\\runtime\\common\\OutcomeVariable', $durations->getVariable('duration1'));
        $this->assertTrue($durations['duration1']->equals(new QtiDuration('PT0S')));
    }
    
    public function testSetVariableWrongBaseType() {
        $durations = new DurationStore();
        
        $this->setExpectedException('\\InvalidArgumentException', "The DurationStore only aims at storing OutcomeVariable objects with a 'duration' baseType, 'identifier' baseType given for variable 'duration1'");
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('identifier')));
    }
    
    public function testSetVariableWrongCardinality() {
        $durations = new DurationStore();
        
        $this->setExpectedException('\\InvalidArgumentException', "The DurationStore only aims at storing OutcomeVariable objects with a 'single' cardinality, 'multiple' cardinality given for variable 'duration1'");
        $durations->setVariable(new OutcomeVariable('duration1', Cardinality::MULTIPLE, BaseType::DURATION, new MultipleContainer(BaseType::DURATION, array(new QtiDuration('PT0S')))));
    }
    
    public function testSetVariableWrongVariableType() {
        $durations = new DurationStore();
        $this->setExpectedException('\\InvalidArgumentException', 'The DurationStore only aims at storing OutcomeVariable objects, qtism\runtime\common\ResponseVariable object given.');
        $durations->setVariable(new ResponseVariable('duration1', Cardinality::SINGLE, BaseType::DURATION, new QtiDuration('PT0S')));
    }
}
