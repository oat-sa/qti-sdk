<?php

namespace qtismtest\common\beans\mocks;

/**
 * Class SimpleBean
 *
 * @package qtismtest\common\beans\mocks
 */
class SimpleBean
{
    /**
     * The name of the SimpleBean object.
     *
     * @var string
     * @qtism-bean-property
     */
    private $name;

    /**
     * The car of the SimpleBean object.
     *
     * @var string
     * @qtism-bean-property
     */
    private $car;

    /**
     * A useless property for testing purpose.
     *
     * @var string
     */
    private $uselessProperty;

    /**
     * A property reported as a bean-property but with
     * no actual setter/getter.
     *
     * @var string
     * @qtism-bean-property
     */
    private $noGetter;

    /**
     * Another useless property because its getter is private.
     *
     * @var string
     */
    private $anotherUselessProperty;

    /**
     * SimpleBean constructor.
     *
     * @param $name
     * @param $car
     * @param string $uselessProperty
     */
    public function __construct($name, $car, $uselessProperty = '')
    {
        $this->setName($name);
        $this->setCar($car);
        $this->setUselessProperty($uselessProperty);
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $car
     */
    public function setCar($car)
    {
        $this->car = $car;
    }

    /**
     * @return string
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param $uselessProperty
     */
    public function setUselessProperty($uselessProperty)
    {
        $this->uselessProperty = $uselessProperty;
    }

    /**
     * @return string
     */
    public function getUselessProperty()
    {
        return $this->uselessProperty;
    }

    /**
     * @param $anotherUselessProperty
     */
    private function setAnotherUselessProperty($anotherUselessProperty)
    {
        $this->anotherUselessProperty = $anotherUselessProperty;
    }

    /**
     * @return string
     */
    public function getAnotherUselessProperty()
    {
        return $this->anotherUselessProperty;
    }
}
