<?php

declare(strict_types=1);

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

namespace qtism\common\beans;

use ReflectionException;
use ReflectionParameter;

/**
 * Represents a Bean method parameter.
 */
class BeanParameter
{
    /**
     * The wrapped ReflectionParameter.
     *
     * @var ReflectionParameter
     */
    private $parameter;

    /**
     * Create a new BeanParameter object.
     *
     * @param string $class The class name.
     * @param string $method The method name.
     * @param string $name The parameter name.
     * @throws BeanException If no such parameter exists in $class::$method.
     */
    public function __construct($class, $method, $name)
    {
        try {
            $this->setParameter(new ReflectionParameter([$class, $method], $name));
        } catch (ReflectionException $e) {
            $msg = "No such parameter '${name}' for method '${method}' of class '${class}'.";
            throw new BeanException($msg, BeanException::NO_PARAMETER, $e);
        }
    }

    /**
     * Set the wrapped ReflectionParameter object.
     *
     * @param ReflectionParameter $parameter A ReflectionParameter object.
     */
    protected function setParameter(ReflectionParameter $parameter): void
    {
        $this->parameter = $parameter;
    }

    /**
     * Get the wrapped ReflectionParameter object.
     *
     * @return ReflectionParameter A ReflectionParameter object.
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }

    /**
     * Get the name of the bean parameter.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getParameter()->getName();
    }
}
