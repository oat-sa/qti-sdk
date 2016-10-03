<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\ShowHide;


use qtismtest\QtiSmTestCase;
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\xhtml\Object;
use qtism\data\content\interactions\GapImg;
use \DOMDocument;

class GapImgMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
	    $object = new Object('http://imagine.us/myimg.png', "image/png");
	    $gapImg = new GapImg('gapImg1', 1, $object, 'my-gap', 'gaps');
	    $gapImg->setShowHide(ShowHide::HIDE);
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapImg);
	    $element = $marshaller->marshall($gapImg);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<gapImg id="my-gap" class="gaps" identifier="gapImg1" matchMax="1" showHide="hide"><object data="http://imagine.us/myimg.png" type="image/png"/></gapImg>', $dom->saveXML($element));
	}
	
	/**
	 * @depends testMarshall21
	 */
	public function testMarshallNoMatchGroup21() {
	    // Aims am testing that matchGroup attribute is ignore in
	    // a QTI 2.1 context.
	    $object = new Object('http://imagine.us/myimg.png', "image/png");
	    $gapImg = new GapImg('gapImg1', 0, $object);
	    $gapImg->setMatchGroup(new IdentifierCollection(array('identifier1')));
	     
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($gapImg);
	    $element = $marshaller->marshall($gapImg);
	     
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<gapImg identifier="gapImg1" matchMax="0"><object data="http://imagine.us/myimg.png" type="image/png"/></gapImg>', $dom->saveXML($element));
	}
	
	public function testMarshall20() {
	    $object = new Object('http://imagine.us/myimg.png', "image/png");
	    $gapImg = new GapImg('gapImg1', 2, $object);
	    $gapImg->setMatchMin(1);
	    $gapImg->setTemplateIdentifier('XTEMPLATE');
	    $gapImg->setShowHide(ShowHide::HIDE);
	    $gapImg->setMatchGroup(new IdentifierCollection(array('identifier1', 'identifier2')));
	
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($gapImg);
	    $element = $marshaller->marshall($gapImg);
	
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<gapImg identifier="gapImg1" matchMax="2" matchGroup="identifier1 identifier2"><object data="http://imagine.us/myimg.png" type="image/png"/></gapImg>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
	    $element = $this->createDOMElement('
	        <gapImg id="my-gap" class="gaps" identifier="gapImg1" matchMax="1" showHide="hide">
	          <object data="http://imagine.us/myimg.png" type="image/png"/>
	        </gapImg>
	    ');
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $gapImg = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\GapImg', $gapImg);
	    $this->assertEquals('my-gap', $gapImg->getId());
	    $this->assertEquals('gaps', $gapImg->getClass());
	    $this->assertEquals('gapImg1', $gapImg->getIdentifier());
	    $this->assertEquals(0, $gapImg->getMatchMin());
	    $this->assertEquals(1, $gapImg->getMatchMax());
	    $this->assertEquals(ShowHide::HIDE, $gapImg->getShowHide());
        $this->assertFalse($gapImg->isFixed());
	    
	    $object = $gapImg->getObject();
	    $this->assertEquals('http://imagine.us/myimg.png', $object->getData());
	    $this->assertEquals('image/png', $object->getType());
	}
	
	/**
	 * @depends testUnmarshall21
	 */
	public function testUnmarshallNoMatchGroup21() {
	    $element = $this->createDOMElement('
	        <gapImg identifier="gapImg1" matchMax="1" matchGroup="identifier1 identifier2">
	          <object data="http://imagine.us/myimg.png" type="image/png"/>
	        </gapImg>
	    ');
	     
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $gapImg = $marshaller->unmarshall($element);
	    $this->assertEquals(0, count($gapImg->getMatchGroup()));
	}
	
	public function testUnmarshall20() {
	    $element = $this->createDOMElement('
	        <gapImg id="my-gap" class="gaps" identifier="gapImg1" matchMax="1" matchMin="1" matchGroup="identifier1 identifier2" templateIdentifier="XTEMPLATE" showHide="hide">
	          <object data="http://imagine.us/myimg.png" type="image/png"/>
	        </gapImg>
	    ');
	     
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
	    $gapImg = $marshaller->unmarshall($element);
	     
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\GapImg', $gapImg);
	    $this->assertEquals('my-gap', $gapImg->getId());
	    $this->assertEquals('gaps', $gapImg->getClass());
	    $this->assertEquals('gapImg1', $gapImg->getIdentifier());
	    $this->assertEquals(0, $gapImg->getMatchMin());
	    $this->assertEquals(1, $gapImg->getMatchMax());
	    $this->assertFalse($gapImg->hasTemplateIdentifier());
	    $this->assertEquals(array('identifier1', 'identifier2'), $gapImg->getMatchGroup()->getArrayCopy());
	    $this->assertEquals(ShowHide::SHOW, $gapImg->getShowHide());
	     
	    $object = $gapImg->getObject();
	    $this->assertEquals('http://imagine.us/myimg.png', $object->getData());
	    $this->assertEquals('image/png', $object->getType());
	}
    
    /**
	 * @depends testUnmarshall21
	 */
    public function testUnmarshalMultipleObjects()
    {
        $element = $this->createDOMElement('
	        <gapImg identifier="gapImg1" matchMax="1">
	          <object data="http://imagine.us/myimg.png" type="image/png"/>
              <object data="http://imagine.us/myimg2.png" type="image/png"/>
	        </gapImg>
	    ');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "A 'gapImg' element must contain a single 'object' element, 2 given."
        );
        
	    $gapImg = $marshaller->unmarshall($element);
    }
    
    /**
	 * @depends testUnmarshall21
	 */
    public function testUnmarshalFixed()
    {
        $element = $this->createDOMElement('
	        <gapImg identifier="gapImg1" matchMax="1" fixed="true">
	          <object data="http://imagine.us/myimg.png" type="image/png"/>
	        </gapImg>
	    ');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $gapImg = $marshaller->unmarshall($element);
        
        $this->assertTrue($gapImg->isFixed());
    }
}
