<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\xhtml\Img;
use \DOMDocument;

class ImgMarshallerTest extends QtiSmTestCase
{
	public function testMarshall()
    {
	    $img = new Img('my/image.png', "An Image...", "my-img");
	    $img->setClass('beautiful');
	    $img->setHeight('40%');
	    $img->setWidth(30);
	    $img->setLang('en-YO');
	    $img->setLongdesc("A Long Description...");
	    $img->setXmlBase('/home/jerome');
	    
 	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($img);
 	    $element = $marshaller->marshall($img);
	    
 	    $dom = new DOMDocument('1.0', 'UTF-8');
 	    $element = $dom->importNode($element, true);
 	    $this->assertEquals('<img src="my/image.png" alt="An Image..." width="30" height="40%" longdesc="A Long Description..." xml:base="/home/jerome" id="my-img" class="beautiful" xml:lang="en-YO"/>', $dom->saveXML($element));
	}
	
	public function testUnmarshall()
    {
	    $element = $this->createDOMElement('
            <img xml:base="/home/jerome" src="my/image.png" alt="An Image..." width="30" height="40%" longdesc="A Long Description..." id="my-img" class="beautiful" xml:lang="en-YO"/>
	    ');
	    
 	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
 	    $img = $marshaller->unmarshall($element);
 	    
 	    $this->assertInstanceOf('qtism\\data\\content\\xhtml\\Img', $img);
 	    $this->assertEquals('my/image.png', $img->getSrc());
 	    $this->assertEquals('An Image...', $img->getAlt());
 	    $this->assertSame(30, $img->getWidth());
 	    $this->assertEquals('40%', $img->getHeight());
 	    $this->assertEquals('A Long Description...', $img->getLongDesc());
 	    $this->assertEquals('my-img', $img->getId());
 	    $this->assertEquals('beautiful', $img->getClass());
 	    $this->assertEquals('en-YO', $img->getLang());
 	    $this->assertEquals('/home/jerome',$img->getXmlBase());
	}
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallPercentage()
    {
        $element = $this->createDOMElement('
            <img src="my/image.png" alt="An Image..." width="30%" height="40%"/>
	    ');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $img = $marshaller->unmarshall($element);
        
        $this->assertEquals('40%', $img->getHeight());
        $this->assertEquals('30%', $img->getWidth());
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallMissingSrc()
    {
        $element = $this->createDOMElement('
            <img/>
	    ');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The 'mandatory' attribute 'src' is missing from element 'img'."
        );
        
        $marshaller->unmarshall($element);
    }
}
