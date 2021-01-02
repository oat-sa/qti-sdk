<?php

namespace qtismtest\data\storage\xml;

use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;
use qtism\data\expressions\Correct;
use qtism\data\expressions\Variable;
use qtism\data\expressions\operators\Match;
use qtism\data\rules\ResponseCondition;
use qtism\data\processing\ResponseProcessing;

/**
 * Class XmlResponseProcessingDocumentTest
 */
class XmlResponseProcessingDocumentTest extends QtiSmTestCase
{
    public function testLoadMatchCorrect()
    {
        $xml = new XmlDocument('2.1');
        $xml->load(self::getTemplatesPath() . '2_1/match_correct.xml');
        $this->assertInstanceOf(ResponseProcessing::class, $xml->getDocumentComponent());
        $this->assertFalse($xml->getDocumentComponent()->hasTemplateLocation());
        $this->assertFalse($xml->getDocumentComponent()->hasTemplate());

        $responseRules = $xml->getDocumentComponent()->getResponseRules();
        $this->assertEquals(1, count($responseRules));

        $responseCondition = $responseRules[0];
        $this->assertInstanceOf(ResponseCondition::class, $responseCondition);

        $responseIf = $responseCondition->getResponseIf();
        $match = $responseIf->getExpression();
        $this->assertInstanceOf(Match::class, $match);

        $matchExpressions = $match->getExpressions();
        $this->assertEquals(2, count($matchExpressions));
        $variable = $matchExpressions[0];
        $this->assertInstanceOf(Variable::class, $variable);
        $this->assertEquals('RESPONSE', $variable->getIdentifier());
        $correct = $matchExpressions[1];
        $this->assertInstanceOf(Correct::class, $correct);
        $this->assertEquals('RESPONSE', $correct->getIdentifier());
        // To be continued...
    }

    /**
     * @dataProvider loadProvider
     *
     * @param string $url
     * @throws XmlStorageException
     */
    public function testLoad($url)
    {
        $xml = new XmlDocument();
        $xml->load($url, true);
        $this::assertTrue(true);
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

    /**
     * @return array
     */
    public function loadProvider()
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
