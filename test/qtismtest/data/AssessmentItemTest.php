<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\data;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use qtism\data\AssessmentItem;
use qtism\data\content\ItemBody;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\content\StylesheetCollection;
use qtism\data\processing\ResponseProcessing;
use qtism\data\QtiComponentCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\TemplateDeclarationCollection;

class AssessmentItemTest extends TestCase
{
    /**
     * @var AssessmentItem
     */
    private $subject;
    public function setUp(): void
    {
        $this->subject = new AssessmentItem(
            '123456789 asbcdżć',
            'title string',
            true,
            'pinglish'
        );
    }

    public function testAssessmentItemCreation(): void
    {
        $this->assertEquals('123456789 asbcdżć', $this->subject->getIdentifier());
        $this->assertEquals('title string', $this->subject->getTitle());
        $this->assertTrue($this->subject->isTimeDependent());
        $this->assertInstanceOf(ResponseDeclarationCollection::class, $this->subject->getResponseDeclarations());
        $this->assertInstanceOf(OutcomeDeclarationCollection::class, $this->subject->getOutcomeDeclarations());
        $this->assertInstanceOf(TemplateDeclarationCollection::class, $this->subject->getTemplateDeclarations());
        $this->assertInstanceOf(StylesheetCollection::class, $this->subject->getStylesheets());
        $this->assertInstanceOf(ModalFeedbackCollection::class, $this->subject->getModalFeedbacks());
        $this->assertEquals('assessmentItem', $this->subject->getQtiClassName());
        $this->assertInstanceOf(QtiComponentCollection::class, $this->subject->getComponents());
    }

    public function testAssessmentItemSetters(): void
    {
        $this->subject->setResponseProcessing(new ResponseProcessing());
        $this->subject->setToolName('toolName shorter 256 characters');
        $this->subject->setToolVersion('Tool version string shorter than 256 characters');

        $this->subject->setItemBody(
            new ItemBody('idString', 'classString', 'langString', 'labelString')
        );

        $this->assertInstanceOf(StylesheetCollection::class, $this->subject->getStylesheets());
        $this->assertInstanceOf(ItemBody::class, $this->subject->getItemBody());
        $this->assertInstanceOf(ResponseProcessing::class, $this->subject->getResponseProcessing());
        $this->assertInstanceOf(ModalFeedbackCollection::class, $this->subject->getModalFeedbacks());
        $this->assertEquals('assessmentItem', $this->subject->getQtiClassName());
        $this->assertInstanceOf(QtiComponentCollection::class, $this->subject->getComponents());
        $this->assertEquals('toolName shorter 256 characters', $this->subject->getToolName());
        $this->assertTrue($this->subject->hasToolName());
        $this->assertEquals('Tool version string shorter than 256 characters', $this->subject->getToolVersion());
        $this->assertTrue($this->subject->hasToolVersion());
        $this->assertInstanceOf(ItemBody::class, $this->subject->getItemBody());
        $this->assertTrue($this->subject->hasItemBody());
    }

    /**
     * @dataProvider getInvalidAssessmentItemData
     */
    public function testAssessmentItemValidation($identifier, $title, $timeDependent, $lang)
    {
        $this->expectException(InvalidArgumentException::class);
        new AssessmentItem($identifier, $title, $timeDependent, $lang);
    }

    public function setToolNameTest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->subject->setToolName(str_repeat('a', 257));
    }

    public function setToolVersionTest()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->subject->setToolVersion(str_repeat('a', 257));
    }

    public function getInvalidAssessmentItemData()
    {
        return [
            'Wrong identifier' => ["string \t with tab", 'title string', true ,'pinglish'],
            'Wrong title' => [
                '123456789 asbcdżć',
                1234,
                true,
                'pinglish'
            ],
            'Wrong isTimeDependent' => [
                '123456789 asbcdżć',
                'title string',
                'true',
                'pinglish'
            ],
            'Wrong lang' => [
                '123456789 asbcdżć',
                'title string',
                'true',
                true
            ],
        ];
    }
}
