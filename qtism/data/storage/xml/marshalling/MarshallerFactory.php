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
use qtism\common\utils\Reflection;
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
     * @var boolean
     */
    private $webComponentFriendly = false;

    /**
     * Create a new instance of MarshallerFactory.
     *
     */
    public function __construct()
    {
        $this->addMappingEntry('customInteraction', 'qtism\\data\\storage\\xml\\marshalling\\CustomInteractionMarshaller');
        $this->addMappingEntry('br', 'qtism\\data\\storage\\xml\\marshalling\\BrMarshaller');
        $this->addMappingEntry('hr', 'qtism\\data\\storage\\xml\\marshalling\\HrMarshaller');
        $this->addMappingEntry('responseCondition', 'qtism\\data\\storage\\xml\\marshalling\\ResponseConditionMarshaller');
        $this->addMappingEntry('responseProcessing', 'qtism\\data\\storage\\xml\\marshalling\\ResponseProcessingMarshaller');
        $this->addMappingEntry('anyN', 'qtism\\data\\storage\\xml\\marshalling\\AnyNMarshaller');
        $this->addMappingEntry('areaMapEntry', 'qtism\\data\\storage\\xml\\marshalling\\AreaMapEntryMarshaller');
        $this->addMappingEntry('areaMapping', 'qtism\\data\\storage\\xml\\marshalling\\AreaMappingMarshaller');
        $this->addMappingEntry('assessmentItem', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentItemMarshaller');
        $this->addMappingEntry('assessmentSectionRef', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentSectionRefMarshaller');
        $this->addMappingEntry('assessmentTest', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentTestMarshaller');
        $this->addMappingEntry('associateInteraction', 'qtism\\data\\storage\\xml\\marshalling\\AssociateInteractionMarshaller');
        $this->addMappingEntry('blockquote', 'qtism\\data\\storage\\xml\\marshalling\\BlockquoteMarshaller');
        $this->addMappingEntry('correct', 'qtism\\data\\storage\\xml\\marshalling\\CorrectMarshaller');
        $this->addMappingEntry('dl', 'qtism\\data\\storage\\xml\\marshalling\\DlMarshaller');
        $this->addMappingEntry('drawingInteraction', 'qtism\\data\\storage\\xml\\marshalling\\DrawingInteractionMarshaller');
        $this->addMappingEntry('endAttemptInteraction', 'qtism\\data\\storage\\xml\\marshalling\\EndAttemptInteractionMarshaller');
        $this->addMappingEntry('equalRounded', 'qtism\\data\\storage\\xml\\marshalling\\EqualRoundedMarshaller');
        $this->addMappingEntry('exitResponse', 'qtism\\data\\storage\\xml\\marshalling\\ExitResponseMarshaller');
        $this->addMappingEntry('exitTest', 'qtism\\data\\storage\\xml\\marshalling\\ExitTestMarshaller');
        $this->addMappingEntry('fieldValue', 'qtism\\data\\storage\\xml\\marshalling\\FieldValueMarshaller');
        $this->addMappingEntry('hottextInteraction', 'qtism\\data\\storage\\xml\\marshalling\\HottextInteractionMarshaller');
        $this->addMappingEntry('inlineChoiceInteraction', 'qtism\\data\\storage\\xml\\marshalling\\InlineChoiceInteractionMarshaller');
        $this->addMappingEntry('gap', 'qtism\\data\\storage\\xml\\marshalling\\GapMarshaller');
        $this->addMappingEntry('gapMatchInteraction', 'qtism\\data\\storage\\xml\\marshalling\\GapMatchInteractionMarshaller');
        $this->addMappingEntry('graphicAssociateInteraction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicAssociateInteractionMarshaller');
        $this->addMappingEntry('graphicGapMatchInteraction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicGapMatchInteractionMarshaller');
        $this->addMappingEntry('graphicOrderInteraction', 'qtism\\data\\storage\\xml\\marshalling\\GraphicOrderInteractionMarshaller');
        $this->addMappingEntry('hotspotInteraction', 'qtism\\data\\storage\\xml\\marshalling\\HotspotInteractionMarshaller');
        $this->addMappingEntry('hottext', 'qtism\\data\\storage\\xml\\marshalling\\HottextMarshaller');
        $this->addMappingEntry('img', 'qtism\\data\\storage\\xml\\marshalling\\ImgMarshaller');
        $this->addMappingEntry('index', 'qtism\\data\\storage\\xml\\marshalling\\IndexMarshaller');
        $this->addMappingEntry('infoControl', 'qtism\\data\\storage\\xml\\marshalling\\InfoControlMarshaller');
        $this->addMappingEntry('inlineChoice', 'qtism\\data\\storage\\xml\\marshalling\\InlineChoiceMarshaller');
        $this->addMappingEntry('inside', 'qtism\\data\\storage\\xml\\marshalling\\InsideMarshaller');
        $this->addMappingEntry('interpolationTableEntry', 'qtism\\data\\storage\\xml\\marshalling\\InterpolationTableEntryMarshaller');
        $this->addMappingEntry('interpolationTable', 'qtism\\data\\storage\\xml\\marshalling\\InterpolationTableMarshaller');
        $this->addMappingEntry('itemBody', 'qtism\\data\\storage\\xml\\marshalling\\ItemBodyMarshaller');
        $this->addMappingEntry('itemSubset', 'qtism\\data\\storage\\xml\\marshalling\\ItemSubsetMarshaller');
        $this->addMappingEntry('li', 'qtism\\data\\storage\\xml\\marshalling\\LiMarshaller');
        $this->addMappingEntry('mapResponse', 'qtism\\data\\storage\\xml\\marshalling\\MapResponseMarshaller');
        $this->addMappingEntry('mapResponsePoint', 'qtism\\data\\storage\\xml\\marshalling\\MapResponsePointMarshaller');
        $this->addMappingEntry('matchInteraction', 'qtism\\data\\storage\\xml\\marshalling\\MatchInteractionMarshaller');
        $this->addMappingEntry('mathConstant', 'qtism\\data\\storage\\xml\\marshalling\\MathConstantMarshaller');
        $this->addMappingEntry('math', 'qtism\\data\\storage\\xml\\marshalling\\MathMarshaller');
        $this->addMappingEntry('mediaInteraction', 'qtism\\data\\storage\\xml\\marshalling\\MediaInteractionMarshaller');
        $this->addMappingEntry('modalFeedback', 'qtism\\data\\storage\\xml\\marshalling\\ModalFeedbackMarshaller');
        $this->addMappingEntry('numberCorrect', 'qtism\\data\\storage\\xml\\marshalling\\NumberCorrectMarshaller');
        $this->addMappingEntry('numberIncorrect', 'qtism\\data\\storage\\xml\\marshalling\\NumberIncorrectMarshaller');
        $this->addMappingEntry('numberPresented', 'qtism\\data\\storage\\xml\\marshalling\\NumberPresentedMarshaller');
        $this->addMappingEntry('numberResponded', 'qtism\\data\\storage\\xml\\marshalling\\NumberRespondedMarshaller');
        $this->addMappingEntry('numberSelected', 'qtism\\data\\storage\\xml\\marshalling\\NumberSelectedMarshaller');
        $this->addMappingEntry('param', 'qtism\\data\\storage\\xml\\marshalling\\ParamMarshaller');
        $this->addMappingEntry('lookupOutcomeValue', 'qtism\\data\\storage\\xml\\marshalling\\LookupOutcomeValueMarshaller');
        $this->addMappingEntry('mathOperator', 'qtism\\data\\storage\\xml\\marshalling\\MathOperatorMarshaller');
        $this->addMappingEntry('mapEntry', 'qtism\\data\\storage\\xml\\marshalling\\MapEntryMarshaller');
        $this->addMappingEntry('matchTableEntry', 'qtism\\data\\storage\\xml\\marshalling\\MatchTableEntryMarshaller');
        $this->addMappingEntry('matchTable', 'qtism\\data\\storage\\xml\\marshalling\\MatchTableMarshaller');
        $this->addMappingEntry('ordering', 'qtism\\data\\storage\\xml\\marshalling\\OrderingMarshaller');
        $this->addMappingEntry('outcomeCondition', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeConditionMarshaller');
        $this->addMappingEntry('matchTable', 'qtism\\data\\storage\\xml\\marshalling\\MatchTableMarshaller');
        $this->addMappingEntry('outcomeMaximum', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeMaximumMarshaller');
        $this->addMappingEntry('outcomeMinimum', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeMinimumMarshaller');
        $this->addMappingEntry('outcomeProcessing', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeProcessingMarshaller');
        $this->addMappingEntry('patternMatch', 'qtism\\data\\storage\\xml\\marshalling\\PatternMatchMarshaller');
        $this->addMappingEntry('positionObjectInteraction', 'qtism\\data\\storage\\xml\\marshalling\\PositionObjectInteractionMarshaller');
        $this->addMappingEntry('positionObjectStage', 'qtism\\data\\storage\\xml\\marshalling\\PositionObjectStageMarshaller');
        $this->addMappingEntry('randomFloat', 'qtism\\data\\storage\\xml\\marshalling\\RandomFloatMarshaller');
        $this->addMappingEntry('repeat', 'qtism\\data\\storage\\xml\\marshalling\\RepeatMarshaller');
        $this->addMappingEntry('mapping', 'qtism\\data\\storage\\xml\\marshalling\\MappingMarshaller');
        $this->addMappingEntry('correctResponse', 'qtism\\data\\storage\\xml\\marshalling\\CorrectResponseMarshaller');
        $this->addMappingEntry('matchTable', 'qtism\\data\\storage\\xml\\marshalling\\MatchTableMarshaller');
        $this->addMappingEntry('itemSessionControl', 'qtism\\data\\storage\\xml\\marshalling\\ItemSessionControlMarshaller');
        $this->addMappingEntry('responseDeclaration', 'qtism\\data\\storage\\xml\\marshalling\\ResponseDeclarationMarshaller');
        $this->addMappingEntry('outcomeDeclaration', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeDeclarationMarshaller');
        $this->addMappingEntry('roundTo', 'qtism\\data\\storage\\xml\\marshalling\\RoundToMarshaller');
        $this->addMappingEntry('rubricBlock', 'qtism\\data\\storage\\xml\\marshalling\\RubricBlockMarshaller');
        $this->addMappingEntry('object', 'qtism\\data\\storage\\xml\\marshalling\\ObjectMarshaller');
        $this->addMappingEntry('col', 'qtism\\data\\storage\\xml\\marshalling\\ColMarshaller');
        $this->addMappingEntry('colgroup', 'qtism\\data\\storage\\xml\\marshalling\\ColgroupMarshaller');
        $this->addMappingEntry('equal', 'qtism\\data\\storage\\xml\\marshalling\\EqualMarshaller');
        $this->addMappingEntry('sectionPart', 'qtism\\data\\storage\\xml\\marshalling\\SectionPartMarshaller');
        $this->addMappingEntry('selectPointInteraction', 'qtism\\data\\storage\\xml\\marshalling\\SelectPointInteractionMarshaller');
        $this->addMappingEntry('setOutcomeValue', 'qtism\\data\\storage\\xml\\marshalling\\SetOutcomeValueMarshaller');
        $this->addMappingEntry('simpleAssociableChoice', 'qtism\\data\\storage\\xml\\marshalling\\SimpleAssociableChoiceMarshaller');
        $this->addMappingEntry('caption', 'qtism\\data\\storage\\xml\\marshalling\\CaptionMarshaller');
        $this->addMappingEntry('tr', 'qtism\\data\\storage\\xml\\marshalling\\TrMarshaller');
        $this->addMappingEntry('branchRule', 'qtism\\data\\storage\\xml\\marshalling\\BranchRuleMarshaller');
        $this->addMappingEntry('simpleMatchSet', 'qtism\\data\\storage\\xml\\marshalling\\SimpleMatchSetMarshaller');
        $this->addMappingEntry('sliderInteraction', 'qtism\\data\\storage\\xml\\marshalling\\SliderInteractionMarshaller');
        $this->addMappingEntry('statsOperator', 'qtism\\data\\storage\\xml\\marshalling\\StatsOperatorMarshaller');
        $this->addMappingEntry('stringMatch', 'qtism\\data\\storage\\xml\\marshalling\\StringMatchMarshaller');
        $this->addMappingEntry('stylesheet', 'qtism\\data\\storage\\xml\\marshalling\\StylesheetMarshaller');
        $this->addMappingEntry('substring', 'qtism\\data\\storage\\xml\\marshalling\\SubstringMarshaller');
        $this->addMappingEntry('table', 'qtism\\data\\storage\\xml\\marshalling\\TableMarshaller');
        $this->addMappingEntry('div', 'qtism\\data\\storage\\xml\\marshalling\\DivMarshaller');
        $this->addMappingEntry('setDefaultValue', 'qtism\\data\\storage\\xml\\marshalling\\SetDefaultValueMarshaller');
        $this->addMappingEntry('randomInteger', 'qtism\\data\\storage\\xml\\marshalling\\RandomIntegerMarshaller');
        $this->addMappingEntry('exitTemplate', 'qtism\\data\\storage\\xml\\marshalling\\ExitTemplateMarshaller');
        $this->addMappingEntry('preCondition', 'qtism\\data\\storage\\xml\\marshalling\\PreConditionMarshaller');
        $this->addMappingEntry('selection', 'qtism\\data\\storage\\xml\\marshalling\\SelectionMarshaller');
        $this->addMappingEntry('assessmentItemRef', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentItemRefMarshaller');
        $this->addMappingEntry('setCorrectResponse', 'qtism\\data\\storage\\xml\\marshalling\\SetCorrectResponseMarshaller');
        $this->addMappingEntry('value', 'qtism\\data\\storage\\xml\\marshalling\\ValueMarshaller');
        $this->addMappingEntry('assessmentSection', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentSectionMarshaller');
        $this->addMappingEntry('baseValue', 'qtism\\data\\storage\\xml\\marshalling\\BaseValueMarshaller');
        $this->addMappingEntry('prompt', 'qtism\\data\\storage\\xml\\marshalling\\PromptMarshaller');
        $this->addMappingEntry('simpleChoice', 'qtism\\data\\storage\\xml\\marshalling\\SimpleChoiceMarshaller');
        $this->addMappingEntry('printedVariable', 'qtism\\data\\storage\\xml\\marshalling\\PrintedVariableMarshaller');
        $this->addMappingEntry('defaultValue', 'qtism\\data\\storage\\xml\\marshalling\\DefaultValueMarshaller');
        $this->addMappingEntry('templateCondition', 'qtism\\data\\storage\\xml\\marshalling\\TemplateConditionMarshaller');
        $this->addMappingEntry('templateConstraint', 'qtism\\data\\storage\\xml\\marshalling\\TemplateConstraintMarshaller');
        $this->addMappingEntry('setTemplateValue', 'qtism\\data\\storage\\xml\\marshalling\\SetTemplateValueMarshaller');
        $this->addMappingEntry('templateDefault', 'qtism\\data\\storage\\xml\\marshalling\\TemplateDefaultMarshaller');
        $this->addMappingEntry('templateProcessing', 'qtism\\data\\storage\\xml\\marshalling\\TemplateProcessingMarshaller');
        $this->addMappingEntry('testFeedback', 'qtism\\data\\storage\\xml\\marshalling\\TestFeedbackMarshaller');
        $this->addMappingEntry('testPart', 'qtism\\data\\storage\\xml\\marshalling\\TestPartMarshaller');
        $this->addMappingEntry('testVariables', 'qtism\\data\\storage\\xml\\marshalling\\TestVariablesMarshaller');
        $this->addMappingEntry('timeLimits', 'qtism\\data\\storage\\xml\\marshalling\\TimeLimitsMarshaller');
        $this->addMappingEntry('uploadInteraction', 'qtism\\data\\storage\\xml\\marshalling\\UploadInteractionMarshaller');
        $this->addMappingEntry('variableDeclaration', 'qtism\\data\\storage\\xml\\marshalling\\VariableDeclarationMarshaller');
        $this->addMappingEntry('variableMapping', 'qtism\\data\\storage\\xml\\marshalling\\VariableMappingMarshaller');
        $this->addMappingEntry('variable', 'qtism\\data\\storage\\xml\\marshalling\\VariableMarshaller');
        $this->addMappingEntry('weight', 'qtism\\data\\storage\\xml\\marshalling\\WeightMarshaller');
        $this->addMappingEntry('choiceInteraction', 'qtism\\data\\storage\\xml\\marshalling\\ChoiceInteractionMarshaller');
        $this->addMappingEntry('textRun', 'qtism\\data\\storage\\xml\\marshalling\\TextRunMarshaller');
        $this->addMappingEntry('templateDeclaration', 'qtism\\data\\storage\\xml\\marshalling\\TemplateDeclarationMarshaller');
        $this->addMappingEntry('default', 'qtism\\data\\storage\\xml\\marshalling\\DefaultValMarshaller');
        $this->addMappingEntry('null', 'qtism\\data\\storage\\xml\\marshalling\\NullValueMarshaller');
        $this->addMappingEntry('max', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('min', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('gcd', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('lcm', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('multiple', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('ordered', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('containerSize', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('isNull', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('random', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('member', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('delete', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('contains', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('not', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('and', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('or', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('match', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('lt', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('gt', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('lte', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('gte', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('durationLT', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('durationGTE', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('sum', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('product', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('subtract', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('divide', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('power', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('integerDivide', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('integerModulus', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('truncate', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('round', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('integerToFloat', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('customOperator', 'qtism\\data\\storage\\xml\\marshalling\\OperatorMarshaller');
        $this->addMappingEntry('outcomeIf', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeControlMarshaller');
        $this->addMappingEntry('outcomeElseIf', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeControlMarshaller');
        $this->addMappingEntry('outcomeElse', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeControlMarshaller');
        $this->addMappingEntry('responseIf', 'qtism\\data\\storage\\xml\\marshalling\\ResponseControlMarshaller');
        $this->addMappingEntry('responseElseIf', 'qtism\\data\\storage\\xml\\marshalling\\ResponseControlMarshaller');
        $this->addMappingEntry('responseElse', 'qtism\\data\\storage\\xml\\marshalling\\ResponseControlMarshaller');
        $this->addMappingEntry('templateIf', 'qtism\\data\\storage\\xml\\marshalling\\TemplateControlMarshaller');
        $this->addMappingEntry('templateElseIf', 'qtism\\data\\storage\\xml\\marshalling\\TemplateControlMarshaller');
        $this->addMappingEntry('templateElse', 'qtism\\data\\storage\\xml\\marshalling\\TemplateControlMarshaller');
        $this->addMappingEntry('em', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('strong', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('abbr', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('acronym', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('b', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('big', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('cite', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('code', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('dfn', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('i', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('kbd', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('samp', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('small', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('span', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('sub', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('sup', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('tt', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('var', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('a', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('q', 'qtism\\data\\storage\\xml\\marshalling\\SimpleInlineMarshaller');
        $this->addMappingEntry('thead', 'qtism\\data\\storage\\xml\\marshalling\\TablePartMarshaller');
        $this->addMappingEntry('tbody', 'qtism\\data\\storage\\xml\\marshalling\\TablePartMarshaller');
        $this->addMappingEntry('tfoot', 'qtism\\data\\storage\\xml\\marshalling\\TablePartMarshaller');
        $this->addMappingEntry('td', 'qtism\\data\\storage\\xml\\marshalling\\TableCellMarshaller');
        $this->addMappingEntry('th', 'qtism\\data\\storage\\xml\\marshalling\\TableCellMarshaller');
        $this->addMappingEntry('address', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h1', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h2', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h3', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h4', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h5', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('h6', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('p', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('pre', 'qtism\\data\\storage\\xml\\marshalling\\AtomicBlockMarshaller');
        $this->addMappingEntry('ul', 'qtism\\data\\storage\\xml\\marshalling\\ListMarshaller');
        $this->addMappingEntry('ol', 'qtism\\data\\storage\\xml\\marshalling\\ListMarshaller');
        $this->addMappingEntry('dd', 'qtism\\data\\storage\\xml\\marshalling\\DlElementMarshaller');
        $this->addMappingEntry('dt', 'qtism\\data\\storage\\xml\\marshalling\\DlElementMarshaller');
        $this->addMappingEntry('orderInteraction', 'qtism\\data\\storage\\xml\\marshalling\\ChoiceInteractionMarshaller');
        $this->addMappingEntry('gapText', 'qtism\\data\\storage\\xml\\marshalling\\GapChoiceMarshaller');
        $this->addMappingEntry('gapImg', 'qtism\\data\\storage\\xml\\marshalling\\GapChoiceMarshaller');
        $this->addMappingEntry('textEntryInteraction', 'qtism\\data\\storage\\xml\\marshalling\\TextInteractionMarshaller');
        $this->addMappingEntry('extendedTextInteraction', 'qtism\\data\\storage\\xml\\marshalling\\TextInteractionMarshaller');
        $this->addMappingEntry('feedbackInline', 'qtism\\data\\storage\\xml\\marshalling\\FeedbackElementMarshaller');
        $this->addMappingEntry('feedbackBlock', 'qtism\\data\\storage\\xml\\marshalling\\FeedbackElementMarshaller');
        $this->addMappingEntry('templateInline', 'qtism\\data\\storage\\xml\\marshalling\\TemplateElementMarshaller');
        $this->addMappingEntry('templateBlock', 'qtism\\data\\storage\\xml\\marshalling\\TemplateElementMarshaller');
        $this->addMappingEntry('hotspotChoice', 'qtism\\data\\storage\\xml\\marshalling\\HotspotMarshaller');
        $this->addMappingEntry('associableHotspot', 'qtism\\data\\storage\\xml\\marshalling\\HotspotMarshaller');
        $this->addMappingEntry('include', 'qtism\\data\\storage\\xml\\marshalling\\XIncludeMarshaller');
        $this->addMappingEntry('assessmentResult', 'qtism\\data\\storage\\xml\\marshalling\\AssessmentResultMarshaller');
        $this->addMappingEntry('context', 'qtism\\data\\storage\\xml\\marshalling\\ContextMarshaller');
        $this->addMappingEntry('sessionIdentifier', 'qtism\\data\\storage\\xml\\marshalling\\SessionIdentifierMarshaller');
        $this->addMappingEntry('testResult', 'qtism\\data\\storage\\xml\\marshalling\\TestResultMarshaller');
        $this->addMappingEntry('itemResult', 'qtism\\data\\storage\\xml\\marshalling\\ItemResultMarshaller');
        $this->addMappingEntry('responseVariable', 'qtism\\data\\storage\\xml\\marshalling\\ResponseVariableMarshaller');
        $this->addMappingEntry('candidateResponse', 'qtism\\data\\storage\\xml\\marshalling\\CandidateResponseMarshaller');
        $this->addMappingEntry('templateVariable', 'qtism\\data\\storage\\xml\\marshalling\\TemplateVariableMarshaller');
        $this->addMappingEntry('outcomeVariable', 'qtism\\data\\storage\\xml\\marshalling\\OutcomeVariableMarshaller');
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
     * @return boolean Whether a mapping entry is defined.
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
     * @param boolean $webComponentFriendly
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
     * @return boolean
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
