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

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\runtime\rendering\AbstractRenderingContext;
use \DOMDocument;

/**
 * Represents an XHTML rendering context.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class XhtmlRenderingContext extends AbstractRenderingContext implements XhtmlRenderingConfig {
    
    /**
     * The document to be generated during the rendering.
     * 
     * @var DOMDocument
     */
    private $document;
    
    /**
     * Wether choices in shufflable interactions
     * must be shuffled.
     * 
     * @var boolean
     */
    private $shuffle = false;
    
    /**
     * Create a new XhtmlRenderingContext object.
     * 
     */
    public function __construct() {
        parent::__construct();
        $this->setDocument(new DOMDocument('1.0', 'UTF-8'));
        
        // The following QTI classes can be rendered
        // with the BodyElementRenderer.
        $bodyElementRenderer = new BodyElementRenderer();
        $this->registerRenderer('abbr', $bodyElementRenderer);
        $this->registerRenderer('acronym', $bodyElementRenderer);
        $this->registerRenderer('address', $bodyElementRenderer);
        $this->registerRenderer('br', $bodyElementRenderer);
        $this->registerRenderer('cite', $bodyElementRenderer);
        $this->registerRenderer('code', $bodyElementRenderer);
        $this->registerRenderer('dfn', $bodyElementRenderer);
        $this->registerRenderer('div', $bodyElementRenderer);
        $this->registerRenderer('em', $bodyElementRenderer);
        $this->registerRenderer('h1', $bodyElementRenderer);
        $this->registerRenderer('h2', $bodyElementRenderer);
        $this->registerRenderer('h3', $bodyElementRenderer);
        $this->registerRenderer('h4', $bodyElementRenderer);
        $this->registerRenderer('h5', $bodyElementRenderer);
        $this->registerRenderer('h6', $bodyElementRenderer);
        $this->registerRenderer('p', $bodyElementRenderer);
        $this->registerRenderer('pre', $bodyElementRenderer);
        $this->registerRenderer('samp', $bodyElementRenderer);
        $this->registerRenderer('span', $bodyElementRenderer);
        $this->registerRenderer('strong', $bodyElementRenderer);
        $this->registerRenderer('var', $bodyElementRenderer);
        $this->registerRenderer('dl', $bodyElementRenderer);
        $this->registerRenderer('dt', $bodyElementRenderer);
        $this->registerRenderer('dd', $bodyElementRenderer);
        $this->registerRenderer('ol', $bodyElementRenderer);
        $this->registerRenderer('ul', $bodyElementRenderer);
        $this->registerRenderer('li', $bodyElementRenderer);
        $this->registerRenderer('object', $bodyElementRenderer);
        $this->registerRenderer('b', $bodyElementRenderer);
        $this->registerRenderer('big', $bodyElementRenderer);
        $this->registerRenderer('hr', $bodyElementRenderer);
        $this->registerRenderer('i', $bodyElementRenderer);
        $this->registerRenderer('small', $bodyElementRenderer);
        $this->registerRenderer('sub', $bodyElementRenderer);
        $this->registerRenderer('sup', $bodyElementRenderer);
        $this->registerRenderer('tt', $bodyElementRenderer);
        $this->registerRenderer('caption', $bodyElementRenderer);
        $this->registerRenderer('tbody', $bodyElementRenderer);
        $this->registerRenderer('tfoot', $bodyElementRenderer);
        $this->registerRenderer('thead', $bodyElementRenderer);
        $this->registerRenderer('tr', $bodyElementRenderer);
        
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
        $this->registerRenderer('textRun', new TextRunRenderer());
        $this->registerRenderer('a', new ARenderer());
        $this->registerRenderer('blockquote', new BlockquoteRenderer());
        $this->registerRenderer('img', new ImgRenderer());
        $this->registerRenderer('object', new ObjectRenderer());
        $this->registerRenderer('param', new ParamRenderer());
        $this->registerRenderer('q', new QRenderer());
        $this->registerRenderer('stylesheet', new StylesheetRenderer());
        $this->registerRenderer('itemBody', new ItemBodyRenderer());
        $this->registerRenderer('prompt', new PromptRenderer());
        $this->registerRenderer('table', new TableRenderer());
        $this->registerRenderer('rubricBlock', new RubricBlockRenderer());
        $this->registerRenderer('choiceInteraction', new ChoiceInteractionRenderer());
        $this->registerRenderer('orderInteraction', new OrderInteractionRenderer());
        $this->registerRenderer('associateInteraction', new AssociateInteractionRenderer());
        $this->registerRenderer('matchInteraction', new MatchInteractionRenderer());
        $this->registerRenderer('gapMatchInteraction', new GapMatchInteractionRenderer());
        $this->registerRenderer('inlineChoiceInteraction', new InlineChoiceInteractionRenderer());
        $this->registerRenderer('textEntryInteraction', new TextEntryInteractionRenderer());
        $this->registerRenderer('simpleChoice', new SimpleChoiceRenderer());
        $this->registerRenderer('simpleAssociableChoice', new SimpleAssociableChoiceRenderer());
        $this->registerRenderer('inlineChoice', new InlineChoiceRenderer());
        $this->registerRenderer('simpleMatchSet', new SimpleMatchSetRenderer());
        $this->registerRenderer('gapText', new GapTextRenderer());
        $this->registerRenderer('gapImg', new GapImgRenderer());
        $this->registerRenderer('gap', new GapRenderer());
    }

    /**
     * Set the document to be used for rendering.
     * 
     * @param DOMDocument $document
     */
    public function setDocument(DOMDocument $document) {
        $this->document = $document;
    }
    
    /**
     * Get the document currently used for rendering.
     * 
     * @return DOMDocument
     */
    public function getDocument() {
        return $this->document;
    }
    
    public function setShuffle($shuffle) {
        $this->shuffle = $shuffle;
    }
    
    public function mustShuffle() {
        return $this->shuffle;
    }

    public function reset() {
        parent::reset();
        $this->setDocument(new DOMDocument('1.0', 'UTF-8'));
    }
}