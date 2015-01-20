<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\Direction;
use qtism\data\content\xhtml\text\Q;
use qtism\data\content\xhtml\A;
use qtism\data\content\xhtml\text\Em;
use qtism\data\content\TextRun;
use qtism\data\content\InlineCollection;
use qtism\data\content\xhtml\text\Strong;
use \DOMDocument;

class SimpleInlineMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
		$strong = new Strong('john');
		$strong->setLabel('His name');
		$strong->setContent(new InlineCollection(array(new TextRun('John Dunbar'))));
		
		$em = new Em('sentence', 'introduction', 'en-US');
		$em->setContent(new InlineCollection(array(new TextRun('He is '), $strong, new TextRun('.'))));
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($em);
		$element = $marshaller->marshall($em);
		$dom = new DOMDocument('1.0', 'UTF-8');
		$element = $dom->importNode($element, true);
		
		$this->assertEquals('<em id="sentence" class="introduction" xml:lang="en-US">He is <strong id="john" label="His name">John Dunbar</strong>.</em>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<em id="sentence" class="introduction" xml:lang="en-US">He is <strong id="john" label="His name">John Dunbar</strong>.</em>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
		$em = $marshaller->unmarshall($element);
		$this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Em', $em);
		$this->assertEquals('sentence', $em->getId());
		$this->assertEquals('introduction', $em->getClass());
		$this->assertEquals('en-US', $em->getLang());
		
		$sentence = $em->getContent();
		$this->assertInstanceOf('qtism\\data\\content\\InlineCollection', $sentence);
		$this->assertEquals(3, count($sentence));
		
		$this->assertInstanceOf('qtism\\data\\content\\TextRun', $sentence[0]);
		$this->assertEquals('He is ', $sentence[0]->getContent());
		
		$this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Strong', $sentence[1]);
		$strongContent = $sentence[1]->getContent();
		$this->assertEquals('John Dunbar', $strongContent[0]->getContent());
		$this->assertEquals('john', $sentence[1]->getId());
		$this->assertEquals('His name', $sentence[1]->getLabel());
		
		$this->assertInstanceOf('qtism\\data\\content\\TextRun', $sentence[2]);
		$this->assertEquals('.', $sentence[2]->getContent());
	}
	
	public function testMarshallQandA21() {
	    $q = new Q('albert-einstein');
	    
	    $a = new A('http://en.wikipedia.org/wiki/Physicist');
	    $a->setType('text/html');
	    $a->setContent(new InlineCollection(array(new TextRun('physicist'))));
	    $q->setContent(new InlineCollection(array(new TextRun('Albert Einstein is a '), $a, new TextRun('.'))));
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    
	    $this->assertEquals('<q id="albert-einstein">Albert Einstein is a <a href="http://en.wikipedia.org/wiki/Physicist" type="text/html">physicist</a>.</q>', $dom->saveXML($element));
	}
	
	public function testUnmarshallQandA21() {
	    $q = $this->createComponentFromXml('<q id="albert-einstein">Albert Einstein is a <a href="http://en.wikipedia.org/wiki/Physicist" type="text/html">physicist</a>.</q>');
	    $this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Q', $q);
	}
	
	public function testUnmarshall22Ltr() {
	    $q = $this->createComponentFromXml('
	        <q id="albert-einstein" class="albie yeah" dir="ltr">
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');
	    
	    $this->assertEquals('albie yeah', $q->getClass());
	    $this->assertEquals('albert-einstein', $q->getId());
	    $this->assertEquals(Direction::LTR, $q->getDir());
	}
	
	public function testUnmarshall22Rtl() {
	    $q = $this->createComponentFromXml('
	        <q dir="rtl">
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');
	    
	    $this->assertEquals(Direction::RTL, $q->getDir());
	}
	
	public function testUnmarshall22DirAuto() {
	    $q = $this->createComponentFromXml('
	        <q>
	            I am Albert Einstein!
	        </q>
	    ', '2.2.0');
	     
	    $this->assertEquals(Direction::AUTO, $q->getDir());
	}
	
	public function testUnmarshall21DirAuto() {
	    $q = $this->createComponentFromXml('
	        <q>
	            I am Albert Einstein!
	        </q>
	    ', '2.1.0');
	
	    $this->assertEquals(Direction::AUTO, $q->getDir());
	}
	
	public function testMarshall22Rtl() {
	    $q = new Q('albert');
	    $q->setDir(Direction::RTL);
	    
	    $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    
	    $this->assertEquals('<q id="albert" dir="rtl"/>', $dom->saveXML($element));
	}
	
	public function testMarshall21Rtl() {
	    $q = new Q('albert');
	    $q->setDir(Direction::RTL);
	     
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	     
	    $this->assertEquals('<q id="albert"/>', $dom->saveXML($element));
	}
	
	public function testMarshall20Rtl() {
	    $q = new Q('albert');
	    $q->setDir(Direction::RTL);
	
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	
	    $this->assertEquals('<q id="albert"/>', $dom->saveXML($element));
	}
	
	public function testMarshall22Ltr() {
	    $q = new Q('albert');
	    $q->setDir(Direction::LTR);
	
	    $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	
	    $this->assertEquals('<q id="albert" dir="ltr"/>', $dom->saveXML($element));
	}
	
	public function testMarshall22DirAuto() {
	    $q = new Q('albert');
	    $q->setDir(Direction::AUTO);
	
	    $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($q);
	    $element = $marshaller->marshall($q);
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	
	    $this->assertEquals('<q id="albert"/>', $dom->saveXML($element));
	}
}