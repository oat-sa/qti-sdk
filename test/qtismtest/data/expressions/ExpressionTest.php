<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 20.04.17
 * Time: 15:20
 */

namespace qtismtest\data\expressions;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\XmlDocument;


class ExpressionTest extends QtiSmTestCase
{
    public function testIsPure()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpath.xml');
        $test = $doc->getDocumentComponent();

        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $itemq1 = $doc->getDocumentComponent()->getComponentByIdentifier('Q01');

        $this->assertEquals(true, $itemq3->getBranchRules()[0]->getExpression()->IsPure());
        $this->assertEquals(false, $itemq1->getBranchRules()[0]->getExpression()->IsPure());

        $doc->load(self::samplesDir() . 'custom/tests/branchingexpressions.xml');
        $test = $doc->getDocumentComponent();
        $impures = ['Q5', 'Q37', 'Q47', 'Q48', 'Q49', 'Q50', 'Q52', 'Q53', 'Q55'];

        for ($i = 1; $i < 56; $i++) {
            $this->assertEquals(!in_array('Q' . $i, $impures),
                $test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression()->IsPure());
        }
    }

    public function testQtiPL()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpath.xml');
        $test = $doc->getDocumentComponent();

        $itemq3 = $doc->getDocumentComponent()->getComponentByIdentifier('Q03');
        $this->assertEquals("true == true", $itemq3->getBranchRules()[0]->getExpression()->toQtiPL());

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingexpressions.xml');
        $test = $doc->getDocumentComponent();

        // Getting the right branchingexpressionsQtiPL answers

        $handle = fopen(self::samplesDir() . 'custom/tests/branchingexpressionsQtiPL.txt', "r");
        $qtiPLExpressions = [];
        $maxTest = 56;

        if ($handle) {

            $lineNumber = 1;

            while (($line = fgets($handle)) !== false && $lineNumber < $maxTest) {
                $qtiPLExpressions["Q" . $lineNumber] = trim(substr($line, strpos($line, "@") + 1));
                $lineNumber++;
            }

            fclose($handle);
        }

        for ($i = 1; $i < $maxTest; $i++) {
            $this->assertEquals($qtiPLExpressions["Q" . $i],
                $test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression()->toQtiPL());
        }

        /*
        for ($i = 1; $i < 56; $i++) {
            var_dump($test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression()->toQtiPL());
        }*/

        // TODO : test with empty expression
        // TODO : CustomOperator, what do to with $externalComponent
    }

    public function testcoverageforQtiPL()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/coverageforQtiPL.xml');
        $test = $doc->getDocumentComponent();

        // Getting the right coverageforQtiPL answers

        $handle = fopen(self::samplesDir() . 'custom/tests/coverageforQtiPLanswers.txt', "r");
        $qtiPLExpressions = [];
        $maxTest = 30 + 1;

        if ($handle) {

            $lineNumber = 1;

            while (($line = fgets($handle)) !== false && $lineNumber < $maxTest) {
                $qtiPLExpressions["Q" . $lineNumber] = trim(substr($line, strpos($line, "@") + 1));
                $lineNumber++;
            }

            fclose($handle);
        }

        for ($i = 1; $i < $maxTest; $i++) {
            $this->assertEquals($qtiPLExpressions["Q" . $i],
                $test->getComponentByIdentifier('Q' . $i)->getBranchRules()[0]->getExpression()->toQtiPL());
        }
    }
}