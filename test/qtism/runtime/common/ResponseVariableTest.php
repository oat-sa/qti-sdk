<?php

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\datatypes\Pair;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\data\state\ResponseDeclaration;
use qtism\runtime\common\ResponseVariable;

class ResponseVariableTest extends QtiSmTestCase {
	
	public function testCreateFromVariableDeclarationExtended() {
		$factory = $this->getMarshallerFactory();
		$element = $this->createDOMElement('
			<responseDeclaration xmlns="http://www.imsglobal.org/xsd/imsqti_v2p0" 
								identifier="outcome1" 
								baseType="pair" 
								cardinality="ordered">
				<defaultValue>
					<value>A B</value>
					<value>C D</value>
					<value>E F</value>
				</defaultValue>
				<correctResponse interpretation="Up to you :)!">
					<value>A B</value>
					<value>E F</value>
				</correctResponse>
				<mapping>
					<mapEntry mapKey="A B" mappedValue="1.0" caseSensitive="true"/>
					<mapEntry mapKey="C D" mappedValue="2.0" caseSensitive="true"/>
					<mapEntry mapKey="E F" mappedValue="3.0" caseSensitive="true"/>
				</mapping>
				<areaMapping>
					<areaMapEntry shape="rect" coords="10, 20, 40, 20" mappedValue="1.0"/>
					<areaMapEntry shape="rect" coords="20, 30, 50, 30" mappedValue="2.0"/>
					<areaMapEntry shape="rect" coords="30, 40, 60, 40" mappedValue="3.0"/>
				</areaMapping>
			</responseDeclaration>
		');
		$responseDeclaration = $factory->createMarshaller($element)->unmarshall($element);
		$responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
		$this->assertInstanceOf('qtism\\runtime\\common\\ResponseVariable', $responseVariable);
		
		$this->assertEquals('outcome1', $responseVariable->getIdentifier());
		$this->assertEquals(BaseType::PAIR, $responseVariable->getBaseType());
		$this->assertEquals(Cardinality::ORDERED, $responseVariable->getCardinality());
		
		$defaultValue = $responseVariable->getDefaultValue();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $defaultValue);
		$this->assertEquals(3, count($defaultValue));
		
		$mapping = $responseVariable->getMapping();
		$this->assertInstanceOf('qtism\\data\\state\\Mapping', $mapping);
		$mapEntries = $mapping->getMapEntries();
		$this->assertEquals(3, count($mapEntries));
		$this->assertInstanceOf('qtism\\common\\datatypes\\Pair', $mapEntries[0]->getMapKey());
		
		$areaMapping = $responseVariable->getAreaMapping();
		$this->assertInstanceOf('qtism\\data\\state\\AreaMapping', $areaMapping);
		$areaMapEntries = $areaMapping->getAreaMapEntries();
		$this->assertEquals(3, count($areaMapEntries));
		$this->assertInstanceOf('qtism\\common\\datatypes\\Coords', $areaMapEntries[0]->getCoords());
		
		$correctResponse = $responseVariable->getCorrectResponse();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $correctResponse);
		$this->assertEquals(2, count($correctResponse));
		$this->assertTrue($correctResponse[0]->equals(new Pair('A', 'B')));
		$this->assertTrue($correctResponse[1]->equals(new Pair('E', 'F')));
	}
}