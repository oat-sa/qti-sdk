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
 * Copyright (c) 2013-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\rendering\qtipl;

use qtism\runtime\rendering\Renderable;

/**
 * Class QtiPLRenderer
 * @package qtism\runtime\rendering\qtipl
 */

class QtiPLRenderer implements Renderable
{

    /**
     * @var string The element opening to where the child elements are written.
     */

    private $openChildElement = '(';

    /**
     * @var string The element closing to where the child elements are written.
     */

    private $closeChildElement = ')';

    /**
     * @var string The element opening to where the attributes are written.
     */

    private $openAttribute = '(';

    /**
     * @var string The element closing to where the attributes are written.
     */

    private $closeAttribute = ')';

    /**
     * Creates a new QtiPLRenderer object.
     */
    public function __construct()
    {
    }

    /**
     * Render a QtiComponent object into another constitution.
     *
     * @param mixed $something Something to render into another consitution.
     * @return mixed The rendered component into another constitution.
     * @throws \qtism\runtime\rendering\RenderingException If something goes wrong while rendering the component.
     */
    public function render($something)
    {
        switch($something->getQtiClassName()) {

            default:
                if (in_array($something->getQtiClassName(), OperatorQtiPLRenderer::getOperatorClassNames())) {
                    return (new OperatorQtiPLRenderer())->render($something);
                } else {
                    return $this->getDefaultRendering($something);
                }
        }
    }

    /**
     * @TODO
     * @return
     */
    public function getDefaultRendering($something) {
        return $something->getQtiClassName() . $this->writeChildElements();
    }

    /**
     * @return string The element opening to where the child elements are written.
     */
    public function getOpenChildElement() {
        return $this->openChildElement;
    }

    /**
     * @return string The element closing to where the child elements are written.
     */
    public function getCloseChildElement() {
        return $this->closeChildElement;
    }

    /**
     * @return string The element opening to where the attributes are written.
     */
    public function getOpenAttributes() {
        return $this->openAttribute;
    }

    /**
     * @return string The element closing to where the attributes are written.
     */
    public function getCloseAttributes() {
        return $this->closeAttribute;
    }

    /**
     * @param array of string $childElements The child elements of the element to render.
     * @return string The child Elements in the open and close child elements
     */
    public function writeChildElements($childElements = []) {

        $childPL = [];

        foreach ($childElements as $ce) {
            $childPL[] = $this->render($ce);
        }

        return $this->getOpenChildElement() . join(", ", $childPL) . $this->getCloseChildElement();
    }

    /**
     * @param array of string $childElements The child elements of the element to render.
     * @return string The child Elements in the open and close child elements
     */
    public function writeAttributes($attributes = []) {

        if (count($attributes) > 0) {

            $attribPL = [];

            foreach ($attributes as $key => $value) {
                $attribPL[] = $key . "=" . $value;
            }

            return $this->getOpenAttributes() . join(", ", $attribPL) . $this->getCloseAttributes();
        }
        else {
            return "";
        }
    }
}