<?php
require_once (dirname(__FILE__) . '/../../../QtiSmRouteTestCase.php');

use qtism\runtime\tests\SelectableRoute;
use \OutOfBoundsException;

class SelectableRouteTest extends QtiSmRouteTestCase {
    
    public function testSwap() {
        $route = self::buildSimpleRoute();
        $this->checkSequence($route, array('Q1', 'Q2', 'Q3'));
        
        $route->swap(0, 2);
        // New sequence is Q3, Q2, Q1
        $this->checkSequence($route, array('Q3', 'Q2', 'Q1'));
        
        $route->swap(0, 1);
        // New sequence is Q2, Q3, Q1 
        $this->checkSequence($route, array('Q2', 'Q3', 'Q1'));
        
        $route->swap(1, 1);
        // Sequence is unchanged.
        $this->checkSequence($route, array('Q2', 'Q3', 'Q1'));
        
        $route->swap(1, 2);
        $this->checkSequence($route, array('Q2', 'Q1', 'Q3'));
        
        // First position out of bounds.
        try {
            $route->swap(5, 6);
            // OutOfBounds!!!
            $this->assertTrue(false);
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
        
        // Second position out of bounds.
        try {
            $route->swap(0, 4);
            // OutOfBounds!!!
            $this->assertTrue(false);
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
    }
    
    protected function checkSequence(SelectableRoute $route, array $idSequence) {
        for ($i = 0; $i < count($idSequence); $i++) {
            $this->assertEquals($idSequence[$i], $route->getRouteItemAt($i)->getAssessmentItemRef()->getIdentifier());
        }
    }
    
    /**
     * @return SelectableRoute
     */
    public static function buildSimpleRoute($fixed = false, $required = false, $visible = true) {
        $selectableRoute = QtiSmRouteTestCase::buildSimpleRoute('qtism\\runtime\\tests\\SelectableRoute');
        $selectableRoute->setFixed($fixed);
        $selectableRoute->setRequired($required);
        $selectableRoute->setVisible($visible);
        
        return $selectableRoute;
    }
}