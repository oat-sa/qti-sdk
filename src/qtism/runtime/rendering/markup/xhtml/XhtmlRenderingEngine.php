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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\runtime\rendering\markup\AbstractMarkupRenderingEngine;

/**
 * The QTI XHTML Rendering Engine.
 */
class XhtmlRenderingEngine extends AbstractMarkupRenderingEngine
{
    /**
     * Create a new XhtmlRenderingEngine object.
     */
    public function __construct()
    {
        parent::__construct();

        // QTI Components the rendering engine will
        // not take into account.
        $ignoreClasses = [
            'responseDeclaration',
            'outcomeDeclaration',
            'templateDeclaration',
            'templateProcessing',
            'responseProcessing',
        ];

        $this->ignoreQtiClasses($ignoreClasses);

        // The following QTI classes can be rendered
        // with the BodyElementRenderer.
        $bodyElementRenderer = new BodyElementRenderer();
        $this->registerRenderer('abbr', $bodyElementRenderer);
        $this->registerRenderer('acronym', $bodyElementRenderer);
        $this->registerRenderer('address', $bodyElementRenderer);
        $this->registerRenderer('b', $bodyElementRenderer);
        $this->registerRenderer('bdo', $bodyElementRenderer);
        $this->registerRenderer('big', $bodyElementRenderer);
        $this->registerRenderer('br', $bodyElementRenderer);
        $this->registerRenderer('caption', $bodyElementRenderer);
        $this->registerRenderer('cite', $bodyElementRenderer);
        $this->registerRenderer('code', $bodyElementRenderer);
        $this->registerRenderer('dd', $bodyElementRenderer);
        $this->registerRenderer('dfn', $bodyElementRenderer);
        $this->registerRenderer('div', $bodyElementRenderer);
        $this->registerRenderer('dl', $bodyElementRenderer);
        $this->registerRenderer('dt', $bodyElementRenderer);
        $this->registerRenderer('em', $bodyElementRenderer);
        $this->registerRenderer('h1', $bodyElementRenderer);
        $this->registerRenderer('h2', $bodyElementRenderer);
        $this->registerRenderer('h3', $bodyElementRenderer);
        $this->registerRenderer('h4', $bodyElementRenderer);
        $this->registerRenderer('h5', $bodyElementRenderer);
        $this->registerRenderer('h6', $bodyElementRenderer);
        $this->registerRenderer('hr', $bodyElementRenderer);
        $this->registerRenderer('i', $bodyElementRenderer);
        $this->registerRenderer('kbd', $bodyElementRenderer);
        $this->registerRenderer('li', $bodyElementRenderer);
        $this->registerRenderer('ol', $bodyElementRenderer);
        $this->registerRenderer('p', $bodyElementRenderer);
        $this->registerRenderer('pre', $bodyElementRenderer);
        $this->registerRenderer('samp', $bodyElementRenderer);
        $this->registerRenderer('small', $bodyElementRenderer);
        $this->registerRenderer('span', $bodyElementRenderer);
        $this->registerRenderer('strong', $bodyElementRenderer);
        $this->registerRenderer('sub', $bodyElementRenderer);
        $this->registerRenderer('sup', $bodyElementRenderer);
        $this->registerRenderer('tbody', $bodyElementRenderer);
        $this->registerRenderer('tfoot', $bodyElementRenderer);
        $this->registerRenderer('thead', $bodyElementRenderer);
        $this->registerRenderer('tr', $bodyElementRenderer);
        $this->registerRenderer('tt', $bodyElementRenderer);
        $this->registerRenderer('ul', $bodyElementRenderer);
        $this->registerRenderer('var', $bodyElementRenderer);

        // Both col and components elements can be rendered
        // by the ColRenderer.
        $colRenderer = new ColRenderer();
        $this->registerRenderer('col', $colRenderer);
        $this->registerRenderer('colgroup', $colRenderer);

        // Both td and th components can be rendered by the
        // TableCellRenderer.
        $tableCellRenderer = new TableCellRenderer();
        $this->registerRenderer('td', $tableCellRenderer);
        $this->registerRenderer('th', $tableCellRenderer);

        // Other bindings...
        $this->registerRenderer('a', new ARenderer());
        $this->registerRenderer('assessmentItem', new AssessmentItemRenderer());
        $this->registerRenderer('associableHotspot', new AssociableHotspotRenderer());
        $this->registerRenderer('associateInteraction', new AssociateInteractionRenderer());
        $this->registerRenderer('blockquote', new BlockquoteRenderer());
        $this->registerRenderer('choiceInteraction', new ChoiceInteractionRenderer());
        $this->registerRenderer('drawingInteraction', new DrawingInteractionRenderer());
        $this->registerRenderer('endAttemptInteraction', new EndAttemptInteractionRenderer());
        $this->registerRenderer('extendedTextInteraction', new ExtendedTextInteractionRenderer());
        $this->registerRenderer('feedbackBlock', new FeedbackBlockRenderer());
        $this->registerRenderer('feedbackInline', new FeedbackInlineRenderer());
        $this->registerRenderer('gap', new GapRenderer());
        $this->registerRenderer('gapImg', new GapImgRenderer());
        $this->registerRenderer('gapMatchInteraction', new GapMatchInteractionRenderer());
        $this->registerRenderer('gapText', new GapTextRenderer());
        $this->registerRenderer('graphicAssociateInteraction', new GraphicAssociateInteractionRenderer());
        $this->registerRenderer('graphicGapMatchInteraction', new GraphicGapMatchInteractionRenderer());
        $this->registerRenderer('graphicOrderInteraction', new GraphicOrderInteractionRenderer());
        $this->registerRenderer('hotspotChoice', new HotspotChoiceRenderer());
        $this->registerRenderer('hotspotInteraction', new HotspotInteractionRenderer());
        $this->registerRenderer('hottext', new HottextRenderer());
        $this->registerRenderer('hottextInteraction', new HottextInteractionRenderer());
        $this->registerRenderer('img', new ImgRenderer());
        $this->registerRenderer('inlineChoice', new InlineChoiceRenderer());
        $this->registerRenderer('inlineChoiceInteraction', new InlineChoiceInteractionRenderer());
        $this->registerRenderer('itemBody', new ItemBodyRenderer());
        $this->registerRenderer('matchInteraction', new MatchInteractionRenderer());
        $this->registerRenderer('mediaInteraction', new MediaInteractionRenderer());
        $this->registerRenderer('modalFeedback', new ModalFeedbackRenderer());
        $this->registerRenderer('object', new ObjectRenderer());
        $this->registerRenderer('orderInteraction', new OrderInteractionRenderer());
        $this->registerRenderer('param', new ParamRenderer());
        $this->registerRenderer('positionObjectInteraction', new PositionObjectInteractionRenderer());
        $this->registerRenderer('positionObjectStage', new PositionObjectStageRenderer());
        $this->registerRenderer('printedVariable', new PrintedVariableRenderer());
        $this->registerRenderer('prompt', new PromptRenderer());
        $this->registerRenderer('q', new QRenderer());
        $this->registerRenderer('rubricBlock', new RubricBlockRenderer());
        $this->registerRenderer('selectPointInteraction', new SelectPointInteractionRenderer());
        $this->registerRenderer('simpleAssociableChoice', new SimpleAssociableChoiceRenderer());
        $this->registerRenderer('simpleChoice', new SimpleChoiceRenderer());
        $this->registerRenderer('simpleMatchSet', new SimpleMatchSetRenderer());
        $this->registerRenderer('sliderInteraction', new SliderInteractionRenderer());
        $this->registerRenderer('stylesheet', new StylesheetRenderer());
        $this->registerRenderer('table', new TableRenderer());
        $this->registerRenderer('textEntryInteraction', new TextEntryInteractionRenderer());
        $this->registerRenderer('textRun', new TextRunRenderer());
        $this->registerRenderer('uploadInteraction', new UploadInteractionRenderer());

        // External QTI Components.
        $this->registerRenderer('math', new MathRenderer());
        $this->registerRenderer('sub', new SsmlSubRenderer(), 'http://www.w3.org/2010/10/synthesis');
    }
}
