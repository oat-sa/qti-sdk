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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\runtime\rendering;

use qtism\data\ShowHide;
use qtism\data\content\FeedbackElement;
use qtism\runtime\common\State;
use qtism\data\ViewCollection;
use qtism\data\QtiComponent;
use \SplStack;
use \DOMDocument;

abstract class AbstractRenderingEngine extends AbstractRenderer implements RenderingConfig {

    /**
     * 
     * @var array
     */
    private $explorationMarker;
    
    /**
     * 
     * @var SplStack
     */
    private $exploration;
    
    /**
     * The currently rendered component.
     * 
     * @var QtiComponent
     */
    private $exploredComponent = null;
    
    /**
     * 
     * @var mixed
     */
    private $lastRendering = null;
    
    public function __construct() {
        parent::__construct($this->createRenderingContext());
    }
    
    /**
     * 
     * @return SplStack
     */
    protected function getExploration() {
        return $this->exploration;
    }
    
    /**
     * 
     * @param SplStack $exploration
     */
    protected function setExploration(SplStack $exploration) {
        $this->exploration = $exploration;
    }
    
    /**
     * 
     * @return array
     */
    protected function getExplorationMarker() {
        return $this->explorationMarker;
    }
    
    /**
     * 
     * @param array $explorationMarker
     */
    protected function setExplorationMarker(array $explorationMarker) {
        $this->explorationMarker = $explorationMarker;
    }
    
    /**
     * 
     * @return QtiComponent
     */
    protected function getExploredComponent() {
        return $this->exploredComponent;
    }
    
    /**
     * 
     * @param QtiComponent $component
     */
    protected function setExploredComponent(QtiComponent $component = null) {
        $this->exploredComponent = $component;
    }
    
    /**
     * 
     * @param mixed $rendering
     */
    protected function setLastRendering($rendering) {
        $this->lastRendering = $rendering;
    }
    
    /**
     * 
     * @return mixed
     */
    protected function getLastRendering() {
        return $this->lastRendering;
    }
    
    public function render(QtiComponent $component) {
        
        $this->setExploration(new SplStack());
        $this->setExplorationMarker(array());
        $this->setLastRendering(null);
        
        // Put the root $component on the stack.
        if ($this->mustIgnoreComponent($component) === false) {
            $this->getExploration()->push($component);
        }
        
        while (count($this->getExploration()) > 0) {
            $this->setExploredComponent($this->getExploration()->pop());
            
            // Component is final or not?
            $final = $this->isFinal();
            
            // Component is explored or not?
            $explored = $this->isExplored();
            
            if ($final === false && $explored === false) {
                // Hierarchical node: 1st pass.
                $this->markAsExplored($this->getExploredComponent());
                $this->getExploration()->push($this->getExploredComponent());
                
                foreach ($this->getNextExploration() as $toExplore) {
                    // Maybe the component must be ignored?
                    if ($this->mustIgnoreComponent($toExplore) === false) {
                        $this->getExploration()->push($toExplore);
                    }
                }
            }
            else if ($final === false && $explored === true) {
                // Hierarchical node: 2nd pass.
                $this->processNode();
                
                if ($this->getExploredComponent() === $component) {
                    // End of the rendering.
                    break;
                }
            }
            else {
                // Leaf node.
                $this->processNode();
                
                if ($this->getExploredComponent() === $component) {
                    // End of the rendering (leaf node is actually a lone root).
                    break;
                }
            }
        }
        
        $finalRendering = $this->createFinalRendering();
        $this->getRenderingContext()->reset();
        return $finalRendering;
    }
    
    public function ignoreQtiClasses($classes) {
        $this->getRenderingContext()->ignoreQtiClasses($classes);
    }
    
    public function getIgnoreClasses() {
        return $this->getRenderingContext()->getIgnoreClasses();
    }
    
    /**
     * 
     * @return boolean
     */
    protected function isFinal() {
        return count($this->getNextExploration()) === 0;
    }
    
    /**
     * 
     * @return QtiComponentCollection
     */
    protected function getNextExploration() {
        return $this->getExploredComponent()->getComponents();
    }
    
    /**
     * 
     * @return boolean
     */
    protected function isExplored() {
        return in_array($this->getExploredComponent(), $this->getExplorationMarker(), true);
    }
    
    /**
     * 
     * @param QtiComponent $component
     */
    protected function markAsExplored(QtiComponent $component) {
        $marker = $this->getExplorationMarker();
        $marker[] = $component;
        $this->setExplorationMarker($marker);
    }
    
    /**
     * 
     * @return AbstractRenderingContext
     */
    abstract protected function createRenderingContext();
    
    /**
     * 
     * @return mixed
     */
    abstract protected function createFinalRendering();
    
    /**
     * 
     */
    protected function processNode() {
        $renderer = $this->getRenderingContext()->getRenderer($this->getExploredComponent());
        $rendering = $renderer->render($this->getExploredComponent());
        $this->setLastRendering($rendering);
    }
    
    /**
     * Whether a component must be ignored or not while rendering. The following cases
     * makes a component to be ignored:
     * 
     * * The ChoiceHideShow policy is set to CONTEXT_AWARE and the variable referenced by the Choice's templateIdentifier attribute does not match the expected value.
     * * The FeedbackHideShow policy is set to CONTEXT_AWARE and the variable referenced by the FeedbackElement's identifier attribute does not match the expected value.
     * * The class of the Component is in the list of QTI classes to be ignored.
     * 
     * @param QtiComponent $component A Component you want to know if it has to be ignored or not.
     * @return boolean
     */
    protected function mustIgnoreComponent(QtiComponent $component) {
        
        // In the list of QTI class names to be ignored?
        if (in_array($component->getQtiClassName(), $this->getIgnoreClasses()) === true) {
            return true;
        }
        // Context Aware + FeedbackElement
        else if ($this->getFeedbackShowHidePolicy() === RenderingConfig::CONTEXT_AWARE && $component instanceof FeedbackElement) {
            
            $outcomeIdentifier = $component->getOutcomeIdentifier();
            $identifier = $component->getIdentifier();
            $showHide = $component->getShowHide();
            $state = $this->getState();
            
            $matches = ($val = $state[$outcomeIdentifier]) !== null && $val === $identifier;
            return ($showHide === ShowHide::SHOW) ? !$matches : $matches;
        }
        else {
            return false;
        }
        
        // @todo CONTEXT_AWARE + Choice + to be hidden?
        
        // @todo CONTEXT_AWARE + RubricBlock + to be hidden?
    }
    
    public function setChoiceShowHidePolicy($policy) {
        $this->getRenderingContext()->setChoiceShowHidePolicy($policy);
    }
    
    public function getChoiceShowHidePolicy() {
        return $this->getRenderingContext()->getChoiceShowHidePolicy();
    }
    
    public function setFeedbackShowHidePolicy($policy) {
        $this->getRenderingContext()->setFeedbackShowHidePolicy($policy);
    }
    
    public function getFeedbackShowHidePolicy() {
        return $this->getRenderingContext()->getFeedbackShowHidePolicy();
    }
    
    public function setViewPolicy($policy) {
        $this->getRenderingContext()->setViewPolicy($policy);
    }
    
    public function getViewPolicy() {
        return $this->getRenderingContext()->getViewPolicy();
    }
    
    public function setViews(ViewCollection $views) {
        $this->getRenderingContext()->setViews($views);
    }
    
    public function getViews() {
        return $this->getRenderingContext()->getViews();
    }
    
    public function setState(State $state) {
        $this->getRenderingContext()->setState($state);
    }
    
    public function getState() {
        return $this->getRenderingContext()->getState();
    }
}