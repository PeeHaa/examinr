<?php
/**
 * Fieldsets of forms
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

use Symfony\Component\HttpFoundation\Request;

/**
 * Fieldsets of forms
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Fieldset implements \Iterator
{
    /**
     * @var \Examinr\Form\FieldFactory Factory which builds fields
     */
    private $fieldFactory;

    /**
     * @var array List of the fields in this fieldset
     */
    private $fields = [];

    /**
     * Creates instance
     *
     * @param \Examinr\Form\FieldFactory $fieldFactory Factory which builds fields
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Adds a field to the field set
     *
     * @param string $name            The name of the field
     * @param string $type            The type of the field
     * @param array  $validationRules The validation rules to apply to the field when submitted
     */
    public function addField($name, $type, array $validationRules = [])
    {
        $this->fields[$name] = $this->fieldFactory->build($type, $validationRules);
    }

    /**
     * Checks whether a field exists
     *
     * @param string $key The name of the field
     *
     * @return bool True when the field exists
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->fields);
    }

    /**
     * Gets a field
     *
     * @param string $key The name of the field
     *
     * @return \Examinr\Form\Field The field
     */
    public function getField($key)
    {
        return $this->fields[$key];
    }

    /**
     * Binds the request data to the fields
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object
     */
    public function bindRequest(Request $request)
    {
        foreach ($this->fields as $name => $field) {
            if (!$request->request->has($name)) {
                continue;
            }

            $this->fields[$name]->setValue($request->request->get($name));
        }
    }

    /**
     * Binds the stored data to the fields
     *
     * @param array $data The stored data
     */
    public function bindData(array $data)
    {
        foreach ($this->fields as $name => $field) {
            if (!array_key_exists($name, $data)) {
                continue;
            }

            $this->fields[$name]->setValue($data[$name]);
        }
    }

    /**
     * Checks whether the fieldset is valid
     *
     * @return bool True when the form is valid
     */
    public function isValid()
    {
        $valid = true;

        foreach ($this->fields as $name => $field) {
            if (!$this->fields[$name]->isValid()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Gets the current field
     *
     * @return array The current field
     */
    public function current()
    {
        return current($this->fields);
    }

    /**
     * Gets the current key
     *
     * @return string The current key
     */
    public function key()
    {
        return key($this->fields);
    }

    /**
     * Advances the pointer to the next field
     */
    public function next()
    {
        next($this->fields);
    }

    /**
     * Resets the pointer to the first field
     */
    public function rewind()
    {
        reset($this->fields);
    }

    /**
     * Checks whether the pointer is valid
     *
     * @return bool true when the pointer is valid
     */
    public function valid()
    {
        return key($this->fields) !== null;
    }
}
