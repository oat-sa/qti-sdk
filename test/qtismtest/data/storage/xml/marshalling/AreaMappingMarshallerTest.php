<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\state\AreaMapping;
use qtism\data\state\AreaMapEntry;
use qtism\data\state\AreaMapEntryCollection;
use DOMDocument;

class AreaMappingMarshallerTest extends QtiSmTestCase
{

    public function testMarshallMinimal()
    {
        $defaultValue = 6.66;
        $areaMapEntries = new AreaMapEntryCollection();
        
        $shape = QtiShape::RECT;
        $coords = new QtiCoords($shape, array(0, 20, 100, 0));
        $mappedValue = 1.377;
        $areaMapEntries[] = new AreaMapEntry($shape, $coords, $mappedValue);
        
        $component = new AreaMapping($areaMapEntries, $defaultValue);
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('areaMapping', $element->nodeName);
    }
    
    public function testUnmarshallMinimal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<areaMapping xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" defaultValue="6.66">
				<areaMapEntry shape="rect" coords="0, 20, 100, 0" mappedValue="1.337"/>
			</areaMapping>
			'
        );
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\state\\AreaMapping', $component);
    }
}
