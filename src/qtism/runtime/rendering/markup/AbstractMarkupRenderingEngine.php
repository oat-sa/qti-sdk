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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\rendering\markup;

use qtism\data\AssessmentItem;
use qtism\data\content\PrintedVariable;
use qtism\data\content\interactions\Gap;
use qtism\common\collections\Container;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiScalar;
use qtism\data\ExternalQtiComponent;
use qtism\runtime\rendering\RenderingException;
use qtism\runtime\rendering\Renderable;
use qtism\runtime\rendering\markup\xhtml\PrintedVariableRenderer;
use qtism\data\content\ModalFeedback;
use qtism\data\content\interactions\Interaction;
use qtism\common\utils\Url;
use qtism\data\QtiComponentCollection;
use qtism\data\content\Flow;
use qtism\data\content\interactions\Choice;
use qtism\data\content\RubricBlock;
use qtism\data\ShowHide;
use qtism\data\content\FeedbackElement;
use qtism\data\storage\php\Utils as PhpUtils;
use qtism\runtime\common\State;
use qtism\data\ViewCollection;
use qtism\data\QtiComponent;
use \SplStack;
use \DOMDocument;
use \DOMDocumentFragment;
use \DOMXPath;

/**
 * The base class to be used by any rendering engines.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractMarkupRenderingEngine implements Renderable
{
    /**
     * Static rendering mode.
     *
     * @var integer
     */
    const CONTEXT_STATIC = 0;

    /**
     * Context-aware rendering.
     *
     * @var integer
     */
    const CONTEXT_AWARE = 1;

    /**
     * Template oriented rendering.
     *
     * @var integer
     */
    const TEMPLATE_ORIENTED = 2;

    /**
     * Ignore xml:base constraints.
     *
     * @var integer
     */
    const XMLBASE_IGNORE = 3;

    /**
     * Keep xml:base in final rendering,
     * but do not process them.
     *
     * @var integer
     */
    const XMLBASE_KEEP = 4;

    /**
     * Process all URL resolutions by taking
     * xml:base into account. xml:base values
     * will not be kept into the final rendering.
     *
     * @var integer
     */
    const XMLBASE_PROCESS = 5;

    /**
     * Stylesheet components are rendered at the same place
     * they appear in the content model to be rendered.
     *
     * @var integer
     */
    const STYLESHEET_INLINE = 6;

    /**
     * Stylesheet components are rendered separately and pushed into
     * a specific place.
     *
     * @var integer
     */
    const STYLESHEET_SEPARATE = 7;
    
    /**
     * QTI specific CSS classes will be ones related to
     * concrete QTI classes from the information model only.
     * 
     * @var integer
     */
    const CSSCLASS_CONCRETE = 8;
    
    /**
     * QTI specific CSS classes will be corresponding to the
     * whole QTI information model class hierarchy.
     * 
     * @var integer
     */
    const CSSCLASS_ABSTRACT = 9;

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
     * The stack where rendered components in other
     * constitution are stored for a later use.
     *
     * @var SplStack
     */
    private $renderingStack;

    /**
     * The stack where encountered xml:base values
     * will be stored.
     *
     * @var SplStack
     */
    private $xmlBaseStack;

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
    */
    private $choiceShowHidePolicy = AbstractMarkupRenderingEngine::CONTEXT_STATIC;

    /**
     * The Feedback rendering policy.
     *
     * @var integer
     */
    private $feedbackShowHidePolicy = AbstractMarkupRenderingEngine::CONTEXT_STATIC;

    /**
     * The View rendering policy.
     *
     * @var integer
     */
    private $viewPolicy = AbstractMarkupRenderingEngine::CONTEXT_STATIC;

    /**
     * The policy to adopt while dealing with printed variables.
     *
     * @var integer
     */
    private $printedVariablePolicy = AbstractMarkupRenderingEngine::CONTEXT_STATIC;
    
    /**
     * The policy to adopt while dealing with shuffling.
     * 
     * @var integer
     */
    private $shufflingPolicy = AbstractMarkupRenderingEngine::CONTEXT_STATIC;

    /**
     * The policy to adopt to deal with xml:base values.
     *
     * @var integer
     */
    private $xmlBasePolicy = AbstractMarkupRenderingEngine::XMLBASE_IGNORE;

    /**
     * The policy to adopt while dealing with QTI stylesheet components.
     *
     * @var integer
     */
    private $stylesheetPolicy = AbstractMarkupRenderingEngine::STYLESHEET_INLINE;
    
    /**
     * The policy to adopt while dealing with QTI related CSS classes.
     * 
     * @var integer
     */
    private $cssClassPolicy = AbstractMarkupRenderingEngine::CSSCLASS_CONCRETE;

    /**
     * The variable name to be used as the QTI AssessmentTest/AssessmentItem
     * State when the feedback policy is set to TEMPLATE_ORIENTED.
     *
     * @var string
     */
    private $stateName = 'qtismState';

    /**
     * The variable name to be used as the QTI views available when
     * views policy is set to TEMPLATE_ORIENTED.
     *
     * @var string
     */
    private $viewsName = 'qtismViews';

    /**
     * The URL to be used in place of the root component's xml:base value.
     *
     * @var string
     */
    private $rootBase = '';

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
     * The current interaction at rendering time.
     * 
     * @var \qtism\data\content\interactions\Interaction
     */
    private $currentInteraction = null;
    
    private $choiceCounter = 0;
    
    private $interactionCounter = 0;

    /**
     * The DOM fragment to be generated during rendering. If the current
     * stylesheet policy is SEPARATE, rendered stylesheet components will be
     * pushed into it.
     *
     * @var DOMDocumentFragment
     */
    private $stylesheets;

    /**
     * The document to be generated during the rendering.
     *
     * @var DOMDocument
     */
    private $document;

    /**
     * Create a new AbstractRenderingObject.
     *
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get the Stack of Component objects that to be still explored.
     *
     * @return SplStack
     */
    protected function getExploration()
    {
        return $this->exploration;
    }

    /**
     * Set the Stack of Component objects that have to be still explored.
     *
     * @param SplStack $exploration
     */
    protected function setExploration(SplStack $exploration)
    {
        $this->exploration = $exploration;
    }

    /**
     * Set the array used to 'tag' components in order to know
     * whether or not they are already explored.
     *
     * @return array
     */
    protected function getExplorationMarker()
    {
        return $this->explorationMarker;
    }

    /**
     * Set the array used to 'tag' components in order to know whether
     * or not they are already explored.
     *
     * @param array $explorationMarker
     */
    protected function setExplorationMarker(array $explorationMarker)
    {
        $this->explorationMarker = $explorationMarker;
    }

    /**
     * Get the currently explored component.
     *
     * @return QtiComponent
     */
    protected function getExploredComponent()
    {
        return $this->exploredComponent;
    }

    /**
     * Set the currently explored Component object.
     *
     * @param QtiComponent $component
     */
    protected function setExploredComponent(QtiComponent $component = null)
    {
        $this->exploredComponent = $component;
    }

    /**
     * Set the last rendering.
     *
     * @param mixed $rendering
     */
    protected function setLastRendering($rendering)
    {
        $this->lastRendering = $rendering;
    }

    /**
     * Get the last rendering.
     *
     * @return mixed
     */
    protected function getLastRendering()
    {
        return $this->lastRendering;
    }
    
    /**
     * Set the current interaction at rendering time.
     * 
     * @param \qtism\data\content\interactions\Interaction $interaction
     */
    protected function setCurrentInteraction(Interaction $interaction = null)
    {
        $this->currentInteraction = $interaction;
    }
    
    /**
     * Get the current interaction at rendering time.
     * 
     * @return \qtism\data\content\interactions\Interaction
     */
    protected function getCurrentInteraction()
    {
        return $this->currentInteraction;
    }

    public function render($component, $base = '')
    {
        // Reset the engine to its initial state.
        $this->reset();

        // Put the root $component on the stack.
        if ($this->mustIgnoreComponent($component) === false) {
            $this->getExploration()->push($component);
        }
        
        // Number of Interaction objects met during
        // the descending phase.
        $this->choiceCounter = 0;

        // Number of components which where
        // already met during the descending phase.
        $componentEncountered = 0;

        while (count($this->getExploration()) > 0) {
            $this->setExploredComponent($this->getExploration()->pop());

            // Component is final or not?
            $final = $this->isFinal();

            // Component is explored or not?
            $explored = $this->isExplored();

            if ($final === false && $explored === false) {

                // Hierarchical node: 1st pass (descending phase).
                $this->registerXmlBase(($componentEncountered === 0) ? ($this->hasRootBase() ? $this->getRootBase() : '') : '');
                $this->markAsExplored($this->getExploredComponent());
                $this->getExploration()->push($this->getExploredComponent());

                foreach ($this->getNextExploration() as $toExplore) {
                    // Maybe the component must be ignored?
                    if ($this->mustIgnoreComponent($toExplore) === false) {
                        $this->getExploration()->push($toExplore);
                    }
                }
                
                // If this is an interaction, just tag it as the current one.
                if ($this->getExploredComponent() instanceof Interaction) {
                    $this->setCurrentInteraction($this->getExploredComponent());
                    $this->choiceCounter = 0;
                }
                
                $componentEncountered++;
                
            } elseif ($final === false && $explored === true) {
                // Hierarchical node: 2nd pass.
                $this->processNode($this->resolveXmlBase());
                
                if ($this->getExploredComponent() instanceof Choice) {
                    $this->choiceCounter++;
                }
                
                if ($this->getExploredComponent() instanceof Interaction) {
                    $this->interactionCounter++;
                }

                if ($this->getExploredComponent() === $component) {
                    // End of the rendering.
                    break;
                }
            } else {
                // Leaf node.
                $this->registerXmlBase();
                $this->processNode($this->resolveXmlBase());

                if ($this->getExploredComponent() === $component) {
                    // End of the rendering (leaf node is actually a lone root).
                    break;
                }
            }
        }

        $finalRendering = $this->createFinalRendering($component);

        return $finalRendering;
    }

    /**
     * Whether or not the currently explored Component object
     * is a final leaf of the tree structured explored hierarchy.
     *
     * @return boolean
     */
    protected function isFinal()
    {
        return count($this->getNextExploration()) === 0;
    }

    /**
     * Get the children components of the currently explored component
     * for future exploration.
     *
     * @return QtiComponentCollection The children Component object of the currently explored Component object.
     */
    protected function getNextExploration()
    {
        return new QtiComponentCollection(array_reverse($this->getExploredComponent()->getComponents()->getArrayCopy()));
    }

    /**
     * Wether or not the currently explored component has been already explored.
     *
     * @return boolean
     */
    protected function isExplored()
    {
        return in_array($this->getExploredComponent(), $this->getExplorationMarker(), true);
    }

    /**
     *
     * @param QtiComponent $component
     */
    protected function markAsExplored(QtiComponent $component)
    {
        $marker = $this->getExplorationMarker();
        $marker[] = $component;
        $this->setExplorationMarker($marker);
    }

    /**
     * Create the final rendering of the rendered $component as it must be rendered by the final
     * implementation.
     *
     * @return mixed
     */
    protected function createFinalRendering(QtiComponent $component)
    {
        $dom = $this->getDocument();
        if (($last = $this->getLastRendering()) !== null) {
            $dom->appendChild($last);
        }

        // If we are rendering an item, let's try to find some Math to
        // for template variable mapping. 
        if ($component instanceof AssessmentItem && count($templateDeclarations = $component->getTemplateDeclarations()) > 0) {
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('m', 'http://www.w3.org/1998/Math/MathML');
            $maths = $xpath->query('//m:math|//math');
            
            if ($maths->length > 0) {
                $printedVariableRenderer = new PrintedVariableRenderer($this);
                
                foreach ($templateDeclarations as $templateDeclaration) {
                    if ($templateDeclaration->isMathVariable() === true) {
                        $templateIdentifier = $templateDeclaration->getIdentifier();
                        
                        foreach ($maths as $math) {
                            // Find <mi> and <ci> elements.
                            foreach ($xpath->query(".//m:mi[text() = '${templateIdentifier}']|.//m:ci[text() = '${templateIdentifier}']|.//mi[text() = '${templateIdentifier}']|.//ci[text() = '${templateIdentifier}']", $math) as $mathElement) {
                                $localElementName = ($mathElement->localName === 'mi') ? 'mn' : 'cn';
                                $newMathElement = $mathElement->ownerDocument->createElement($localElementName);
                                $printedVariable = new PrintedVariable($templateIdentifier);
                                $printedVariableFragment = $printedVariableRenderer->render($printedVariable);
                                
                                if ($this->getPrintedVariablePolicy() === self::CONTEXT_STATIC) {
                                    foreach ($printedVariableFragment->childNodes as $node) {
                                        $newMathElement->appendChild($node);
                                        $mathElement->parentNode->replaceChild($newMathElement, $mathElement);
                                    }
                                } else if ($this->getPrintedVariablePolicy() === self::CONTEXT_AWARE) {
                                    $newMathElement->appendChild($newMathElement->ownerDocument->createTextNode($printedVariableFragment->firstChild->nodeValue));
                                    $mathElement->parentNode->replaceChild($newMathElement, $mathElement);
                                } else {
                                    foreach ($printedVariableFragment->firstChild->childNodes as $node) {
                                        $newMathElement->appendChild($node);
                                        $mathElement->parentNode->replaceChild($newMathElement, $mathElement);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $dom;
    }

    /**
     * Process the current node (Ascending phase).
     *
     * @param string $base the value of xml:base for the node to be processed.
     * @throws RenderingException If an error occurs while processing the node.
     */
    protected function processNode($base = '')
    {
        $component = $this->getExploredComponent();
        $renderer = $this->getRenderer($component);
        $rendering = $renderer->render($component, $base);

        if ($this->mustTemplateFeedbackComponent($component) === true) {
            $this->templateFeedbackComponent($component, $rendering);
        }

        if ($this->mustTemplateRubricBlockComponent($component) === true) {
            $this->templateRubricBlockComponent($component, $rendering);
        }
        
        if ($this->mustTemplateChoiceComponent($component) === true) {
            $this->templateChoiceComponent($component, $rendering);
        }
        
        if ($this->mustIncludeChoiceComponent($component) === true) {
            $this->includeChoiceComponent($component, $rendering);
        }

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
    protected function mustIgnoreComponent(QtiComponent $component)
    {
        // In the list of QTI class names to be ignored?
        if (in_array($component->getQtiClassName(), $this->getIgnoreClasses()) === true) {
            return true;
        }
        // Context Aware + FeedbackElement OR Context Aware + Choice Templating.
        elseif ((self::isFeedback($component) && $this->getFeedbackShowHidePolicy() === AbstractMarkupRenderingEngine::CONTEXT_AWARE) || ($component instanceof Choice && $component->hasTemplateIdentifier() === true && $this->getChoiceShowHidePolicy() === AbstractMarkupRenderingEngine::CONTEXT_AWARE)) {
            $matches = $this->identifierMatches($component);
            $showHide = $component->getShowHide();

            return ($showHide === ShowHide::SHOW) ? !$matches : $matches;
        }
        // Context Aware + RubricBlock
        elseif ($this->getViewPolicy() === AbstractMarkupRenderingEngine::CONTEXT_AWARE && $component instanceof RubricBlock) {
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
        } else {
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
    protected function identifierMatches(QtiComponent $component)
    {
        $variableIdentifier = ($component instanceof FeedbackElement || $component instanceof ModalFeedback) ? $component->getOutcomeIdentifier() : $component->getTemplateIdentifier();
        $identifier = new QtiIdentifier($component->getIdentifier());
        $showHide = $component->getShowHide();
        $state = $this->getState();
        
        $matches = false;
        
        if (($val = $state[$variableIdentifier]) !== null) {
            if ($val instanceof QtiScalar) {
                $matches = $val->equals($identifier);
            } elseif ($val instanceof Container) {
                $matches = $val->contains($identifier);
            }
        }
        
        return $matches;
    }

    /**
     * Set the renderers array.
     *
     * @param array $renderers
     */
    protected function setRenderers(array $renderers)
    {
        $this->renderers = $renderers;
    }

    /**
     * Get the renderers array.
     *
     * @return array
     */
    protected function getRenderers()
    {
        return $this->renderers;
    }

    /**
     * Set the array containing the QTI class names
     * to be ignored for rendering.
     *
     * @param array $ignoreClasses
     */
    protected function setIgnoreClasses(array $ignoreClasses)
    {
        $this->ignoreClasses = $ignoreClasses;
    }

    public function getIgnoreClasses()
    {
        return $this->ignoreClasses;
    }

    public function ignoreQtiClasses($classes)
    {
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
     * @param \qtism\runtime\rendering\markup\AbstractMarkupRenderer $renderer An AbstractRenderer object.
     * @param string $ns
     */
    public function registerRenderer($qtiClassName, AbstractMarkupRenderer $renderer, $ns = 'qtism')
    {
        $renderer->setRenderingEngine($this);
        $renderers = $this->getRenderers();
        $renderers[$ns][$qtiClassName] = $renderer;
        $this->setRenderers($renderers);
    }

    /**
     * Get the AbstractRenderer implementation which is appropriate to render the given
     * QtiComponent $component.
     *
     * @param QtiComponent $component A QtiComponent object you want to get the appropriate AbstractRenderer implementation.
     * @throws RenderingException If no implementation of AbstractRenderer is registered for $component.
     * @return \qtism\runtime\rendering\markup\AbstractMarkupRenderer The AbstractRenderer implementation to render $component.
     */
    public function getRenderer(QtiComponent $component)
    {
        $renderers = $this->getRenderers();
        $className = $component->getQtiClassName();

        if ($component instanceof ExternalQtiComponent && isset($renderers[$component->getTargetNamespace()][$className])) {
            return $renderers[$component->getTargetNamespace()][$className];
        } elseif (isset($renderers['qtism'][$className]) === true) {
            return $renderers['qtism'][$className];
        } else {
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
    protected function getRenderingStack()
    {
        return $this->renderingStack;
    }

    /**
     * Set the stack of rendered components stored
     * for a later use by AbstractRenderer objects.
     *
     * @param SplStack $renderingStack
     */
    protected function setRenderingStack(SplStack $renderingStack)
    {
        $this->renderingStack = $renderingStack;
    }

    /**
     * Set the stack where encountered xml:base values will
     * be stored.
     *
     * @param SplStack $xmlBaseStack
     */
    protected function setXmlBaseStack(SplStack $xmlBaseStack)
    {
        $this->xmlBaseStack = $xmlBaseStack;
    }

    /**
     * Get the stack where encountered xml:base values will be
     * stored.
     *
     * @return SplStack
     */
    protected function getXmlBaseStack()
    {
        return $this->xmlBaseStack;
    }

    /**
     * Store a rendered component as a rendering for a later use
     * by AbstractRenderer objects.
     *
     * @param QtiComponent $component The $component from which the rendering was made.
     * @param mixed $rendering A component rendered in another format.
     */
    public function storeRendering(QtiComponent $component, $rendering)
    {
        $this->getRenderingStack()->push(array($component, $rendering));
    }

    /**
     * Get the renderings related to the children of $component.
     *
     * @param QtiComponent $component A QtiComponent object to be rendered.
     * @return array
     */
    public function getChildrenRenderings(QtiComponent $component)
    {
        $returnValue = array();

        foreach (array_reverse($component->getComponents()->getArrayCopy()) as $c) {

            if (count($this->getRenderingStack()) > 0) {
                list($renderedComponent, $rendering) = $this->getRenderingStack()->pop();

                if ($c === $renderedComponent) {
                    array_unshift($returnValue, $rendering);
                } else {
                    // repush...
                    $this->storeRendering($renderedComponent, $rendering);
                }
            }
        }

        return $returnValue;
    }

    /**
     * Reset the engine to its initial state, in order
     * to be ready for reuse i.e. render a new component. However,
     * configuration such as policies are kept intact.
     */
    public function reset()
    {
        $this->choiceCounter = 0;
        $this->setExploration(new SplStack());
        $this->setExplorationMarker(array());
        $this->setLastRendering(null);
        $this->setRenderingStack(new SplStack());
        $this->setXmlBaseStack(new SplStack());
        $this->setDocument(new DOMDocument('1.0', 'UTF-8'));
        $this->setStylesheets($this->getDocument()->createDocumentFragment());
        $this->setCurrentInteraction(null);
    }

    /**
     * Register the value of xml:base of the currently explored component
     * into the xmlBaseStack.
     *
     * @param string $substitution If set, the registered xml:base value will be the value of the argument instead of the currently explored component's xml:base value.
     */
    protected function registerXmlBase($substitution = '')
    {
        $c = $this->getExploredComponent();
        $toPush = $substitution;
        
        if ($c instanceof Flow) {
            if (empty($substitution) === true) {
                $toPush = $c->getXmlBase();
            }
        }
        
        $this->getXmlBaseStack()->push($toPush);
    }

    /**
     * Resolve what is the base URL to be used for the currently explored component.
     *
     * @return string A URL or the empty string ('') if no base URL could be resolved.
     */
    protected function resolveXmlBase()
    {
        $stack = $this->getXmlBaseStack();
        $stack->rewind();

        $resolvedBase = '';

        while ($stack->valid() === true) {

            if (($currentBase = $stack->current()) !== '') {
                if ($resolvedBase === '') {
                    $resolvedBase = $currentBase;
                } else {
                    $resolvedBase = Url::rtrim($currentBase) . '/' . Url::ltrim($resolvedBase);
                }
            }

            $stack->next();
        }

        if ($stack->count() > 0) {
            $stack->pop();
        }

        return $resolvedBase;
    }

    /**
     * Whether or not a given component (expected to be a feedback) must be templated. A component
     * is considered to be templatable if:
     *
     * * it is an instance of FeedbackElement or ModalFeedback
     * * the current policy for feedback elements is TEMPLATE_ORIENTED
     *
     * @param QtiComponent $component
     * @return boolean
     */
    protected function mustTemplateFeedbackComponent(QtiComponent $component)
    {
        return (self::isFeedback($component) && $this->getFeedbackShowHidePolicy() === AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED);
    }

    /**
     * Whether or not a given component (expected to be a rubricBlock) must be templated. A component
     * is considered to be templatable if:
     *
     * * it is an instance of RubricBlock
     * * the current policy for views is TEMPLATE_ORIENTED
     *
     * @param QtiComponent $component
     * @return boolean
     */
    protected function mustTemplateRubricBlockComponent(QtiComponent $component)
    {
        return (self::isRubricBlock($component) && $this->getViewPolicy() === AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED);
    }
    
    /**
     * Whether or not a given component (expected to be a choice) must be templated. A component is
     * considered to be templatable if:
     * 
     * * it is an instance of Choice
     * * the current policy for choice show/hide is TEMPLATE_ORIENTED
     *
     * @param QtiComponent $component
     * @return boolean
     */
    protected function mustTemplateChoiceComponent(QtiComponent $component)
    {
        return self::isChoice($component) && $this->getChoiceShowHidePolicy() === self::TEMPLATE_ORIENTED && $component->hasTemplateIdentifier();
    }
    
    protected function mustIncludeChoiceComponent(QtiComponent $component)
    {
        $shufflables = array(
            'choiceInteraction',
            'orderInteraction',
            'associateInteraction',
            'matchInteraction',
            'gapMatchInteraction',
            'inlineChoiceInteraction'                
        );
        $interaction = $this->getCurrentInteraction();
        
        return self::isChoice($component) && !$component instanceof Gap && $component->isFixed() === false && $this->getShufflingPolicy() === self::TEMPLATE_ORIENTED && in_array($interaction->getQtiClassName(), $shufflables) === true && $interaction->mustShuffle() === true;
    }

    /**
     * Whether or not a given component is an instance of
     * FeedbackElement or ModalFeedback.
     *
     * @param QtiComponent $component A QtiComponent object.
     * @return boolean
     */
    protected static function isFeedback(QtiComponent $component)
    {
        return ($component instanceof FeedbackElement || $component instanceof ModalFeedback);
    }
    
    /**
     * Whether or not a given component is an instance of Choice.
     * 
     * @param QtiComponent $component A QtiComponent object.
     * @return boolean
     */
    protected static function isChoice(QtiComponent $component)
    {
        return $component instanceof Choice;
    }

    /**
     * Whether or not a given component is an instance of
     * RubricBlock.
     *
     * @param QtiComponent $component A QtiComponent object.
     * @return boolean
     */
    protected static function isRubricBlock(QtiComponent $component)
    {
        return ($component instanceof RubricBlock);
    }

    /**
     * Contains the logic of templating a QTI feedback (feedbackElement, modalFeedback).
     *
     * @param QtiComponent $component The QtiComponent being rendered.
     * @param DOMDocumentFragment $rendering The rendering corresponding to $component.
     * @throws RenderingException If $component is not an instance of FeedbackElement nor ModalFeedback.
     */
    protected function templateFeedbackComponent(QtiComponent $component, DOMDocumentFragment $rendering)
    {
        if (self::isFeedback($component) === false) {
            $msg = "Cannot template a component which is not an instance of FeedbackElement nor ModalFeedback.";
            throw new RenderingException($msg, RenderingException::RUNTIME);
        }

        $operator = ($component->getShowHide() === ShowHide::SHOW) ? '' : '!';
        $val = '$' . $this->getStateName() . "['" . $component->getOutcomeIdentifier() . "']";
        $identifier = $component->getIdentifier();
        $identifierType = 'qtism\\common\\datatypes\\QtiIdentifier';
        $scalarType = 'qtism\\common\\datatypes\\QtiScalar';
        $containerType = 'qtism\\common\\collections\\Container';
        $scalarCheck = "${val} instanceof ${identifierType} && ${val}->equals(new ${identifierType}('${identifier}'))";
        $containerCheck = "${val} instanceof ${containerType} && ${val}->contains(new ${identifierType}('${identifier}'))";
        $valCheck = "(${scalarCheck} || ${containerCheck})";

        $ifStmt = " qtism-if (${operator}(${val} !== null && ${valCheck})): ";
        $endifStmt = " qtism-endif ";

        $ifStmtCmt = $rendering->ownerDocument->createComment($ifStmt);
        $endifStmtCmt = $rendering->ownerDocument->createComment($endifStmt);

        $rendering->insertBefore($ifStmtCmt, $rendering->firstChild);
        $rendering->appendChild($endifStmtCmt);
    }

    /**
     * Contains the logic of templating a QTI rubricBlock (RubricBlock).
     *
     * @param QtiComponent $component The QtiComponent being rendered.
     * @param DOMDocumentFragment $rendering The rendering corresponding to $component.
     * @throws RenderingException If $component is not an instance of RubricBlock.
     */
    protected function templateRubricBlockComponent(QtiComponent $component, DOMDocumentFragment $rendering)
    {
        if (self::isRubricBlock($component) === false) {
            $msg = "Cannot template a component which is not an instance of RubricBlock.";
            throw new RenderingException($msg, RenderingException::RUNTIME);
        }

        $viewsName = '$' . $this->getViewsName();
        $views = $component->getViews();
        $conds = array();

        foreach ($component->getViews() as $v) {
            $conds[] = "in_array(${v}, ${viewsName})";
        }

        $conds = (count($views) > 1) ? implode(' || ', $conds) : $conds[0];
        $ifStmt = " qtism-if (${conds}): ";
        $endifStmt = " qtism-endif ";

        $ifStmtCmt = $rendering->ownerDocument->createComment($ifStmt);
        $endifStmtCmt = $rendering->ownerDocument->createComment($endifStmt);

        $rendering->insertBefore($ifStmtCmt, $rendering->firstChild);
        $rendering->appendChild($endifStmtCmt);
    }
    
    /**
     * Contains the logic of templating a QTI choice (Choice).
     * 
     * @param QtiComponent $component The QtiComponent object being rendered
     * @param DOMDocumentFragment $rendering The rendering corresponding to $component
     * @throws RenderingException If $component is not an instance of Choice.
     */
    protected function templateChoiceComponent(QtiComponent $component, DOMDocumentFragment $rendering)
    {
        if (self::isChoice($component) === false) {
            $msg = "Cannot template a component which is not an instance of Choice.";
            throw new RenderingException($msg, RenderingException::RUNTIME);
        }
        
        $operator = ($component->getShowHide() === ShowHide::SHOW) ? '' : '!';
        $val = '$' . $this->getStateName() . "['" . $component->getTemplateIdentifier() . "']";
        $identifier = $component->getIdentifier();
        $identifierType = 'qtism\\common\\datatypes\\QtiIdentifier';
        $scalarType = 'qtism\\common\\datatypes\\QtiScalar';
        $containerType = 'qtism\\common\\collections\\Container';
        $scalarCheck = "${val} instanceof ${identifierType} && ${val}->equals(new ${identifierType}('${identifier}'))";
        $containerCheck = "${val} instanceof ${containerType} && ${val}->contains(new ${identifierType}('${identifier}'))";
        $valCheck = "(${scalarCheck} || ${containerCheck})";
        
        $ifStmt = " qtism-if (${operator}(${val} !== null && ${valCheck})): ";
        $endifStmt = " qtism-endif ";
        
        $ifStmtCmt = $rendering->ownerDocument->createComment($ifStmt);
        $endifStmtCmt = $rendering->ownerDocument->createComment($endifStmt);
        
        $rendering->insertBefore($ifStmtCmt, $rendering->firstChild);
        $rendering->appendChild($endifStmtCmt);
    }
    
    protected function includeChoiceComponent(QtiComponent $component, DOMDocumentFragment $rendering)
    {
        $choiceIndex = $this->choiceCounter;
        $choiceIdentifier = PhpUtils::doubleQuotedPhpString($component->getIdentifier());
        $interactionIndex = $this->interactionCounter;
        $stateName = $this->getStateName();
        
        $includeStmtCmt = $rendering->ownerDocument->createComment(' qtism-include($' . "${stateName}, ${interactionIndex}, ${choiceIdentifier}, ${choiceIndex}): ");
        $endIncludeStmtCmt = $rendering->ownerDocument->createComment(' qtism-endinclude ');
        $rendering->insertBefore($includeStmtCmt, $rendering->firstChild);
        $rendering->appendChild($endIncludeStmtCmt);
    }

    /**
     * Set the policy ruling the way qti:choice components are managed while rendering.
     *
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:choice is described in QTI-XML. The component will never be discarded from rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from rendering depending on the value of the variable referenced by the choice:templateIdentifier attribute and the value of the choice:showHide attribute.
     * * IN TEMPLATE_ORIENTED mode, the component will be rendered with template oriented conditional statements.
     *
     * @param integer $policy AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE.
     * @see http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10271 The qti:choice class.
     */
    public function setChoiceShowHidePolicy($policy)
    {
        $this->choiceShowHidePolicy = $policy;
    }

    /**
     * Get the policy ruling the way qti:hoice components are managed while rendering.
     *
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:choice is described in QTI-XML. The component will never be discarded from rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from rendering depending on the value of the variable referenced by the choice:templateIdentifier attribute and the value of the choice:showHide attribute.
     * * IN TEMPLATE_ORIENTED mode, the component will be rendered with template oriented conditional statements.
     *
     * @return integer AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE.
     * @see http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10271 The qti:choice class.
     */
    public function getChoiceShowHidePolicy()
    {
        return $this->choiceShowHidePolicy;
    }

    /**
     * Set the policy ruling the way qti:feedbackElement are managed while rendering.
     *
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:feedbackElement is defined. It will never be discarded from the final rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from the final rendering depending on the value of the variable referenced by the qti:feedbackElement.
     * * In TEMPLATE_ORIENTED mode, the component will be always rendered and enclosed in template tags, that can be processed later on depending on the needs.
     *
     * @param integer $policy AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE or AbstractMarkupRenderingEngine::TEMPLATE_ORIENTED.
     */
    public function setFeedbackShowHidePolicy($policy)
    {
        $this->feedbackShowHidePolicy = $policy;
    }

    /**
     * Get the policy ruling the way qti:feedbackElement and qti:modalFeedback are managed while rendering.
     *
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:feedbackElement is defined. It will never be discarded from the final rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from the final rendering depending on the value of the variable referenced by the qti:feedbackElement.
     *
     * @return integer AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE.
     */
    public function getFeedbackShowHidePolicy()
    {
        return $this->feedbackShowHidePolicy;
    }

    /**
     * Set the policy ruling the way QTI components with a qti:view attribute are managed during the rendering phase.
     *
     * * In CONTEXT_STATIC mode, the qti-view-candidate|qti-view-auhor|qti-view-proctor|qti-view-tutor|qti-view-tutor|qti-view-testConstructor|qti-view-scorer CSS class will be simply added to the rendered elements.
     * * In CONTEXT_AWARE mode, CSS classes will be set up as in CONTEXT_STATIC mode, but views that do not match the view given by the client-code will be discarded from rendering.
     * * In TEMPLATE_ORIENTED mode, the component will be always rendered and enclosed in template tags, that can be processed later on depending on the needs.
     *
     * @param integer $policy AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE.
     */
    public function setViewPolicy($policy)
    {
        $this->viewPolicy = $policy;
    }

    /**
     * Set the policy ruling the way QTI components with a qti:view attribute are managed during the rendering phase.
     *
     * * In CONTEXT_STATIC mode, the qti-view-candidate|qti-view-auhor|qti-view-proctor|qti-view-tutor|qti-view-tutor|qti-view-testConstructor|qti-view-scorer CSS class will be simply added to the rendered elements depending on the value of the "view" attribute in the QTI-XML definition.
     * * In CONTEXT_AWARE mode, CSS classes will be set up as in CONTEXT_STATIC mode, but views that do not match the view given by the client-code will be discarded from rendering.
     * * In TEMPLATE_ORIENTED mode, the component will be always rendered and enclosed in template tags, that can be processed later on depending on the needs.
     *
     * @return integer AbstractMarkupRenderingEngine::CONTEXT_STATIC or AbstractMarkupRenderingEngine::CONTEXT_AWARE.
     */
    public function getViewPolicy()
    {
        return $this->viewPolicy;
    }

    /**
     * Set the policy ruling the way qti:printedVariable components are managed during the rendering phase.
     *
     * * In CONTEXT_STATIC mode, the printed variable will be generated in a static way. Data attributes will be used by the client-code to render the appropriate value.
     * * In CONTEXT_AWARE mode, the printed variable will be generated as in CONTEXT_STATIC mode, but the value to be displayed will be generated.
     * * In TEMPLATE_ORIENTED mode, the code to be processed to render the variable value will be enclosed into template tags, that can be processed later on.
     *
     * @param integer $printedVariablePolicy AbstractMarkup
     */
    public function setPrintedVariablePolicy($printedVariablePolicy)
    {
        $this->printedVariablePolicy = $printedVariablePolicy;
    }

    /**
     * Get the policy ruling the way qti:printedVariable components are managed during the rendering phase.
     *
     * * In CONTEXT_STATIC mode, the printed variable will be generated in a static way. Data attributes will be used by the client-code to render the appropriate value.
     * * In CONTEXT_AWARE mode, the printed variable will be generated as in CONTEXT_STATIC mode, but the value to be displayed will be generated.
     * * In TEMPLATE_ORIENTED mode, the code to be processed to render the variable value will be enclosed into template tags, that can be processed later on.
     *
     * @return integer
     */
    public function getPrintedVariablePolicy()
    {
        return $this->printedVariablePolicy;
    }
    
    public function setShufflingPolicy($shufflingPolicy)
    {
        $this->shufflingPolicy = $shufflingPolicy;
    }
    
    public function getShufflingPolicy()
    {
        return $this->shufflingPolicy;
    }

    /**
     * Set the policy to adopt while rendering regarding xml:base.
     *
     * * AbstractMarkupRenderingEngine::XMLBASE_IGNORE: Ignore xml:base constraints. The URIs in the final rendering will be the same as in the QTI model.
     * * AbstractMarkupRenderingEngine::XMLBASE_KEEP: Keep xml:base values into the rendering. The URIs in the final rendering will remain the same as in the QTI model.
     * * AbstractMarkupRenderingEngine::XMLBASE_PROCESS: Process URIs by taking xml:base values into account. URIs in the final rendering will reflect the constraints set by xml:base values.
     *
     * @param integer $xmlBasePolicy AbstractMarkupRenderingEngine::XMLBASE_IGNORE, AbstractMarkupRenderingEngine::XMLBASE_KEEP or AbstractMarkupRenderingEngine::XMLBASE_PROCESS.
     * @see http://www.w3.org/TR/xmlbase/#syntax W3C XML Base (Second Edition)
     */
    public function setXmlBasePolicy($xmlBasePolicy)
    {
        $this->xmlBasePolicy = $xmlBasePolicy;
    }

    /**
     * Get the policy to adopt while rendering regarding xml:base.
     *
     * * AbstractMarkupRenderingEngine::XMLBASE_IGNORE: Ignore xml:base constraints. The URIs in the final rendering will be the same as in the QTI model.
     * * AbstractMarkupRenderingEngine::XMLBASE_KEEP: Keep xml:base values into the rendering. The URIs in the final rendering will remain the same as in the QTI model.
     * * AbstractMarkupRenderingEngine::XMLBASE_PROCESS: Process URIs by taking xml:base values into account. URIs in the final rendering will reflect the constraints set by xml:base values.
     *
     * @return integer AbstractMarkupRenderingEngine::XMLBASE_IGNORE, AbstractMarkupRenderingEngine::XMLBASE_KEEP or AbstractMarkupRenderingEngine::XMLBASE_PROCESS.
     * @see http://www.w3.org/TR/xmlbase/#syntax W3C XML Base (Second Edition)
     */
    public function getXmlBasePolicy()
    {
        return $this->xmlBasePolicy;
    }

    /**
     * Set the policy to adopt while rendering QTI stylesheet components.
     *
     * * AbstractMarkupRenderingEngine::STYLESHEET_INLINE: Stylesheet components are rendered at the same place they appear in the content model to be rendered.
     * * AbstractMarkupRenderingEngine::STYLESHEET_SEPARATE: Stylesheet components are rendered separately from the rest of the model, and pushed into a specific place.
     *
     * @param integer $stylesheetPolicy AbstractMarkupRenderingEngine::STYLESHEET_INLINE or AbstractMarkupRenderingEngine::STYLESHEET_SEPARATE.
     */
    public function setStylesheetPolicy($stylesheetPolicy)
    {
        $this->stylesheetPolicy = $stylesheetPolicy;
    }

    /**
     * Get the policy to adopt while rendering QTI stylesheet components.
     *
     * * AbstractMarkupRenderingEngine::STYLESHEET_INLINE: Stylesheet components are rendered at the same place they appear in the content model to be rendered.
     * * AbstractMarkupRenderingEngine::STYLESHEET_SEPARATE: Stylesheet components are rendered separately from the rest of the model, and pushed into a specific place.
     *
     * @return integer AbstractMarkupRenderingEngine::STYLESHEET_INLINE or AbstractMarkupRenderingEngine::STYLESHEET_SEPARATE.
     */
    public function getStylesheetPolicy()
    {
        return $this->stylesheetPolicy;
    }
    
    /**
     * Set the policy to adopt while dealing with QTI related CSS classes.
     * 
     * * AbstractMarkupRenderingEngine::CSSCLASS_CONCRETE: Only the concrete QTI specific classes will be rendered.
     * * AbstractMarkupRenderingEngine::CSSCLASS_ABSTRACT: The whole hierarachy of QTI specific classes will be rendered.
     * 
     * @param integer $cssClassPolicy
     */
    public function setCssClassPolicy($cssClassPolicy)
    {
        $this->cssClassPolicy = $cssClassPolicy;
    }
    
    /**
     * Get the policy to adopt while dealing with QTI related CSS classes.
     * 
     * * AbstractMarkupRenderingEngine::CSSCLASS_CONCRETE: Only the concrete QTI specific classes will be rendered.
     * * AbstractMarkupRenderingEngine::CSSCLASS_ABSTRACT: The whole hierarachy of QTI specific classes will be rendered.
     * 
     * @return integer
     */
    public function getCssClassPolicy()
    {
        return $this->cssClassPolicy;
    }

    /**
     * Set the URL (Uniform Resource Locator) to use in place of the value
     * of the root component's xml:base value.
     *
     * @param string $rootBase A URL.
     */
    public function setRootBase($rootBase)
    {
        $this->rootBase = $rootBase;
    }

    /**
     * Get the URL (Uniform Resource Locator) to use in place of the value
     * of the root component's xml:base value.
     *
     * @return string A URL.
     */
    public function getRootBase()
    {
        return $this->rootBase;
    }

    /**
     * Set the variable name to be used as the QTI AssessmentTest/AssessmentItem
     * State when the feedback policy is set to TEMPLATE_ORIENTED.
     *
     * @param string $stateName A variable name (without the leading dollar sign ('$')).
     */
    public function setStateName($stateName)
    {
        $this->stateName = $stateName;
    }

    /**
     * Get the variable name to be used as the QTI AssessmentTest/AssessmentItem
     * State when the feedback policy is set to TEMPLATE_ORIENTED.
     *
     * @return string A variable name (without the leading dollar sign('$')).
     */
    public function getStateName()
    {
        return $this->stateName;
    }

    /**
     * Set the variable name to be used as the QTI views in use
     * when the views policy is TEMPLATE_ORIENTED.
     *
     * @param string $viewsName A variable name (without the leading dollar sign ('$')).
     */
    public function setViewsName($viewsName)
    {
        $this->viewsName = $viewsName;
    }

    /**
     * Get the variable name to be used as the QTI views in use
     * when the views policy is TEMPLATE_ORIENTED.
     *
     * @return string A variable name (without the leading dollar sign ('$')).
     */
    public function getViewsName()
    {
        return $this->viewsName;
    }

    /**
     * Whether or not a URL is defined in place of the value of the root
     * component's xml:base value.
     *
     * @return boolean
     */
    protected function hasRootBase()
    {
        return $this->getRootBase() !== '';
    }

    /**
     * Set the contextual qti:view(s) to be used in CONTEXT_AWARE mode.
     *
     * @param ViewCollection $views A collection of values from the View enumeration.
     */
    public function setViews(ViewCollection $views)
    {
        $this->views = $views;
    }

    /**
     * Get the contextual qti:view to be used in CONTEXT_AWARE mode.
     *
     * @return ViewCollection A collection of values from the View enumeration.
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set the State to be used as the context used in CONTEXT_AWARE mode.
     *
     * @param State $state A State object.
     */
    public function setState(State $state)
    {
        $this->state = $state;
    }

    /**
     * Get the State used in CONTEXT_AWARE mode.
     *
     * @return State A State object.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the DOMDocumentFragment object to be used to collect
     * rendered QTI stylesheet components when the stylesheet policy
     * is SEPARATE.
     *
     * @param DOMDocumentFragment $stylesheets A DOMDocumentFragment object.
     */
    protected function setStylesheets(DOMDocumentFragment $stylesheets)
    {
        $this->stylesheets = $stylesheets;
    }

    /**
     * Get the DOMDocumentFragment object to be used to collect
     * rendered QTI stylesheet components when the stylesheet policy is
     * SEPARATE.
     *
     * The rendered components will be set in the order they appear during
     * the rendering phase.
     *
     * The owner of the DOMDocumentFragment object is the one you get by calling
     * the XhtmlRenderingEngine::getDocument() method.
     *
     * @return DOMDocumentFragment A DOMDocumentFragment object.
     * @see XhtmlRenderingEngine::getDocument() The method to get the owner document of the DOMDocument fragment.
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * Set the document to be used for rendering.
     *
     * @param DOMDocument $document
     */
    public function setDocument(DOMDocument $document)
    {
        $this->document = $document;
    }

    /**
     * Get the document currently used for rendering.
     *
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }
}
