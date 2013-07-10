<?php

use qtism\data\storage\xml\Utils;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class XmlUtilsTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider validInferQTIVersionProvider
	 */
	public function testInferQTIVersionValid($file, $expectedVersion) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->load($file);
		$this->assertEquals($expectedVersion, Utils::inferQTIVersion($dom));
	}
	
	public function validInferQTIVersionProvider() {
		return array(
			array(self::samplesDir() . 'ims/items/2_1/associate.xml', '2.1'),
			array(self::samplesDir() . 'ims/items/2_0/associate.xml', '2.0'),
			array(self::samplesDir() . 'ims/tests/arbitrary_collections_of_item_outcomes/arbitrary_collections_of_item_outcomes.xml', '2.1')		
		);
	}
}