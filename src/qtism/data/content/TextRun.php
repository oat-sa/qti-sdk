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

namespace qtism\data\content;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * A text run is simply a run of characters. Unlike all other elements in the
 * content model it is not a sub-class of bodyElement. To assign attributes to a
 * run of text you must use the span element instead.
 */
class TextRun extends QtiComponent implements FlowStatic, InlineStatic, TextOrVariable
{
    use FlowTrait;

    /**
     * The characters contained in the TextRun.
     *
     * @var string
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new TextRun object.
     *
     * @param string $content The characters to be contained by the TextRun.
     */
    public function __construct($content)
    {
        $this->setContent($content);
    }

    /**
     * Set the characters to be contained by the TextRun.
     *
     * @param string $content A string value.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get the characters contained by the TextRun.
     *
     * @return string A string value.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     *
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }

    public function getQtiClassName()
    {
        return 'textRun';
    }
}
