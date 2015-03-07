<?php
/**
 * Abstract form. All forms should inherit from this class
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Form;

use Examinr\Form\Form;
use Examinr\Security\Token;
use Examinr\Form\Fieldset;
use Symfony\Component\HttpFoundation\Request;

/**
 * Add client form
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
abstract class AbstractForm implements Form, \ArrayAccess
{
    /**
     * @var \Examinr\Security\Token The CSRF token handler
     */
    private $csrfToken;

    /**
     * @var \Examinr\Form\Fieldset The field set of this form
     */
    protected $fieldset;

    /**
     * @var \Symfony\Component\HttpFoundation\Request The HTTP request
     */
    protected $request;

    /**
     * Creates instance
     *
     * @param \Examinr\Security\Token $csrfToken The CSRF token handler
     * @param \Examinr\Form\Fieldset  $fieldset  The field set of this form
     */
    public function __construct(Token $csrfToken, Fieldset $fieldset)
    {
        $this->csrfToken = $csrfToken;
        $this->fieldset  = $fieldset;

        $this->setupFields();
    }

    /**
     * Sets up the fields of the form
     */
    abstract protected function setupFields();

    /**
     * Binds the request to the form
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object
     */
    public function bindRequest(Request $request)
    {
        $this->request = $request;

        $this->fieldset->bindRequest($request);
    }

    /**
     * Checks whether the form is valid
     *
     * @return bool True when the form is valid
     */
    public function isValid()
    {
        if (!$this->csrfToken->validate(base64_decode($this->request->request->get('csrf-token')))) {
            return false;
        }

        return $this->fieldset->isValid();
    }

    /**
     * Checks whether an offset exists
     *
     * @param mixed $offset An offset to check for
     *
     * @return bool True when the offset exists
     */
    public function offsetExists($offset)
    {
        return $this->fieldset->exists($offset);
    }

    /**
     * Gets a field if it exists
     *
     * @param mixed $offset Offset to retrieve
     *
     * @return null|\Examinr\Form\Field The field if it exists
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->fieldset->getField($offset);
    }

    /**
     * Sets a field
     *
     * Because all the fields are build in the parent class it is not possible to set a field from some other scope.
     * We are throwing up here the user doing this and introducing maintenance nightmares
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value  The value to set
     *
     * @throws \Examinr\Form\OufOfScopeException When trying to use this method
     */
    public function offsetSet($offset, $value)
    {
        throw new OutOfScopeException();
    }

    /**
     * Unsets a field
     *
     * Because all the fields are build in the parent class it is not possible to unset a field from some other scope.
     * We are throwing up here the user doing this and introducing maintenance nightmares
     *
     * @param mixed $offset The offset to unset
     *
     * @throws \Examinr\Form\OufOfScopeException When trying to use this method
     */
    public function offsetUnset($offset)
    {
        throw new OutOfScopeException();
    }

    /**
     * Gets the fieldset of the form
     *
     * @return \Examinr\Form\Fieldset The fieldset
     */
    public function getFieldset()
    {
        return $this->fieldset;
    }
}
