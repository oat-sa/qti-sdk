<?php

namespace qtismtest\common\beans\mocks;

/**
 * Class NotStrictMissingSetterBean
 */
class NotStrictMissingSetterBean
{
    /**
     * @var string
     * @qtism-bean-property
     */
    private $firstName;

    /**
     * @var string
     * @qtism-bean-property
     */
    private $lastName;

    /**
     * @var string
     * @qtism-bean-property
     */
    private $hair;

    /**
     * NotStrictMissingSetterBean constructor.
     *
     * @param $firstName
     * @param $lastName
     * @param $hair
     */
    public function __construct($firstName, $lastName, $hair)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setHair($hair);
    }

    /**
     * @param $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->girstName;
    }

    /**
     * @param $lastName
     * @return string
     */
    public function setLastName($lastName)
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * The setHair method should be public.
     *
     * @param string $hair
     */
    protected function setHair($hair)
    {
        $this->hair = $hair;
    }

    /**
     * @return string
     */
    public function getHair()
    {
        return $this->hair;
    }
}
