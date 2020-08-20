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

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use InvalidArgumentException;
use qtism\data\QtiComponent;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * The MarshallerFactory aims at giving the client code the ability to
 * create appropriate marshallers regarding a specific QtiComponent
 * or DOMElement.
 */
abstract class MarshallerFactory
{
    /**
     * An associative array where keys are QTI class names
     * and values are fully qualified marshaller PHP class names.
     *
     * @var array
     */
    private $mapping = [];

    /**
     * Get the associative array which represents the current QTI class <-> Marshaller
     * mapping.
     *
     * @return array An associative array where keys are QTI class names and values are fully qualified PHP class names.
     */
    public function &getMapping()
    {
        return $this->mapping;
    }

    /**
     * Whether or not element and attribute serialization must be Web Component friendly.
     *
     * @var bool
     */
    private $webComponentFriendly = false;

    /**
     * Create a new instance of MarshallerFactory.
     *
     */
    public function __construct()
    {
        $this->addMappingEntry('customInteraction', CustomInteractionMarshaller::class);
        $this->addMappingEntry('br', BrMarshaller::class);
        $this->addMappingEntry('hr', HrMarshaller::class);
        $this->addMappingEntry('responseCondition', ResponseConditionMarshaller::class);
        $this->addMappingEntry('responseProcessing', ResponseProcessingMarshaller::class);
        $this->addMappingEntry('anyN', AnyNMarshaller::class);
        $this->addMappingEntry('areaMapEntry', AreaMapEntryMarshaller::class);
        $this->addMappingEntry('areaMapping', AreaMappingMarshaller::class);
        $this->addMappingEntry('assessmentItem', AssessmentItemMarshaller::class);
        $this->addMappingEntry('assessmentSectionRef', AssessmentSectionRefMarshaller::class);
        $this->addMappingEntry('assessmentTest', AssessmentTestMarshaller::class);
        $this->addMappingEntry('associateInteraction', AssociateInteractionMarshaller::class);
        $this->addMappingEntry('blockquote', BlockquoteMarshaller::class);
        $this->addMappingEntry('correct', CorrectMarshaller::class);
        $this->addMappingEntry('dl', DlMarshaller::class);
        $this->addMappingEntry('drawingInteraction', DrawingInteractionMarshaller::class);
        $this->addMappingEntry('endAttemptInteraction', EndAttemptInteractionMarshaller::class);
        $this->addMappingEntry('equalRounded', EqualRoundedMarshaller::class);
        $this->addMappingEntry('exitResponse', ExitResponseMarshaller::class);
        $this->addMappingEntry('exitTest', ExitTestMarshaller::class);
        $this->addMappingEntry('fieldValue', FieldValueMarshaller::class);
        $this->addMappingEntry('hottextInteraction', HottextInteractionMarshaller::class);
        $this->addMappingEntry('inlineChoiceInteraction', InlineChoiceInteractionMarshaller::class);
        $this->addMappingEntry('gap', GapMarshaller::class);
        $this->addMappingEntry('gapMatchInteraction', GapMatchInteractionMarshaller::class);
        $this->addMappingEntry('graphicAssociateInteraction', GraphicAssociateInteractionMarshaller::class);
        $this->addMappingEntry('graphicGapMatchInteraction', GraphicGapMatchInteractionMarshaller::class);
        $this->addMappingEntry('graphicOrderInteraction', GraphicOrderInteractionMarshaller::class);
        $this->addMappingEntry('hotspotInteraction', HotspotInteractionMarshaller::class);
        $this->addMappingEntry('hottext', HottextMarshaller::class);
        $this->addMappingEntry('img', ImgMarshaller::class);
        $this->addMappingEntry('index', IndexMarshaller::class);
        $this->addMappingEntry('infoControl', InfoControlMarshaller::class);
        $this->addMappingEntry('inlineChoice', InlineChoiceMarshaller::class);
        $this->addMappingEntry('inside', InsideMarshaller::class);
        $this->addMappingEntry('interpolationTableEntry', InterpolationTableEntryMarshaller::class);
        $this->addMappingEntry('interpolationTable', InterpolationTableMarshaller::class);
        $this->addMappingEntry('itemBody', ItemBodyMarshaller::class);
        $this->addMappingEntry('itemSubset', ItemSubsetMarshaller::class);
        $this->addMappingEntry('li', LiMarshaller::class);
        $this->addMappingEntry('mapResponse', MapResponseMarshaller::class);
        $this->addMappingEntry('mapResponsePoint', MapResponsePointMarshaller::class);
        $this->addMappingEntry('matchInteraction', MatchInteractionMarshaller::class);
        $this->addMappingEntry('mathConstant', MathConstantMarshaller::class);
        $this->addMappingEntry('math', MathMarshaller::class);
        $this->addMappingEntry('mediaInteraction', MediaInteractionMarshaller::class);
        $this->addMappingEntry('modalFeedback', ModalFeedbackMarshaller::class);
        $this->addMappingEntry('numberCorrect', NumberCorrectMarshaller::class);
        $this->addMappingEntry('numberIncorrect', NumberIncorrectMarshaller::class);
        $this->addMappingEntry('numberPresented', NumberPresentedMarshaller::class);
        $this->addMappingEntry('numberResponded', NumberRespondedMarshaller::class);
        $this->addMappingEntry('numberSelected', NumberSelectedMarshaller::class);
        $this->addMappingEntry('param', ParamMarshaller::class);
        $this->addMappingEntry('lookupOutcomeValue', LookupOutcomeValueMarshaller::class);
        $this->addMappingEntry('mathOperator', MathOperatorMarshaller::class);
        $this->addMappingEntry('mapEntry', MapEntryMarshaller::class);
        $this->addMappingEntry('matchTableEntry', MatchTableEntryMarshaller::class);
        $this->addMappingEntry('matchTable', MatchTableMarshaller::class);
        $this->addMappingEntry('ordering', OrderingMarshaller::class);
        $this->addMappingEntry('outcomeCondition', OutcomeConditionMarshaller::class);
        $this->addMappingEntry('matchTable', MatchTableMarshaller::class);
        $this->addMappingEntry('outcomeMaximum', OutcomeMaximumMarshaller::class);
        $this->addMappingEntry('outcomeMinimum', OutcomeMinimumMarshaller::class);
        $this->addMappingEntry('outcomeProcessing', OutcomeProcessingMarshaller::class);
        $this->addMappingEntry('patternMatch', PatternMatchMarshaller::class);
        $this->addMappingEntry('positionObjectInteraction', PositionObjectInteractionMarshaller::class);
        $this->addMappingEntry('positionObjectStage', PositionObjectStageMarshaller::class);
        $this->addMappingEntry('randomFloat', RandomFloatMarshaller::class);
        $this->addMappingEntry('repeat', RepeatMarshaller::class);
        $this->addMappingEntry('mapping', MappingMarshaller::class);
        $this->addMappingEntry('correctResponse', CorrectResponseMarshaller::class);
        $this->addMappingEntry('matchTable', MatchTableMarshaller::class);
        $this->addMappingEntry('itemSessionControl', ItemSessionControlMarshaller::class);
        $this->addMappingEntry('responseDeclaration', ResponseDeclarationMarshaller::class);
        $this->addMappingEntry('outcomeDeclaration', OutcomeDeclarationMarshaller::class);
        $this->addMappingEntry('roundTo', RoundToMarshaller::class);
        $this->addMappingEntry('rubricBlock', RubricBlockMarshaller::class);
        $this->addMappingEntry('object', ObjectMarshaller::class);
        $this->addMappingEntry('col', ColMarshaller::class);
        $this->addMappingEntry('colgroup', ColgroupMarshaller::class);
        $this->addMappingEntry('equal', EqualMarshaller::class);
        $this->addMappingEntry('sectionPart', SectionPartMarshaller::class);
        $this->addMappingEntry('selectPointInteraction', SelectPointInteractionMarshaller::class);
        $this->addMappingEntry('setOutcomeValue', SetOutcomeValueMarshaller::class);
        $this->addMappingEntry('simpleAssociableChoice', SimpleAssociableChoiceMarshaller::class);
        $this->addMappingEntry('caption', CaptionMarshaller::class);
        $this->addMappingEntry('tr', TrMarshaller::class);
        $this->addMappingEntry('branchRule', BranchRuleMarshaller::class);
        $this->addMappingEntry('simpleMatchSet', SimpleMatchSetMarshaller::class);
        $this->addMappingEntry('sliderInteraction', SliderInteractionMarshaller::class);
        $this->addMappingEntry('statsOperator', StatsOperatorMarshaller::class);
        $this->addMappingEntry('stringMatch', StringMatchMarshaller::class);
        $this->addMappingEntry('stylesheet', StylesheetMarshaller::class);
        $this->addMappingEntry('substring', SubstringMarshaller::class);
        $this->addMappingEntry('table', TableMarshaller::class);
        $this->addMappingEntry('div', DivMarshaller::class);
        $this->addMappingEntry('setDefaultValue', SetDefaultValueMarshaller::class);
        $this->addMappingEntry('randomInteger', RandomIntegerMarshaller::class);
        $this->addMappingEntry('exitTemplate', ExitTemplateMarshaller::class);
        $this->addMappingEntry('preCondition', PreConditionMarshaller::class);
        $this->addMappingEntry('selection', SelectionMarshaller::class);
        $this->addMappingEntry('assessmentItemRef', AssessmentItemRefMarshaller::class);
        $this->addMappingEntry('setCorrectResponse', SetCorrectResponseMarshaller::class);
        $this->addMappingEntry('value', ValueMarshaller::class);
        $this->addMappingEntry('assessmentSection', AssessmentSectionMarshaller::class);
        $this->addMappingEntry('baseValue', BaseValueMarshaller::class);
        $this->addMappingEntry('prompt', PromptMarshaller::class);
        $this->addMappingEntry('simpleChoice', SimpleChoiceMarshaller::class);
        $this->addMappingEntry('printedVariable', PrintedVariableMarshaller::class);
        $this->addMappingEntry('defaultValue', DefaultValueMarshaller::class);
        $this->addMappingEntry('templateCondition', TemplateConditionMarshaller::class);
        $this->addMappingEntry('templateConstraint', TemplateConstraintMarshaller::class);
        $this->addMappingEntry('setTemplateValue', SetTemplateValueMarshaller::class);
        $this->addMappingEntry('templateDefault', TemplateDefaultMarshaller::class);
        $this->addMappingEntry('templateProcessing', TemplateProcessingMarshaller::class);
        $this->addMappingEntry('testFeedback', TestFeedbackMarshaller::class);
        $this->addMappingEntry('testPart', TestPartMarshaller::class);
        $this->addMappingEntry('testVariables', TestVariablesMarshaller::class);
        $this->addMappingEntry('timeLimits', TimeLimitsMarshaller::class);
        $this->addMappingEntry('uploadInteraction', UploadInteractionMarshaller::class);
        $this->addMappingEntry('variableDeclaration', VariableDeclarationMarshaller::class);
        $this->addMappingEntry('variableMapping', VariableMappingMarshaller::class);
        $this->addMappingEntry('variable', VariableMarshaller::class);
        $this->addMappingEntry('weight', WeightMarshaller::class);
        $this->addMappingEntry('choiceInteraction', ChoiceInteractionMarshaller::class);
        $this->addMappingEntry('textRun', TextRunMarshaller::class);
        $this->addMappingEntry('templateDeclaration', TemplateDeclarationMarshaller::class);
        $this->addMappingEntry('default', DefaultValMarshaller::class);
        $this->addMappingEntry('null', NullValueMarshaller::class);
        $this->addMappingEntry('max', OperatorMarshaller::class);
        $this->addMappingEntry('min', OperatorMarshaller::class);
        $this->addMappingEntry('gcd', OperatorMarshaller::class);
        $this->addMappingEntry('lcm', OperatorMarshaller::class);
        $this->addMappingEntry('multiple', OperatorMarshaller::class);
        $this->addMappingEntry('ordered', OperatorMarshaller::class);
        $this->addMappingEntry('containerSize', OperatorMarshaller::class);
        $this->addMappingEntry('isNull', OperatorMarshaller::class);
        $this->addMappingEntry('random', OperatorMarshaller::class);
        $this->addMappingEntry('member', OperatorMarshaller::class);
        $this->addMappingEntry('delete', OperatorMarshaller::class);
        $this->addMappingEntry('contains', OperatorMarshaller::class);
        $this->addMappingEntry('not', OperatorMarshaller::class);
        $this->addMappingEntry('and', OperatorMarshaller::class);
        $this->addMappingEntry('or', OperatorMarshaller::class);
        $this->addMappingEntry('match', OperatorMarshaller::class);
        $this->addMappingEntry('lt', OperatorMarshaller::class);
        $this->addMappingEntry('gt', OperatorMarshaller::class);
        $this->addMappingEntry('lte', OperatorMarshaller::class);
        $this->addMappingEntry('gte', OperatorMarshaller::class);
        $this->addMappingEntry('durationLT', OperatorMarshaller::class);
        $this->addMappingEntry('durationGTE', OperatorMarshaller::class);
        $this->addMappingEntry('sum', OperatorMarshaller::class);
        $this->addMappingEntry('product', OperatorMarshaller::class);
        $this->addMappingEntry('subtract', OperatorMarshaller::class);
        $this->addMappingEntry('divide', OperatorMarshaller::class);
        $this->addMappingEntry('power', OperatorMarshaller::class);
        $this->addMappingEntry('integerDivide', OperatorMarshaller::class);
        $this->addMappingEntry('integerModulus', OperatorMarshaller::class);
        $this->addMappingEntry('truncate', OperatorMarshaller::class);
        $this->addMappingEntry('round', OperatorMarshaller::class);
        $this->addMappingEntry('integerToFloat', OperatorMarshaller::class);
        $this->addMappingEntry('customOperator', OperatorMarshaller::class);
        $this->addMappingEntry('outcomeIf', OutcomeControlMarshaller::class);
        $this->addMappingEntry('outcomeElseIf', OutcomeControlMarshaller::class);
        $this->addMappingEntry('outcomeElse', OutcomeControlMarshaller::class);
        $this->addMappingEntry('responseIf', ResponseControlMarshaller::class);
        $this->addMappingEntry('responseElseIf', ResponseControlMarshaller::class);
        $this->addMappingEntry('responseElse', ResponseControlMarshaller::class);
        $this->addMappingEntry('templateIf', TemplateControlMarshaller::class);
        $this->addMappingEntry('templateElseIf', TemplateControlMarshaller::class);
        $this->addMappingEntry('templateElse', TemplateControlMarshaller::class);
        $this->addMappingEntry('em', SimpleInlineMarshaller::class);
        $this->addMappingEntry('strong', SimpleInlineMarshaller::class);
        $this->addMappingEntry('abbr', SimpleInlineMarshaller::class);
        $this->addMappingEntry('acronym', SimpleInlineMarshaller::class);
        $this->addMappingEntry('b', SimpleInlineMarshaller::class);
        $this->addMappingEntry('big', SimpleInlineMarshaller::class);
        $this->addMappingEntry('cite', SimpleInlineMarshaller::class);
        $this->addMappingEntry('code', SimpleInlineMarshaller::class);
        $this->addMappingEntry('dfn', SimpleInlineMarshaller::class);
        $this->addMappingEntry('i', SimpleInlineMarshaller::class);
        $this->addMappingEntry('kbd', SimpleInlineMarshaller::class);
        $this->addMappingEntry('samp', SimpleInlineMarshaller::class);
        $this->addMappingEntry('small', SimpleInlineMarshaller::class);
        $this->addMappingEntry('span', SimpleInlineMarshaller::class);
        $this->addMappingEntry('sub', SimpleInlineMarshaller::class);
        $this->addMappingEntry('sup', SimpleInlineMarshaller::class);
        $this->addMappingEntry('tt', SimpleInlineMarshaller::class);
        $this->addMappingEntry('var', SimpleInlineMarshaller::class);
        $this->addMappingEntry('a', SimpleInlineMarshaller::class);
        $this->addMappingEntry('q', SimpleInlineMarshaller::class);
        $this->addMappingEntry('thead', TablePartMarshaller::class);
        $this->addMappingEntry('tbody', TablePartMarshaller::class);
        $this->addMappingEntry('tfoot', TablePartMarshaller::class);
        $this->addMappingEntry('td', TableCellMarshaller::class);
        $this->addMappingEntry('th', TableCellMarshaller::class);
        $this->addMappingEntry('address', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h1', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h2', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h3', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h4', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h5', AtomicBlockMarshaller::class);
        $this->addMappingEntry('h6', AtomicBlockMarshaller::class);
        $this->addMappingEntry('p', AtomicBlockMarshaller::class);
        $this->addMappingEntry('pre', AtomicBlockMarshaller::class);
        $this->addMappingEntry('ul', ListMarshaller::class);
        $this->addMappingEntry('ol', ListMarshaller::class);
        $this->addMappingEntry('dd', DlElementMarshaller::class);
        $this->addMappingEntry('dt', DlElementMarshaller::class);
        $this->addMappingEntry('orderInteraction', ChoiceInteractionMarshaller::class);
        $this->addMappingEntry('gapText', GapChoiceMarshaller::class);
        $this->addMappingEntry('gapImg', GapChoiceMarshaller::class);
        $this->addMappingEntry('textEntryInteraction', TextInteractionMarshaller::class);
        $this->addMappingEntry('extendedTextInteraction', TextInteractionMarshaller::class);
        $this->addMappingEntry('feedbackInline', FeedbackElementMarshaller::class);
        $this->addMappingEntry('feedbackBlock', FeedbackElementMarshaller::class);
        $this->addMappingEntry('templateInline', TemplateElementMarshaller::class);
        $this->addMappingEntry('templateBlock', TemplateElementMarshaller::class);
        $this->addMappingEntry('hotspotChoice', HotspotMarshaller::class);
        $this->addMappingEntry('associableHotspot', HotspotMarshaller::class);
        $this->addMappingEntry('include', XIncludeMarshaller::class);
        $this->addMappingEntry('assessmentResult', AssessmentResultMarshaller::class);
        $this->addMappingEntry('context', ContextMarshaller::class);
        $this->addMappingEntry('sessionIdentifier', SessionIdentifierMarshaller::class);
        $this->addMappingEntry('testResult', TestResultMarshaller::class);
        $this->addMappingEntry('itemResult', ItemResultMarshaller::class);
        $this->addMappingEntry('responseVariable', ResponseVariableMarshaller::class);
        $this->addMappingEntry('candidateResponse', CandidateResponseMarshaller::class);
        $this->addMappingEntry('templateVariable', TemplateVariableMarshaller::class);
        $this->addMappingEntry('outcomeVariable', OutcomeVariableMarshaller::class);
    }

    /**
     * Set the associative array which represents the current QTI class <-> Marshaller class mapping.
     *
     * @param array $mapping An associative array where keys are QTI class names and values are fully qualified PHP class names.
     */
    protected function setMapping(array &$mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Add a mapping entry for a given tuple $qtiClassName <-> $marshallerClassName.
     *
     * @param string $qtiClassName A QTI class name.
     * @param string $marshallerClassName A PHP marshaller class name (fully qualified).
     */
    public function addMappingEntry($qtiClassName, $marshallerClassName)
    {
        $mapping = &$this->getMapping();
        $mapping[$qtiClassName] = $marshallerClassName;
    }

    /**
     * Whether a mapping entry is defined for a given $qtiClassName.
     *
     * @param string $qtiClassName A QTI class name.
     * @return bool Whether a mapping entry is defined.
     */
    public function hasMappingEntry($qtiClassName)
    {
        $mapping = &$this->getMapping();
        return isset($mapping[$qtiClassName]);
    }

    /**
     * Get the mapping entry.
     *
     * @param string $qtiClassName A QTI class name.
     * @return false|string False if does not exist, otherwise a fully qualified class name.
     */
    public function getMappingEntry($qtiClassName)
    {
        $mapping = &$this->getMapping();
        return $mapping[$qtiClassName];
    }

    /**
     * Remove a mapping for $qtiClassName.
     *
     * @param string $qtiClassName A QTI class name.
     */
    public function removeMappingEntry($qtiClassName)
    {
        $mapping = &$this->getMapping();

        if ($this->hasMappingEntry($qtiClassName)) {
            unset($mapping[$qtiClassName]);
        }
    }

    /**
     * Set Web Componenent Friendship
     *
     * Sets whether or not consider Web Component friendly QTI components.
     *
     * @param bool $webComponentFriendly
     */
    protected function setWebComponentFriendly($webComponentFriendly)
    {
        $this->webComponentFriendly = $webComponentFriendly;
    }

    /**
     * Web Component Friendship Status
     *
     * Whether or not Web Component friendly QTI components are considered.
     *
     * @return bool
     */
    public function isWebComponentFriendly()
    {
        return $this->webComponentFriendly;
    }

    /**
     * Create a marshaller for a given QtiComponent or DOMElement object, depending on the current mapping
     * of the MarshallerFactory. If no mapping entry can be found, the factory will perform a ultimate
     * trial in the qtism\\data\\storage\\xml\\marshalling namespace to find the relevant Marshaller object.
     *
     * The newly created marshaller will be set up with the MarshallerFactory itself as its MarshallerFactory
     * object (yes, we know, this is highly recursive but necessary x)).
     *
     * @param DOMElement|QtiComponent $object A QtiComponent or DOMElement object you want to get the corresponding Marshaller object.
     * @param array $args An optional array of arguments to be passed to the Marshaller constructor.
     * @return Marshaller The corresponding Marshaller object.
     * @throws RuntimeException If no Marshaller object can be created for the given $object.
     * @throws InvalidArgumentException If $object is not a QtiComponent nor a DOMElement object.
     */
    public function createMarshaller($object, array $args = [])
    {
        if ($object instanceof QtiComponent) {
            $qtiClassName = $object->getQtiClassName();
        } else {
            if ($object instanceof DOMElement) {
                $qtiClassName = $object->localName;
            }
        }

        if (isset($qtiClassName)) {
            try {
                // Look for a mapping entry.
                if ($this->hasMappingEntry($qtiClassName)) {
                    $class = new ReflectionClass($this->getMappingEntry($qtiClassName));
                } else {
                    // Look for default.
                    $className = 'qtism\\data\\storage\\xml\\marshalling\\' . ucfirst($qtiClassName) . 'Marshaller';
                    $class = new ReflectionClass($className);
                }
            } catch (ReflectionException $e) {
                $msg = "No marshaller implementation could be found for component '${qtiClassName}'.";
                throw new RuntimeException($msg, 0, $e);
            }

            $marshaller = $this->instantiateMarshaller($class, $args);
            $marshaller->setMarshallerFactory($this);

            return $marshaller;
        } else {
            $msg = "The object argument must be a QtiComponent or a DOMElementObject, '" . gettype($object) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    abstract protected function instantiateMarshaller(ReflectionClass $class, array $args);
}
