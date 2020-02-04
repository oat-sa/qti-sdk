<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\TestFeedbackMarshaller;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\TestFeedback;
use qtism\data\TestFeedbackAccess;
use qtism\data\ShowHide;
use qtism\data\content\TextRun;
use qtism\data\content\InlineCollection;
use qtism\data\content\xhtml\text\P;
use qtism\data\content\FlowCollection;
use qtism\data\content\xhtml\text\Div;
use qtism\data\content\FlowStaticCollection;
use ReflectionClass;
use DOMDocument;

class TestFeedbackMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myTestFeedBack1';
        $outcomeIdentifier = 'myOutcomeIdentifier1';
        $access = TestFeedbackAccess::AT_END;
        $showHide = ShowHide::SHOW;
        $text = new TextRun('Hello World!');
        $p = new P();
        $p->setContent(new InlineCollection(array($text)));
        $div = new Div();
        $div->setContent(new FlowCollection(array($p)));
        $content = new FlowStaticCollection(array($div));

        $component = new TestFeedback($identifier, $outcomeIdentifier, $content);
        $component->setAccess($access);
        $component->setShowHide($showHide);
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('testFeedback', $element->nodeName);
        $this->assertEquals($identifier, $element->getAttribute('identifier'));
        $this->assertEquals($outcomeIdentifier, $element->getAttribute('outcomeIdentifier'));
        $this->assertEquals('', $element->getAttribute('title'));
        $this->assertEquals('atEnd', $element->getAttribute('access'));
        $this->assertEquals('show', $element->getAttribute('showHide'));
        
        $content = $element->getElementsByTagName('div');
        $this->assertEquals($content->length, 1);
        $this->assertEquals($content->item(0)->getElementsByTagName('p')->length, 1);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myIdentifier1" access="atEnd" outcomeIdentifier="myOutcomeIdentifier1" showHide="show" title="my title">
                <p>Have a nice test!</p>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertInstanceOf('qtism\\data\\testFeedback', $component);
        $this->assertEquals($component->getIdentifier(), 'myIdentifier1');
        $this->assertEquals($component->getAccess(), TestFeedbackAccess::AT_END);
        $this->assertEquals($component->getShowHide(), ShowHide::SHOW);
        $this->assertEquals($component->getTitle(), 'my title');
        
        $content = $component->getContent();
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\P', $content[1]);
    }
    
    public function testUnmarshallWrongContent()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myIdentifier1" access="atEnd" outcomeIdentifier="myOutcomeIdentifier1" showHide="show" title="my title">
                <choiceInteraction responseIdentifier="RESPONSE">
                    <simpleChoice identifier="IDENTIFIER">Choice A</simpleChoice>
                </choiceInteraction>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "'testFeedback' elements cannot contain 'choiceInteraction' elements."
        );
        
        $marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNoAccessAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myIdentifier1" outcomeIdentifier="myOutcomeIdentifier1" showHide="show" title="my title">
                <p>Have a nice test!</p>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'access' attribute is missing from element 'testFeedback'."
        );
        
        $marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNoShowHideAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myIdentifier1" access="atEnd" outcomeIdentifier="myOutcomeIdentifier1" title="my title">
                <p>Have a nice test!</p>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'showHide' attribute is missing from element 'testFeedback'."
        );
        
        $marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNoOutcomeIdentifierAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myIdentifier1" showHide="show" access="atEnd" title="my title">
                <p>Have a nice test!</p>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'outcomeIdentifier' attribute is missing from element 'testFeedback'."
        );
        
        $marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNoIdentifierAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
		    <testFeedback xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" outcomeIdentifier="myOutcomeIdentifier1" showHide="show" access="atEnd" title="my title">
                <p>Have a nice test!</p>
            </testFeedback>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'identifier' attribute is missing from element 'testFeedback'."
        );
        
        $marshaller->unmarshall($element);
    }
}
