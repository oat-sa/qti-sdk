<?php
namespace qtismtest\data\storage\xml;

use qtismtest\QtiSmTestCase;
use qtism\data\content\TextRun;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use \DOMDocument;

class XmlDocumentTest extends QtiSmTestCase {
	
    public function testRubricBlockRuptureNoValidation() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/paper_vs_xsd/rubricblock_other_content_than_block.xml');
        
        $search = $doc->getDocumentComponent()->getComponentsByClassName('rubricBlock');
        $rubricBlock = $search[0];
        $this->assertInstanceOf('qtism\\data\\content\\RubricBlock', $rubricBlock);
        
        $content = $rubricBlock->getContent();
        $text = $content[0];
        $this->assertEquals('Hello there', substr(trim($text->getContent()), 0, 11));
        
        $hr = $content[2];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\presentation\\Hr', $hr);
        
        $div = $content[4];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Div', $div);
        $divContent = $div->getContent();
        $this->assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));
        
        $a = $content[7];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\A', $a);
        $aContent = $a->getContent();
        $this->assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }
    
    public function testRubricBlockRuptureValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/rubricblock_other_content_than_block.xml';

        // We use here XSD validation.
        $valid = false;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this->assertTrue($valid, 'Even if the content of the rubricBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');
        
        $doc->load($file);
        $this->assertTrue(true);
    }
    
    public function testTemplateBlockRuptureNoValidation() {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/paper_vs_xsd/templateblock_other_content_than_block.xml');
        
        // Check the content...
        $search = $doc->getDocumentComponent()->getComponentsByClassName('templateBlock');
        $templateBlock = $search[0];
        $this->assertInstanceOf('qtism\\data\\content\\TemplateBlock', $templateBlock);
        
        $content = $templateBlock->getContent();
        $this->assertEquals('Hello there', substr(trim($content[0]->getContent()), 0, 11));
        
        $hr = $content[2];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\presentation\\Hr', $hr);
        
        $div = $content[4];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Div', $div);
        $divContent = $div->getContent();
        $this->assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));
        
        $a = $content[7];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\A', $a);
        $aContent = $a->getContent();
        $this->assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }
    
    public function testTemplateBlockRuptureValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/templateblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this->assertTrue($valid, 'Even if the content of the templateBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');
        
        $doc->load($file);
        $this->assertTrue(true);
    }
    
    public function testFeedbackBlockRuptureNoValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/feedbackblock_other_content_than_block.xml';
        $doc->load($file);
        
        // Let's check the content of this...
        $test = $doc->getDocumentComponent();
        $feedbacks = $test->getComponentsByClassName('feedbackBlock');
        $this->assertEquals(1, count($feedbacks));
        
        $feedback = $feedbacks[0];
        $content = $feedback->getContent();
        $text = $content[0];
        $this->assertInstanceOf('qtism\\data\\content\\TextRun', $text);
        $this->assertEquals('Hello there', substr(trim($text->getContent()), 0, 11));
        
        $hr = $content[2];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\presentation\\Hr', $hr);
        
        $div = $content[4];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Div', $div);
        $divContent = $div->getContent();
        $this->assertEquals('This div and its inner text are perfectly valid from both XSD and paper spec point of views.', trim($divContent[0]->getContent()));
        
        $a = $content[7];
        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\A', $a);
        $aContent = $a->getContent();
        $this->assertEquals('Go to somewhere...', $aContent[0]->getContent());
    }
    
    public function testFeedbackBlockRuptureValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/feedbackblock_other_content_than_block.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this->assertTrue($valid, 'Even if the content of the feedbackBlock is invalid from the paper spec point of view, it is XSD valid. See rupture points.');
        
        $doc->load($file);
        $this->assertTrue(true);
    }
    
    public function testPromptRuptureNoValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/prompt_other_content_than_inlinestatic.xml';
        $doc->load($file);
        
        $search = $doc->getDocumentComponent()->getComponentsByClassName('prompt');
        $prompt = $search[0];
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\prompt', $prompt);
        
        $promptContent = $prompt->getContent();
        $this->assertEquals('Hell ', $promptContent[0]->getContent());
        $div = $promptContent[1];
        $divContent = $div->getContent();
        $this->assertEquals('YEAH!', $divContent[0]->getContent());
        
        $search = $doc->getDocumentComponent()->getComponentsByClassName('choiceInteraction');
        $choiceInteraction = $search[0];
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $choiceInteraction);
        
        $simpleChoices = $choiceInteraction->getSimpleChoices();
        $this->assertEquals(1, count($simpleChoices));
        
        $simpleChoiceContent = $simpleChoices[0]->getContent();
        $this->assertEquals('Resistance is futile!', $simpleChoiceContent[0]->getContent());
    }
    
    public function testPromptRuptureValidation() {
        $doc = new XmlDocument();
        $file = self::samplesDir() . 'custom/paper_vs_xsd/prompt_other_content_than_inlinestatic.xml';
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        $valid = $dom->schemaValidate(dirname(__FILE__) . '/../../../../../src/qtism/data/storage/xml/schemes/qtiv2p1/imsqti_v2p1.xsd');
        $this->assertTrue($valid, 'Even if the content of the prompt is invalid from the paper spec point of view, it is XSD valid. See rupture points.');
        
        $doc->load($file);
        $this->assertTrue(true);
    }
    
    public function testAmps() {
        $file = self::samplesDir() . 'custom/amps.xml';
        $doc = new XmlDocument();
        $doc->load($file);
        
        $root = $doc->getDocumentComponent();
        $divs = $root->getComponentsByClassName('div');
        $this->assertEquals(1, count($divs));
        
        $divContent = $divs[0]->getContent();
        $divText = $divContent[0];
        $this->assertEquals('Hello there & there! I am trying to make <you> "crazy"', $divText->getcontent());
    }
    
    public function testWrongVersion() {
        $this->setExpectedException('\\InvalidArgumentException');
        $doc = new XMLDocument('2.2.3');
    }
    
    public function testLoadFromString() {
        $doc = new XmlDocument('2.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        
        $component = $doc->getDocumentComponent();
        $this->assertInstanceOf('\\qtism\\data\\AssessmentItemRef', $component);
        $this->assertEquals('Q01', $component->getIdentifier());
        $this->assertEquals('./Q01.xml', $component->getHref());
    }
    
    public function testLoadFromEmptyString() {
        $doc = new XmlDocument('2.1');
        
        $expectedMsg = "Cannot load QTI from an empty string.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::READ);
        
        $doc->loadFromString('');
    }
    
    public function testLoadFromMalformedString() {
        $doc = new XmlDocument('2.1');
        
        $expectedMsg = "An internal error occured while parsing QTI-XML:\nFatal Error: Premature end of data in tag assessmentItem line 1 at 1:17.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::READ);
        
        $doc->loadFromString('<assessmentItem>');
    }
    
    public function testLoadNoVersion() {
        $doc = new XmlDocument('2.1');
        
        $expectedMsg = "Cannot infer QTI version. Check namespaces and schema locations in XML file.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        
        $doc->load(self::samplesDir() . 'invalid/noversion.xml');
    }
    
    public function testLoadFromEmptyFile() {
        $doc = new XmlDocument('2.1');
        // This path does not resolve anything.
        $path = self::samplesDir() . 'invalid/unknown.xml';
        
        $expectedMsg = "Cannot load QTI file '${path}'. It does not exist or is not readable.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::RESOLUTION);
        
        $doc->load($path);
    }
    
    public function testLoadFromStringNotSupportedElement20() {
        // Will throw an error because assessmentItemRef is not supported in QTI 2.0.
        $doc = new XmlDocument('2.0');
        $expectedMsg = "'assessmentItemRef' components are not supported in QTI version '2.0.0'.";
        
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
    }
    
    public function testSaveNoMarshaller20() {
        $doc = new XMLDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        $doc->setVersion('2.0');
        
        $expectedMsg = "'assessmentItemRef' components are not supported in QTI version '2.0.0'.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg);
        
        $str = $doc->saveToString();
    }
    
    public function testVersionDoesNotChangeLoadFromString()
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        // Version always returned as MAJOR.MINOR.PATCH
        $this->assertEquals('2.1.1', $doc->getVersion());
    }
    
    public function testSaveUnknownLocation()
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('<assessmentItemRef identifier="Q01" href="./Q01.xml"/>');
        
        $expectedMsg = "An error occured while saving QTI-XML file at '/unknown/location/qti.xml'. Maybe the save location is not reachable?";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::WRITE);
        
        $doc->save('/unknown/location/qti.xml');
    }
    
    public function testUnknownClassWhileSavingBecauseOfVersion1()
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
                <matchTable>
                    <matchTableEntry sourceValue="1" targetValue="2.5"/>
                </matchTable>
            </outcomeDeclaration>'
        );
        
        // This should fail because in QTI 2.0.0, <matchTable> does not exist.
        $doc->setVersion('2.0.0');
        
        $expectedMsg = "'matchTable' components are not supported in QTI version '2.0.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        $str = $doc->saveToString(true);
    }
    
    public function testUnknownClassWhileLoadingBecauseOfVersion1()
    {
        $expectedMsg = "'matchTable' components are not supported in QTI version '2.0.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        
        // This will fail because no <matchTable> element is defined in the 2.0.0 QTI Information Model.
        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
                <matchTable>
                    <matchTableEntry sourceValue="1" targetValue="2.5"/>
                </matchTable>
            </outcomeDeclaration>'
        );
    }
    
    public function testUnknownClassWhileSavingBecauseOfVersion2()
    {
        $doc = new XmlDocument('2.1.1');
        $doc->loadFromString('
            <sum>
                <subtract>
                    <mathConstant name="pi"/>
                    <mathConstant name="pi"/>            
                </subtract>
            </sum>'
        );
        
        // This should fail because in QTI 2.0.0, <mathConstant does not exist>.
        $doc->setVersion('2.0.0');
        
        $expectedMsg = "'mathConstant' components are not supported in QTI version '2.0.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        $str = $doc->saveToString(true);
    }
    
    public function testUnknownClassWhileLoadingBecauseOfVersion2()
    {
        $expectedMsg = "'mathConstant' components are not supported in QTI version '2.0.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        
        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <sum>
                <subtract>
                    <mathConstant name="pi"/>
                    <mathConstant name="pi"/>
                </subtract>
            </sum>'
        );
    }
    
    public function testUnknownClassWhileSavingBecauseOfVersion3()
    {
        $doc = new XmlDocument('2.2.0');
        $doc->loadFromString('
            <div>
                <bdo dir="rtl">I am reversed!</bdo>            
            </div>'
        );
        
        // This should fail because in QTI 2.2.0 because <bdo> does not exist.
        $doc->setVersion('2.1.0');
        
        $expectedMsg = "'bdo' components are not supported in QTI version '2.1.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
        $str = $doc->saveToString(true);
    }
    
    public function testUnknownClassWhileLoadingBecauseOfVersion3()
    {
        $expectedMsg = "'bdo' components are not supported in QTI version '2.0.0'";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::VERSION);
    
        $doc = new XmlDocument('2.0.0');
        $doc->loadFromString('
            <div>
                <bdo dir="rtl">I am reversed!</bdo>            
            </div>'
        );
    }
    
    public function testInvalidAgainstXMLSchema()
    {
        $expectedMsg = "The document could not be validated with XML Schema:\n";
        $expectedMsg .= "Error: Element '{http://www.imsglobal.org/xsd/imsqti_v2p1}responseDeclaration', attribute 'foo': The attribute 'foo' is not allowed. at 9:0.";
        $this->setExpectedException('qtism\\data\\storage\\xml\\XmlStorageException', $expectedMsg, XmlStorageException::XSD_VALIDATION);
        
        $uri = self::samplesDir() . 'invalid/xsdinvalid.xml';
        $doc = new XmlDocument('2.1.0');
        $doc->load($uri, true);
    }
}
