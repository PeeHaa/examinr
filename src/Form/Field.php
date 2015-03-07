<?php
/**
 * This class represent a field in the fieldset
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 *
 * @todo       Create separate classes for different fields and split the validation out
 *             and make it suck less in general
 */
namespace Examinr\Form;

/**
 * This class represent a field in the fieldset
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Field
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * @var string The type of the field
     */
    private $type;

    /**
     * @var array List of validation rules
     */
    private $validationRules;

    /**
     * @var array List of validation errors
     */
    private $errors = [];

    /**
     * @var string The value of the field
     */
    private $value = null;

    /**
     * Creates instance
     *
     * @param \PDO   $dbConnection    A database connection
     * @param string $type            The type of the field
     * @param array  $validationRules List of validation rules
     */
    public function __construct(\PDO $dbConnection, $type, array $validationRules = [])
    {
        $this->dbConnection    = $dbConnection;
        $this->type            = $type;
        $this->validationRules = $validationRules;
    }

    /**
     * Gets the type of the field
     *
     * @return string The type of the field
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Checks whether the field is required
     *
     * @return bool True when the field is required
     */
    public function isRequired()
    {
        return isset($this->validationRules['required']);
    }

    /**
     * Gets the field options
     *
     * @return array The field options
     */
    public function getOptions()
    {
        if (!isset($this->validationRules['options'])) {
            return [];
        }

        return $this->validationRules['options'];
    }

    /**
     * Sets the field's value
     *
     * @param string $value The value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the field's value
     *
     * @return string The value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Validates the field
     *
     * @param string $value The value to validate
     *
     * @return bool True when the value is valid
     */
    public function isValid()
    {
        $value = $this->value;

        if (isset($this->validationRules['required']) && $this->validationRules['required'] === true && ($value === '' || $value === null)) {
            $this->errors['required'] = [];

            return false;
        }

        if (isset($this->validationRules['unique']) && !$this->isUnique($this->validationRules['unique'])) {
            $this->errors['unique'] = [];

            return false;
        }

        if (isset($this->validationRules['options']) && !array_key_exists($value, $this->validationRules['options'])) {
            $this->errors['options'] = $this->validationRules['options'];

            return false;
        }

        if (isset($this->validationRules['type']) && $this->validationRules['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors['type.email'] = [];

            return false;
        }

        if (isset($this->validationRules['type']) && $this->validationRules['type'] === 'decimal' && !is_numeric($value)) {
            $this->errors['type.decimal'] = [];

            return false;
        }

        if (isset($this->validationRules['min']) && $value < $this->validationRules['min']) {
            $this->errors['min'] = $this->validationRules['min'];

            return false;
        }

        if (isset($this->validationRules['max']) && $value > $this->validationRules['max']) {
            $this->errors['max'] = $this->validationRules['max'];

            return false;
        }

        return true;
    }

    /**
     * Checks whether the value is unique
     *
     * @param array $column In the format (key => value) table => column
     *
     * @return bool True when the data is unique
     */
    private function isUnique(array $column)
    {
        $query = 'SELECT COUNT(id) FROM ' . key($column) . ' WHERE ' . reset($column) . ' = :value';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'value' => $this->getValue(),
        ]);

        return !$stmt->fetchColumn(0);
    }

    /**
     * Checks whether there were validation errors
     *
     * @return bool True when there were validation errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Gets the error type
     *
     * @return array The error type
     */
    public function getErrorType()
    {
        return key($this->errors);
    }

    /**
     * Gets the error data
     *
     * @return array The error data
     */
    public function getErrorData()
    {
        return $this->errors;
    }
}
