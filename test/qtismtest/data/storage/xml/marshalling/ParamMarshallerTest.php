<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\xhtml\Param;
use qtism\data\content\xhtml\ParamType;
use qtismtest\QtiSmTestCase;

class ParamMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshallSimple()
    {
        $param = $this->createComponentFromXml('
            <param name="movie" value="movie.swf" valuetype="REF" type="application/x-shockwave-flash"/>            
	    ');

        $this->assertInstanceOf(Param::class, $param);
        $this->assertEquals('movie', $param->getName());
        $this->assertEquals('movie.swf', $param->getValue());
        $this->assertEquals(ParamType::REF, $param->getValueType());
        $this->assertEquals('application/x-shockwave-flash', $param->getType());
    }

    public function testMarshallSimple()
    {
        $param = new Param('movie', 'movie.swf', ParamType::REF, 'application/x-shockwave-flash');

        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($param)->marshall($param);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals('<param name="movie" value="movie.swf" valuetype="REF" type="application/x-shockwave-flash"/>', $dom->saveXml($element));
    }
}
