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

use qtism\runtime\common\State;
use qtism\data\View;
use qtism\data\ViewCollection;
use qtism\data\QtiComponent;
use \SplStack;

/**
 * An AbstractRenderingContext object represents the context in which
 * a hierarchy of QtiComponents are rendered by a set of
 * AbstractRenderer objects.
 * 
 * All Rendering Policies are by default set to RenderingConfig::CONTEXT_STATIC.
 * See the RenderingConfig interface for more information.
 * 
 * The contextual QTI Views for the CONTEXT_AWARE rendering mode are by default
 * all the values containes in the View enumeration.
 * 
 * The State used for the CONTEXT_AWARE is by default an empty State object.
 * See the State class for more information about states management in QtiSm.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @see RenderingConfig
 * @see qtism\data\View For information about the QTI Views.
 * @see qtism\runtime\common\State For information about State management.
 *
 */
abstract class AbstractRenderingContext implements RenderingConfig {
    
    /**
     * The stack where rendered components in other
     * constitution are stored for a later use.
     * 
     * @var SplStack
     */
    private $renderingStack;
    
    /**
     * An associative array where keys are QTI class names
     * and values are AbstractRenderer objects.
     * 
     * @var array
     */
    private $renderers;
    
    /**
     * An array containing the QTI classes to be ignored
     * for rendering.
     * 
     * @var array
     */
    private $ignoreClasses = array();
    
    /**
     * The Choice rendering policy.
     * 
     * @var integer
     * @see RenderingConfig For information about rendering policies.
     */
    private $choiceShowHidePolicy = RenderingConfig::CONTEXT_STATIC;
    
    /**
     * The Feedback rendering policy.
     * 
     * @var integer
     * @see RenderingConfig For information about rendering policies
     */
    private $feedbackShowHidePolicy = RenderingConfig::CONTEXT_STATIC;
    
    /**
     * The View rendering policy.
     * 
     * @var integer
     * @see RenderingConfig For information about rendering policies
     */
    private $viewPolicy = RenderingConfig::CONTEXT_STATIC;
    
    /**
     * The QTI views to be used while rendering in CONTEXT_AWARE mode. 
     * 
     * @var ViewCollection
     */
    private $views;
    
    /**
     * The State object used in CONTEXT_AWARE rendering mode.
     * 
     * @var State
     */
    private $state;
    
    /**
     * Create a new AbstractRenderingContext object.
     * 
     */
    public function __construct() {
        $this->setRenderers(array());
        $this->setRenderingStack(new SplStack());
        $this->setViews(new ViewCollection(array(View::AUTHOR, View::CANDIDATE, View::PROCTOR, View::SCORER, View::TEST_CONSTRUCTOR, View::TUTOR)));
        $this->setState(new State());
    }
    
    /**
     * Set the renderers array.
     * 
     * @param array $renderers
     */
    protected function setRenderers(array $renderers) {
        $this->renderers = $renderers;
    }
    
    /**
     * Get the renderers array.
     * 
     * @return array
     */
    protected function getRenderers() {
        return $this->renderers;
    }
    
    /**
     * Set the array containing the QTI class names
     * to be ignored for rendering.
     * 
     * @param array $ignoreClasses
     */
    protected function setIgnoreClasses(array $ignoreClasses) {
        $this->ignoreClasses = $ignoreClasses;
    }
    
    public function getIgnoreClasses() {
        return $this->ignoreClasses;
    }
    
    public function ignoreQtiClasses($classes) {
        if (is_string($classes) === true) {
            $classes = array($classes);
        }
        
        $ignoreClasses = $this->getIgnoreClasses();
        $ignoreClasses = array_unique(array_merge($ignoreClasses, $classes));
        
        $this->setIgnoreClasses($ignoreClasses);
    }
    
    /**
     * Register a $renderer object to a given $qtiClassName.
     * 
     * @param string $qtiClassName A QTI class name.
     * @param AbstractRenderer $renderer An AbstractRenderer object.
     */
    public function registerRenderer($qtiClassName, AbstractRenderer $renderer) {
        $renderer->setRenderingContext($this);
        $renderers = $this->getRenderers();
        $renderers[$qtiClassName] = $renderer;
        $this->setRenderers($renderers);
    }
    
    /**
     * Get the AbstractRenderer implementation which is appropriate to render the given
     * QtiComponent $component.
     * 
     * @param QtiComponent $component A QtiComponent object you want to get the appropriate AbstractRenderer implementation.
     * @throws RenderingException If no implementation of AbstractRenderer is registered for $component.
     * @return AbstractRenderer The AbstractRenderer implementation to render $component.
     */
    public function getRenderer(QtiComponent $component) {
        $renderers = $this->getRenderers();
        $className = $component->getQtiClassName();
        
        if (isset($renderers[$className]) === true) {
            return $renderers[$className];
        }
        else {
            $msg = "No AbstractRenderer implementation registered for QTI class name '${className}'.";
            throw new RenderingException($msg, RenderingException::NO_RENDERER);
        }
    }
    
    /**
     * Get the stack of rendered components stored for a later use
     * by AbstractRenderer objects.
     * 
     * @return SplStack
     */
    protected function getRenderingStack() {
        return $this->renderingStack;
    }
    
    /**
     * Set the stack of rendered components stored
     * for a later use by AbstractRenderer objects.
     * 
     * @param SplStack $renderingStack
     */
    protected function setRenderingStack(SplStack $renderingStack) {
        $this->renderingStack = $renderingStack;
    }
    
    /**
     * Store a rendered component as a rendering for a later use
     * by AbstractRenderer objects.
     * 
     * @param QtiComponent $component The $component from which the rendering was made.
     * @param mixed $rendering A component rendered in another format.
     */
    public function storeRendering(QtiComponent $component, $rendering) {
        $this->getRenderingStack()->push(array($component, $rendering));
    }
    
    /**
     * Get the renderings related to the children of $component.
     * 
     * @param QtiComponent $component A QtiComponent object to be rendered.
     * @return array
     */
    public function getChildrenRenderings(QtiComponent $component) {
        
        $returnValue = array();
            
        foreach ($component->getComponents() as $c) {
            
            if (count($this->getRenderingStack()) > 0) {
                list($renderedComponent, $rendering) = $this->getRenderingStack()->pop();
                
                if ($c === $renderedComponent) {
                    $returnValue[] = $rendering;
                }
                else {
                    // repush...
                    $this->storeRendering($renderedComponent, $rendering);
                }
            }
        }
        
        return $returnValue;
    }
    
    /**
     * Reset the context to its initial state, in order
     * to be ready for reuse.
     * 
     */
    public function reset() {
        $this->setRenderingStack(new SplStack());
    }
    
    public function setChoiceShowHidePolicy($policy) {
        $this->choiceShowHidePolicy = $policy;
    }
    
    public function getChoiceShowHidePolicy() {
        return $this->choiceShowHidePolicy;
    }
    
    public function setFeedbackShowHidePolicy($policy) {
        $this->feedbackShowHidePolicy = $policy;
    }
    
    public function getFeedbackShowHidePolicy() {
        return $this->feedbackShowHidePolicy;
    }
    
    public function setViewPolicy($policy) {
        $this->viewPolicy = $policy;
    }
    
    public function getViewPolicy() {
        return $this->viewPolicy;
    }
    
    public function setViews(ViewCollection $views) {
        $this->views = $views;
    }
    
    public function getViews() {
        return $this->views;
    }
    
    public function setState(State $state) {
        $this->state = $state;
    }
    
    public function getState() {
        return $this->state;
    }
}