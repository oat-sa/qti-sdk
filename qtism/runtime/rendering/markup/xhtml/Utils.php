<?php

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\data\ShufflableCollection;
use \DOMNode;
use \RuntimeException;

/**
 * Utility class focusing on XHTML rendering.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils {
    
    /**
     * Shuffle the elements related to $shufflables components within a given $node.
     * 
     * @param DOMNode $node The DOM Node where corresponding $shufflables must be shuffled.
     * @param ShufflableCollection $shufflables A collection of Shufflable objects.
     * @throws RuntimeException If not all given Shufflable objects can be found into the child elements of $node.
     */
    static public function shuffle(DOMNode $node, ShufflableCollection $shufflables) {
        $shufflableIndexes = array();
        $elements = array();
        
        // 1. Detect what are the components that must
        // be shuffles within the fragment ($shufflableIndexes).
        //
        // 2. Store the related DOMElements into a
        // more suitable way ($elements). 
        $i = 0;
        foreach ($shufflables as $k => $s) {
            // 1...
            if ($s->isFixed() === false) {
                $shufflableIndexes[] = $k;
            }
            
            // 2...
            while ($i < $node->childNodes->length) {
                $n = $node->childNodes->item($i);
                $i++;
                
                if ($n->nodeType === XML_ELEMENT_NODE && $n->nodeName === $s->getQtiClassName()) {
                    $elements[] = $n;
                    break;
                }
            }
        }
        
        // A little bit of consistency check...
        if (count($shufflables) !== count($elements)) {
            
            $msg = "Could not found all related DOM elements (" . count($elements) . ") into the given Shufflable objects (" . count($shufflables) . ").";
            throw new RuntimeException($msg);
        }

        // Swap two elements together N times where N is the number of shufflable components.
        $count = count($shufflableIndexes);
        $max = $count - 1;
        for ($i = 0; $i < $count; $i++) {
            $r1 = mt_rand(0, $max);
            $r2 = mt_rand(0, $max);
            
            if ($r1 !== $r2) {
                // Do only if swapping is 'useful'...
                $placeholder1 = $node->ownerDocument->createElement('placeholder1');
                $placeholder2 = $node->ownerDocument->createElement('placeholder2');
                
                $node->replaceChild($placeholder1, $elements[$shufflableIndexes[$r1]]);
                $node->replaceChild($placeholder2, $elements[$shufflableIndexes[$r2]]);

                $placeholder1 = $node->replaceChild($elements[$shufflableIndexes[$r2]], $placeholder1);
                $placeholder2 = $node->replaceChild($elements[$shufflableIndexes[$r1]], $placeholder2);
                
                unset($placeholder1);
                unset($placeholder2);
            }
        }
    }
}