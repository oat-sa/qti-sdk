<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiDatatype;
use qtism\data\state\ResponseValidityConstraint;
use qtism\runtime\tests\Utils as TestUtils;

class TestUtilsTest extends QtiSmTestCase {
    
    /**
     * @dataProvider isResponseValidProvider
     */
    public function testIsResponseValid($expected, QtiDatatype $response = null, ResponseValidityConstraint $constraint) {
        $this->assertEquals($expected, TestUtils::isResponseValid($response, $constraint));
    }
    
    public function isResponseValidProvider() {
        return array(
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 0)),
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 1)),
            array(true, null, new ResponseValidityConstraint('RESPONSE', 0, 3)),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 1, 3)),
            array(false, null, new ResponseValidityConstraint('RESPONSE', 2, 3)),
        );
    }
}
