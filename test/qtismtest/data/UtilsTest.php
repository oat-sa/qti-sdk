<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data;

use qtism\data\storage\xml\XmlDocument;
use qtismtest\QtiSmTestCase;
use qtism\data\Utils as DataUtils;
use qtism\common\utils\Format;

class UtilsTest extends QtiSmTestCase
{
    public function testGetFirstItem()
    {
        // Simple cases

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpathwithpre2.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q01'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('Q01'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q03'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q05'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('TP02'), $sections));

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingsubsections.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q05'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S04'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q03'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S03'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q01'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('TP01'), $sections));
        $this->assertEquals(null, DataUtils::getFirstItem($test, $test, $sections));
    }

    public function testgetFirstItem2()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingtestparts.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q06'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('TP03'), $sections));

        // Recursive subsections

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingsubsections2.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q04'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q07'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S07'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q07'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S04'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q11'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S13'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q99'),
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S99'), $sections));
        $this->assertEquals(null,
            DataUtils::getFirstItem($test, $test->getComponentByIdentifier('S95'), $sections));
    }

    public function testGetLastItem()
    {
        // Simple cases

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingpathwithpre2.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q01'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('Q01'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q04'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q08'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('TP02'), $sections));

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingsubsections.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q08'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S04'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q04'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S03'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q08'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('TP01'), $sections));
        $this->assertEquals(null, DataUtils::getLastItem($test, $test, $sections));
    }

    public function testgetLastItem2()
    {
        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingtestparts.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q05'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('TP03'), $sections));

        // Recursive subsections

        $doc = new XmlDocument();
        $doc->load(self::samplesDir() . 'custom/tests/branchingsubsections2.xml');
        $test = $doc->getDocumentComponent();
        $sections = $test->getComponentsByClassName("assessmentSection")->getArrayCopy();

        $this->assertEquals($test->getComponentByIdentifier('Q03'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S02'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q06'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S07'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q10'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S13'), $sections));
        $this->assertEquals($test->getComponentByIdentifier('Q99'),
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S98'), $sections));
        $this->assertEquals(null,
            DataUtils::getLastItem($test, $test->getComponentByIdentifier('S96'), $sections));
    }

    public function testSanitizeIdentifier()
    {
       $testIdentifiers = ["GoodIdentifier", "abc 123", "@bc", "2017id", "abc@@@", "20i17d", "20id@@", "9bc", "bc@"];
       $correctIdentifiers = ["GoodIdentifier", "abc123", "bc", "id", "abc", "i17d", "id", "bc", "bc"];

       for ($i = 0; $i < count($testIdentifiers); $i++) {
           $this->assertEquals($testIdentifiers[$i] == $correctIdentifiers[$i],
               Format::isIdentifier($testIdentifiers[$i]), false);
           $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($testIdentifiers[$i]), false));
           $this->assertEquals($correctIdentifiers[$i], Format::sanitizeIdentifier($testIdentifiers[$i]), false);
       }

       $unsanitizableIdentifiers = ["", "\"", "123@"];

        for ($i = 0; $i < count($unsanitizableIdentifiers); $i++) {
            $this->assertTrue(Format::isIdentifier(Format::sanitizeIdentifier($unsanitizableIdentifiers[$i]), false));
        }
    }
}