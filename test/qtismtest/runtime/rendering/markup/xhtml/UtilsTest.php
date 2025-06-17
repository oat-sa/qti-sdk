<?php

namespace qtismtest\runtime\rendering\markup\xhtml;

use DOMDocument;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\ShufflableCollection;
use qtism\runtime\rendering\markup\xhtml\Utils;
use qtismtest\QtiSmTestCase;

/**
 * Class RenderingMarkupXhtmlUtils
 */
class RenderingMarkupXhtmlUtils extends QtiSmTestCase
{
    public function testShuffleWithFixed(): void
    {
        // It is difficult to test a random algorithm.
        // In this way, we just check it runs. Deeper
        // analysis can be done in /test/scripts/.

        // DOM creation...
        $dom = new DOMDocument('1.0', 'UTF-8');
        $node = $dom->createElement('fakenode');
        $dom->appendChild($node);

        $choice = $dom->createElement('div');
        $choice->setAttribute('fixed', 'false');
        $choice->setAttribute('id', 'choice1');
        $choice->setAttribute('class', 'qti-simpleChoice');
        $node->appendChild($choice);

        $choice = $dom->createElement('div');
        $choice->setAttribute('fixed', 'true');
        $choice->setAttribute('id', 'choice2');
        $choice->setAttribute('class', 'qti-simpleChoice qti-hide');
        $node->appendChild($choice);

        $choice = $dom->createElement('div');
        $choice->setAttribute('fixed', 'false');
        $choice->setAttribute('id', 'choice3');
        $choice->setAttribute('class', 'qti-simpleChoice');
        $node->appendChild($choice);

        $choice = $dom->createElement('div');
        $choice->setAttribute('fixed', 'true');
        $choice->setAttribute('id', 'choice4');
        $choice->setAttribute('class', 'qti-simpleChoice');
        $node->appendChild($choice);

        $choice = $dom->createElement('div');
        $choice->setAttribute('fixed', 'false');
        $choice->setAttribute('id', 'choice5');
        $choice->setAttribute('class', 'qti-simpleChoice');
        $node->appendChild($choice);

        // In memory model creation ...
        $shufflables = new ShufflableCollection();

        $choice = new SimpleChoice('choice1');
        $choice->setFixed(false);
        $choice->setId('choice1');
        $shufflables[] = $choice;

        $choice = new SimpleChoice('choice2');
        $choice->setFixed(true);
        $choice->setId('choice2');
        $shufflables[] = $choice;

        $choice = new SimpleChoice('choice3');
        $choice->setFixed(false);
        $choice->setId('choice3');
        $shufflables[] = $choice;

        $choice = new SimpleChoice('choice4');
        $choice->setFixed(true);
        $choice->setId('choice4');
        $shufflables[] = $choice;

        $choice = new SimpleChoice('choice5');
        $choice->setFixed(false);
        $choice->setId('choice5');
        $shufflables[] = $choice;

        Utils::shuffle($node, $shufflables);

        // Let's check if fixed 'choice2' and 'choice4' are still in place...
        $this::assertEquals(
            'choice2',
            $node->getElementsByTagName('div')->item(1)->getAttribute('id')
        );
        $this::assertEquals(
            'choice4',
            $node->getElementsByTagName('div')->item(3)->getAttribute('id')
        );

        // Check shuffled
        $node0Id = $node->getElementsByTagName('div')->item(0)->getAttribute('id');
        $node2Id = $node->getElementsByTagName('div')->item(2)->getAttribute('id');
        $node4Id = $node->getElementsByTagName('div')->item(4)->getAttribute('id');
        $shuffled = ['choice1', 'choice3', 'choice5'];
        $place0 = array_search($node0Id, $shuffled);
        $place2 = array_search($node2Id, $shuffled);
        $place4 = array_search($node4Id, $shuffled);

        // None of them lost
        $this::assertFalse(
            $place0 === false || $place2 === false || $place4 === false
        );
        // None of them repeated
        $this::assertFalse(
            $place0 == $place2 || $place0 == $place4 || $place2 == $place4
        );
        // None fixed duplicates
        $this::assertEquals(0, count(array_intersect(['choice2', 'choice4'], [$node0Id, $node2Id, $node4Id])));
        // Overall still the same amount of choices
        $this::assertEquals(5, $node->getElementsByTagName('div')->count());
    }

    public function testShuffleWithStatements(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <interaction>
                <!-- qtism-if (bla) --><li class="qti-simpleChoice" data-identifier="bla">bla</li><!-- qtism-endif -->
                <li class="qti-simpleChoice" data-identifier="bli">bli</li>
                <!-- qtism-if (blu) --><li class="qti-simpleChoice" data-identifier="blu">blu</li><!-- qtism-endif -->
            </interaction>
        ');

        $shufflables = new ShufflableCollection();
        $shufflables[] = new SimpleChoice('bla');
        $shufflables[] = new SimpleChoice('bli');
        $shufflables[] = new SimpleChoice('blu');

        Utils::shuffle($dom->documentElement, $shufflables);
        $this::assertTrue(true);
    }

    public function testHasClass(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $node = $dom->createElement('root');

        $node->setAttribute('class', 'hello there');

        $this::assertTrue(Utils::hasClass($node, 'hello'));
        $this::assertTrue(Utils::hasClass($node, 'there'));
        $this::assertTrue(Utils::hasClass($node, ['hello', 'there']));
        $this::assertFalse(Utils::hasClass($node, 'unknown'));
        $this::assertFalse(Utils::hasClass($node, ['unknown', 'class']));
    }

    public function testExtractStatements(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $node = $dom->createElement('fakenode');
        $dom->appendChild($node);

        $div = $dom->createElement('div');
        $node->appendChild($div);

        $if = $dom->createComment('qtism-if (true)');
        $endif = $dom->createComment('qtism-endif');

        $node->insertBefore($if, $div);
        $node->appendChild($endif);

        $statements = Utils::extractStatements($div);
        $this::assertEquals('qtism-if (true)', $statements[0]->data);
        $this::assertEquals('qtism-endif', $statements[1]->data);
    }

    public function testExtractStatementsNothing(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $node = $dom->createElement('fakenode');
        $dom->appendChild($node);

        $div = $dom->createElement('div');
        $node->appendChild($div);

        $this::assertEquals([], Utils::extractStatements($div));
    }

    public function testExtractStatementsIfOnly(): void
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $node = $dom->createElement('fakenode');
        $dom->appendChild($node);

        $div = $dom->createElement('div');
        $node->appendChild($div);

        $if = $dom->createComment('qtism-if (true)');

        $node->insertBefore($if, $div);

        $statements = Utils::extractStatements($div);
        $this::assertEquals([], $statements);
    }
}
