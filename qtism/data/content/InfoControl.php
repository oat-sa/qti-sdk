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

use InvalidArgumentException;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * The infoControl element is a means to provide the candidate with extra information about
 * the item when s/he chooses to trigger the control. The extra information can be a hint,
 * but could also be additional tools such as a ruler or a (javaScript) calculator.
 *
 * Unlike endAttemptInteraction, triggering infoControl has no consequence for response processing.
 * That means that its triggering won't be recorded, nor the candidate penalised for triggering it.
 */
class InfoControl extends BodyElement implements BlockStatic, FlowStatic
{
    use FlowTrait;

    /**
     * The content of the InfoControl.
     *
     * @var FlowStaticCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new InfoControl object.
     *
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the above arguments is invalid.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setContent(new FlowStaticCollection());
    }

    /**
     * Set the content of the InfoControl.
     *
     * @param FlowStaticCollection $content A collection of FlowStatic objects.
     */
    public function setContent(FlowStaticCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the content of the InfoControl.
     *
     * @return FlowStaticCollection A collection of FlowStatic objects.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'infoControl';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection($this->getContent()->getArrayCopy());
    }
}
