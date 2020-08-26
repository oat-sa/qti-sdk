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
use qtism\common\utils\Version;
use qtism\data\content\FlowStaticCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for Prompt elements of the content model.
 */
class PromptMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $fqClass = $this->lookupClass($element);
        $component = new $fqClass();

        $content = new FlowStaticCollection();
        $error = false;
        $exclusion = ['pre', 'hottext', 'printedVariable', 'templateBlock', 'templateInline', 'infoControl', 'feedbackBlock', 'rubricBlock', 'feedbackInline'];

        if (Version::compare($this->getVersion(), '2.2.0', '<')) {
            $exclusion[] = 'a';
        }

        foreach ($children as $c) {
            if (in_array($c->getQtiClassName(), $exclusion) === true) {
                $error = true;
                break;
            }

            try {
                $content[] = $c;
            } catch (InvalidArgumentException $e) {
                $error = true;
                break;
            }
        }

        if ($error === true) {
            $qtiClass = $c->getQtiClassName();
            $msg = "A 'prompt' cannot contain '${qtiClass}' elements.";
            throw new UnmarshallingException($msg, $element);
        }

        $component->setContent($content);

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->fillElement($element, $component);

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
