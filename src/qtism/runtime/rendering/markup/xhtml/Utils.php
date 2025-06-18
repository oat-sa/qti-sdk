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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use DOMElement;
use DOMNode;
use qtism\data\ShufflableCollection;

/**
 * Utility class focusing on XHTML rendering.
 */
class Utils
{
    public const EXTRACT_IF = 0;

    public const EXTRACT_INCLUDE = 1;

    /**
     * Shuffle the elements related to $shufflables components within a given $node.
     *
     * @param DOMNode $node The DOM Node where corresponding $shufflables must be shuffled.
     * @param ShufflableCollection $shufflables A collection of Shufflable objects.
     */
    public static function shuffle(DOMNode $node, ShufflableCollection $shufflables): void
    {
        $shufflableIndexes = [];
        $elements = [];

        // 1. Detect what are the components that must
        // be shuffles within the fragment ($shufflableIndexes).
        //
        // 2. Store the related DOMElements into a
        // more suitable way ($elements).

        foreach ($shufflables as $k => $s) {
            $i = 0;

            while ($i < $node->childNodes->length) {
                $n = $node->childNodes->item($i);
                $i++;
                if ($n->nodeType === XML_ELEMENT_NODE && self::hasClass($n, 'qti-' . $s->getQtiClassName()) === true && !in_array($n, $elements, true)) {
                    $elements[] = $n;

                    if ($s->isFixed() === false) {
                        $shufflableIndexes[] = $k;
                    }

                    break;
                }
            }
        }

        // Swap elements to place them in shuffled order
        $shuffledIndexes = $shufflableIndexes;
        shuffle($shuffledIndexes);
        $map = self::getSwappingMapByValues($shuffledIndexes, $shufflableIndexes);
        foreach ($map as $swapPair) {
            list($elIndex1, $elIndex2) = $swapPair;
            $element = $elements[$elIndex1];
            $elementToSwap = $elements[$elIndex2];

            $placeholder1 = $node->ownerDocument->createElement('placeholder1');
            $placeholder2 = $node->ownerDocument->createElement('placeholder2');

            $statements1 = self::extractStatements($element);
            $statements2 = self::extractStatements($elementToSwap);

            $node->replaceChild($placeholder1, $element);
            $node->replaceChild($placeholder2, $elementToSwap);

            $placeholder1 = $node->replaceChild($elementToSwap, $placeholder1);
            $placeholder2 = $node->replaceChild($element, $placeholder2);

            if (empty($statements1) === false && empty($statements2) === false) {
                for ($i = 0; $i < 2; $i++) {
                    $node->removeChild($statements1[$i]);
                    $node->replaceChild($statements1[$i], $statements2[$i]);
                }

                $node->insertBefore($statements2[0], $elementToSwap);
                $elementToSwap->parentNode->insertBefore($statements2[1], $elementToSwap->nextSibling);
            } elseif (empty($statements1) === false && empty($statements2)) {
                $node->removeChild($statements1[0]);
                $node->removeChild($statements1[1]);

                $node->insertBefore($statements1[0], $element);
                $element->parentNode->insertBefore($statements1[1], $element->nextSibling);
            } elseif (empty($statements2) === false && empty($statements1)) {
                $node->removeChild($statements2[0]);
                $node->removeChild($statements2[1]);

                $node->insertBefore($statements2[0], $elementToSwap);
                $elementToSwap->parentNode->insertBefore($statements2[1], $elementToSwap->nextSibling);
            }

            unset($placeholder1);
            unset($placeholder2);
        }
    }

    public static function getSwappingMapByValues(array $shufflableIndexes, array $shuffledIndexes): array
    {
        $swappingMapByValues = [];
        $tempShuffled = $shuffledIndexes; // Create a temporary copy to modify

        // Create a reverse lookup for the temporary shuffled array: value => current_index
        $valueToIndexMap = array_flip($tempShuffled);

        for ($i = 0; $i < count($shufflableIndexes); $i++) {
            $expectedValue = $shufflableIndexes[$i]; // The value that should be at this position
            $currentValue = $tempShuffled[$i];     // The value currently at this position

            // If the current value is not the expected value, a swap is needed
            if ($currentValue !== $expectedValue) {
                // Find the current index of the expected value
                $indexOfExpectedValue = $valueToIndexMap[$expectedValue];

                // Record the swap pair: [value_to_move_out, value_to_move_in]
                $swappingMapByValues[] = [$currentValue, $expectedValue];

                // --- Perform the actual swap in the temporary array (using indexes) ---
                // This is where the value-based map gets translated back to index operations
                list($tempShuffled[$i], $tempShuffled[$indexOfExpectedValue]) = [$tempShuffled[$indexOfExpectedValue], $tempShuffled[$i]];

                // --- Update the valueToIndexMap because values have moved ---
                $valueToIndexMap[$currentValue] = $indexOfExpectedValue; // The 'currentValue' moved to the old spot of 'expectedValue'
                $valueToIndexMap[$expectedValue] = $i;                 // The 'expectedValue' moved to the current spot
            }
        }

        return $swappingMapByValues;
    }

    /**
     * Whether a given $node element has the given CSS $class(es).
     *
     * @param DOMElement $node
     * @param string|array $class A class or an array of CSS classes.
     * @return bool
     */
    public static function hasClass(DOMElement $node, $class): bool
    {
        if (is_array($class) === false) {
            $class = [$class];
        }

        $attr = explode("\x20", $node->getAttribute('class'));

        foreach ($class as $c) {
            if (in_array($c, $attr) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract qtism-if/qtism-endif statements around a given $node.
     *
     * @param DOMElement $node
     * @param int $type
     * @return array An array of DOMComment objects.
     */
    public static function extractStatements(DOMElement $node, $type = self::EXTRACT_IF): array
    {
        $statements = [];
        $extract = [
            'qtism-if',
            'qtism-endif',
        ];

        if ($type === self::EXTRACT_INCLUDE) {
            $extract = [
                'qtism-include',
                'qtism-endinclude',
            ];
        }

        $sibling = $node->previousSibling;
        while ($sibling && $sibling->nodeType === XML_COMMENT_NODE) {
            if (strpos(trim($sibling->data), $extract[0]) === 0) {
                $statements[] = $sibling;
                break;
            }
            $sibling = $sibling->previousSibling;
        }

        if (empty($statements) === false) {
            $sibling = $node->nextSibling;
            while ($sibling && $sibling->nodeType === XML_COMMENT_NODE) {
                if (strpos(trim($sibling->data), $extract[1]) === 0) {
                    $statements[] = $sibling;
                    return $statements;
                }
                $sibling = $sibling->nextSibling;
            }
        }

        return [];
    }
}
