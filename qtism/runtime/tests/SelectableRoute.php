<?php

namespace qtism\runtime\tests;

use \OutOfBoundsException;

/**
 * The SelectableRoute class aims at representing a Route which is
 * subject to be selected in a selection process.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SelectableRoute extends Route {
    
    /**
     * If the SelectableRoute is fixed.
     * 
     * @var boolean
     */
    private $visible;
    
    /**
     * If the SelectableRoute is visible.
     * 
     * @var boolean
     */
    private $fixed;
    
    /**
     * If the SelectableRoute is required.
     * 
     * @var boolean
     */
    private $required;
    
    /**
     * Create a new SelectableRoute object.
     * 
     * @param boolean $fixed If the SelectableRoute is fixed.
     * @param boolean $visible If the SelectableRoute is visible.
     */
    public function __construct($fixed = false, $required = false, $visible = true) {
        parent::__construct();
        $this->setFixed($fixed);
        $this->setRequired($required);
        $this->setVisible($visible);
    }
    
    /**
     * Whether the SelectableRoute is fixed.
     * 
     * @return boolean
     */
    public function isFixed() {
        return $this->fixed;
    }
    
    /**
     * Whether the SelectableRoute is visible.
     * 
     * @return boolean
     */
    public function isVisible() {
        return $this->visible;
    }
    
    /**
     * Whether the SelectableRoute is required.
     * 
     * @return boolean
     */
    public function isRequired() {
        return $this->required;
    }
    
    /**
     * Set whether the SelectableRoute is fixed.
     * 
     * @param boolean $fixed
     */
    public function setFixed($fixed) {
        $this->fixed = $fixed;
    }
    
    /**
     * Set whether the SelectableRoute is visible.
     * 
     * @param boolean $visible
     */
    public function setVisible($visible) {
        $this->visible = $visible;
    }
    
    /**
     * Set Whether the SelectableRoute is required.
     * 
     * @param boolean $required
     */
    public function setRequired($required) {
        $this->required = $required;
    }
    
    /**
     * Swap RouteItem at position $key1 with the RouteItem
     * at position $key2.
     * 
     * @param int $position1 A RouteItem position.
     * @param int $position2 A RouteItem position.
     * @throws OutOfBoundsException If $position1 or $position2 are not poiting to any RouteItem.
     */
    public function swap($position1, $position2) {
        $routeItems = &$this->getRouteItems();
        
        if (isset($routeItems[$position1]) === false) {
            $msg = "No RouteItem object at position '${position1}'.";
            throw new OutOfBoundsException($msg);
        }
        
        if (isset($routeItems[$position2]) === false) {
            $msg = "No RouteItem object at position '${position2}'.";
            throw new OutOfBoundsException($msg);
        }
        
        $temp = $routeItems[$position2];
        $routeItems[$position2] = $routeItems[$position1];
        $routeItems[$position1] = $temp;
    }
}