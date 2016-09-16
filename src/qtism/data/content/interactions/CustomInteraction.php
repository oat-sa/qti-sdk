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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\interactions;

use qtism\data\QtiComponentCollection;
use qtism\data\content\Flow;
use qtism\data\content\Block;
use qtism\data\ExternalQtiComponent;
use qtism\data\IExternal;

/**
 * From IMS QTI:
 *
 * The custom interaction provides an opportunity for extensibility of this
 * specification to include support for interactions not currently documented.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class CustomInteraction extends Interaction implements IExternal, Block, Flow
{
    use \qtism\data\content\FlowTrait;

    /**
     * The xml string content of the custom interaction.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlString;

    /**
     *
     * @var \qtism\data\ExternalQtiComponent
     */
    private $externalComponent = null;

    /**
     * Create a new CustomInteraction object.
     *
     * @param string $responseIdentifier The identifier of the Response Variable bound to the interaction.
     * @param string $xmlString The xml data representing the whole customInteraction component and its content.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws \InvalidArgumentException
     */
    public function __construct($responseIdentifier, $xmlString, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($responseIdentifier, $id, $class, $lang, $label);
        $this->setXmlString($xmlString);
        $this->setExternalComponent(new ExternalQtiComponent($xmlString));
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'customInteraction';
    }

    /**
     * Get the complete XML String representing the customInteraction.
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->xmlString;
    }

    /**
     * Set the complete XML String representing the customInteraction.
     *
     * @param string $xmlString
     */
    public function setXmlString($xmlString)
    {
        $this->xmlString = $xmlString;
        if ($this->externalComponent !== null) {
            $this->getExternalComponent()->setXmlString($xmlString);
        }
    }

    /**
     * Get the XML content of the custom interaction itself and its content.
     *
     * @return \DOMDocument A DOMDocument object representing the custom interaction.
     * @throws \RuntimeException If the XML content of the custom interaction and/or its content cannot be transformed into a valid DOMDocument.
     */
    public function getXml()
    {
        return $this->getExternalComponent()->getXml();
    }

    /**
     * Set the encapsulated external component.
     *
     * @param \qtism\data\ExternalQtiComponent $externalComponent
     */
    private function setExternalComponent(ExternalQtiComponent $externalComponent)
    {
        $this->externalComponent = $externalComponent;
    }

    /**
     * Get the encapsulated external component.
     *
     * @return \qtism\data\ExternalQtiComponent
     */
    private function getExternalComponent()
    {
        return $this->externalComponent;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }
}
