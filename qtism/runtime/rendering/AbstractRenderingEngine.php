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

use qtism\data\content\interactions\Choice;
use qtism\data\content\RubricBlock;
use qtism\data\ShowHide;
use qtism\data\content\FeedbackElement;
use qtism\runtime\common\State;
use qtism\data\ViewCollection;
use qtism\data\QtiComponent;
use \SplStack;
use \DOMDocument;

/**
 * The base class to be used by any rendering engines.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractRenderingEngine extends AbstractRenderer implements RenderingConfig {

    /**
     * An array used to 'tag' explored Component object.
     * 
     * @var array
     */
    private $explorationMarker;
    
    /**
     * The stack of Component object that still have to be explored.
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
     * The last rendering.
     * 
     * @var mixed
     */
    private $lastRendering = null;
    
    /**
     * Create a new AbstractRenderingObject.
     * 
     */
    public function __construct() {
        parent::__construct($this->createRenderingContext());
    }
    
    /**
     * Get the Stack of Component objects that to be still explored.
     * 
     * @return SplStack
     */
    protected function getExploration() {
        return $this->exploration;
    }
    
    /**
     * Set the Stack of Component objects that have to be still explored.
     * 
     * @param SplStack $exploration
     */
    protected function setExploration(SplStack $exploration) {
        $this->exploration = $exploration;
    }
    
    /**
     * Set the array used to 'tag' components in order to know
     * whether or not they are already explored.
     * 
     * @return array
     */
    protected function getExplorationMarker() {
        return $this->explorationMarker;
    }
    
    /**
     * Set the array used to 'tag' components in order to know whether
     * or not they are already explored.
     * 
     * @param array $explorationMarker
     */
    protected function setExplorationMarker(array $explorationMarker) {
        $this->explorationMarker = $explorationMarker;
    }
    
    /**
     * Get the currently explored component.
     * 
     * @return QtiComponent
     */
    protected function getExploredComponent() {
        return $this->exploredComponent;
    }
    
    /**
     * Set the currently explored Component object.
     * 
     * @param QtiComponent $component
     */
    protected function setExploredComponent(QtiComponent $component = null) {
        $this->exploredComponent = $component;
    }
    
    /**
     * Set the last rendering.
     * 
     * @param mixed $rendering
     */
    protected function setLastRendering($rendering) {
        $this->lastRendering = $rendering;
    }
    
    /**
     * Get the last rendering.
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
     * Whether or not the currently explored Component object
     * is a final leaf of the tree structured explored hierarchy.
     * 
     * @return boolean
     */
    protected function isFinal() {
        return count($this->getNextExploration()) === 0;
    }
    
    /**
     * Get the children components of the currently explored component
     * for future exploration.
     * 
     * @return QtiComponentCollection The children Component object of the currently explored Component object.
     */
    protected function getNextExploration() {
        return $this->getExploredComponent()->getComponents();
    }
    
    /**
     * Wether or not the currently explored component has been already explored.
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
     * Create an appropriate rendering context (factory method).
     * 
     * @return AbstractRenderingContext
     * @see http://en.wikipedia.org/wiki/Factory_method_pattern Factory Method pattern.
     */
    abstract protected function createRenderingContext();
    
    /**
     * Create the final rendering as it must be rendered by the final
     * implementation.
     * 
     * @return mixed
     */
    abstract protected function createFinalRendering();
    
    /**
     * Process the current node.
     */
    protected function processNode() {
        $renderer = $this->getRenderingContext()->getRenderer($this->getExploredComponent());
        $rendering = $renderer->render($this->getExploredComponent());
        $this->setLastRendering($rendering);
    }
    
    /**
     * Whether or not a component must be ignored or not while rendering. The following cases
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
        // Context Aware + FeedbackElement OR Context Aware + Choice
        else if (($component instanceof FeedbackElement && $this->getFeedbackShowHidePolicy() === RenderingConfig::CONTEXT_AWARE) || ($component instanceof Choice && $component->hasTemplateIdentifier() === true && $this->getChoiceShowHidePolicy() === RenderingConfig::CONTEXT_AWARE)) {
            $matches = $this->identifierMatches($component);
            $showHide = $component->getShowHide();
            return ($showHide === ShowHide::SHOW) ? !$matches : $matches;
        }
        // Context Aware + RubricBlock
        else if ($this->getViewPolicy() === RenderingConfig::CONTEXT_AWARE && $component instanceof RubricBlock) {
            $renderingViews = $this->getViews();
            $rubricViews = $component->getViews();
            
            // If one of the rendering views matches a single view
            // in the rubricBlock's view, render!
            foreach ($renderingViews as $v) {
                if ($rubricViews->contains($v) === true) {
                    return false;
                }
            }
            
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Whether or not the 'outcomeIdentifier'/'templateIdentifier' set on a templateElement/feedbackElement/choice
     * matches its 'identifier' attribute.
     * 
     * @param QtiComponent $component A TemplateElement or FeedbackElement or Choice element.
     * @return boolean
     */
    protected function identifierMatches(QtiComponent $component) {
        $variableIdentifier = ($component instanceof FeedbackElement) ? $component->getOutcomeIdentifier() : $component->getTemplateIdentifier();
        $identifier = $component->getIdentifier();
        $showHide = $component->getShowHide();
        $state = $this->getState();
        
        return (($val = $state[$variableIdentifier]) !== null && $val === $identifier);
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