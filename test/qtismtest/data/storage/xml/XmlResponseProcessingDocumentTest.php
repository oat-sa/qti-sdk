<?php

namespace qtismtest\data\storage\xml;

use qtism\data\storage\xml\XmlDocument;
use qtismtest\QtiSmTestCase;

class XmlResponseProcessingDocumentTest extends QtiSmTestCase
{
    public function testLoadMatchCorrect()
    {
        $xml = new XmlDocument('2.1');
        $xml->load(self::getTemplatesPath() . '2_1/match_correct.xml');
        $this->assertInstanceOf('qtism\\data\\processing\\ResponseProcessing', $xml->getDocumentComponent());
        $this->assertFalse($xml->getDocumentComponent()->hasTemplateLocation());
        $this->assertFalse($xml->getDocumentComponent()->hasTemplate());

        $responseRules = $xml->getDocumentComponent()->getResponseRules();
        $this->assertEquals(1, count($responseRules));

        $responseCondition = $responseRules[0];
        $this->assertInstanceOf('qtism\\data\\rules\\ResponseCondition', $responseCondition);

        $responseIf = $responseCondition->getResponseIf();
        $match = $responseIf->getExpression();
        $this->assertInstanceOf('qtism\\data\\expressions\\operators\\Match', $match);

        $matchExpressions = $match->getExpressions();
        $this->assertEquals(2, count($matchExpressions));
        $variable = $matchExpressions[0];
        $this->assertInstanceOf('qtism\\data\\expressions\\Variable', $variable);
        $this->assertEquals('RESPONSE', $variable->getIdentifier());
        $correct = $matchExpressions[1];
        $this->assertInstanceOf('qtism\\data\\expressions\\Correct', $correct);
        $this->assertEquals('RESPONSE', $correct->getIdentifier());
        // To be continued...
    }

    /**
     * @dataProvider testLoadProvider
     *
     * @param string $url
     */
    public function testLoad($url)
    {
        $xml = new XmlDocument();
        $xml->load($url, true);
    }

    /**
     * Returns the location of the templates on the file system
     * WITH A TRAILING SLASH.
     *
     * @return string
     */
    public static function getTemplatesPath()
    {
        return dirname(__FILE__) . '/../../../../../src/qtism/runtime/processing/templates/';
    }

    public function testLoadProvider()
    {
        return [
            [self::getTemplatesPath() . '2_1/match_correct.xml'],
            [self::getTemplatesPath() . '2_1/map_response.xml'],
            [self::getTemplatesPath() . '2_1/map_response_point.xml'],
            [self::getTemplatesPath() . '2_0/match_correct.xml'],
            [self::getTemplatesPath() . '2_0/map_response.xml'],
            [self::getTemplatesPath() . '2_0/map_response_point.xml'],
        ];
    }
}
