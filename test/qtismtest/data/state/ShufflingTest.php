<?php
namespace qtismtest\data\state;

use qtismtest\QtiSmTestCase;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingGroup;
use qtism\common\collections\IdentifierCollection;
use qtism\data\state\ShufflingGroupCollection;

class ShufflingTest extends QtiSmTestCase {
    
	public function testShufflingShuffle() {
	    $identifiers1 = new IdentifierCollection(array('id1', 'id2', 'id3', 'id4', 'id5'));
	    $identifiers2 = new IdentifierCollection(array('id6', 'id7', 'id8', 'id9', 'id10'));
	    $group1 = new ShufflingGroup($identifiers1);
	    $group2 = new ShufflingGroup($identifiers2);
	    $group2->setFixedIdentifiers(new IdentifierCollection(array('id7', 'id8', 'id9')));
	    
	    $shuffling = new Shuffling('RESPONSE', new ShufflingGroupCollection(array($group1, $group2)));
	    $shuffled = $shuffling->shuffle();
	    
	    // Shuffling::shuffle makes a deep cloning...
	    $this->assertNotSame($shuffling, $shuffled);
	    $originalGroups = $shuffling->getShufflingGroups();
	    $shuffledGroups = $shuffled->getShufflingGroups();
	    $this->assertNotSame($originalGroups, $shuffledGroups);
	    $this->assertNotSame($originalGroups[0], $shuffledGroups[0]);
	    $this->assertNotSame($originalGroups[1], $shuffledGroups[1]);
	    $this->assertNotSame($originalGroups[0]->getIdentifiers(), $shuffledGroups[0]->getIdentifiers());
	    $this->assertNotSame($originalGroups[1]->getIdentifiers(), $shuffledGroups[1]->getIdentifiers());
	    
	    // We can check 1 thing:
	    // Each identifier of original group is contained in shuffled group.
	    for ($i = 0; $i < count($originalGroups); $i++) {
	        $originalIdentifiers = $originalGroups[$i]->getIdentifiers();
	        
	        for ($j = 0; $j < count($originalIdentifiers); $j++) {
	            $this->assertTrue($shuffledGroups[$i]->getIdentifiers()->contains($originalIdentifiers[$j]));
	        }
	    }
	}
	
	public function testGetIdentifierAtWithValidIndexes() {
	    $identifiers1 = new IdentifierCollection(array('id1', 'id2', 'id3'));
	    $identifiers2 = new IdentifierCollection(array('id4', 'id5', 'id6'));
	    $group1 = new ShufflingGroup($identifiers1);
	    $group2 = new ShufflingGroup($identifiers2);
	    
	    $shuffling = new Shuffling('RESPONSE', new ShufflingGroupCollection(array($group1, $group2)));
	    
	    $this->assertEquals('id1', $shuffling->getIdentifierAt(0));
	    $this->assertEquals('id2', $shuffling->getIdentifierAt(1));
	    $this->assertEquals('id3', $shuffling->getIdentifierAt(2));
	    $this->assertEquals('id4', $shuffling->getIdentifierAt(3));
	    $this->assertEquals('id5', $shuffling->getIdentifierAt(4));
	    $this->assertEquals('id6', $shuffling->getIdentifierAt(5));
	}
	
	/**
	 * @dataProvider getIdentifierAtWithInvalidIndexesProvider
	 */
	public function testGetIdentifierAtWithInvalidIndexes($index) {
	    $identifiers1 = new IdentifierCollection(array('id1', 'id2', 'id3'));
	    $identifiers2 = new IdentifierCollection(array('id4', 'id5', 'id6'));
	    $group1 = new ShufflingGroup($identifiers1);
	    $group2 = new ShufflingGroup($identifiers2);
	    $shuffling = new Shuffling('RESPONSE', new ShufflingGroupCollection(array($group1, $group2)));
	    
	    $this->setExpectedException('\\OutOfBoundsException');
	    $identifier = $shuffling->getIdentifierAt($index);
	}
	
	public function getIdentifierAtWithInvalidIndexesProvider() {
	    return array(
	        array(-1),
	        array('string'),
	        array(7)                
	    );
	}
}
