<?php
/**
 * Factory for fields
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

/**
 * Factory for fields
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class FieldFactory
{
    /**
     * @var \PDO The database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \PDO $dbConnection A database connection
     */
    public function __construct(\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Builds the fieldset
     *
     * @param string $type            The type of the field
     * @param array  $validationRules List of validation rules
     *
     * @return \Examinr\Form\Field The built field set
     */
    public function build($type, $validationRules)
    {
        return new Field($this->dbConnection, $type, $validationRules);
    }
}
