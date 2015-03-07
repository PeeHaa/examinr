<?php
/**
 * Factory for forms
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

use Examinr\Security\Token;

/**
 * Factory for forms
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Factory
{
    /**
     * @var \Examinr\Security\Token CSRF token handler
     */
    private $csrfToken;

    /**
     * @var \Examinr\Form\FieldsetFactory The field set factory
     */
    private $fieldsetFactory;

    /**
     * @var \PDO A database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \Examinr\Security\Token       $csrfToken       CSRF token handler
     * @param \Examinr\Form\FieldsetFactory $fieldsetFactory The field set factory
     * @param \PDO                          $dbConnection    A database connection
     */
    public function __construct(Token $csrfToken, FieldsetFactory $fieldsetFactory, \PDO $dbConnection)
    {
        $this->csrfToken       = $csrfToken;
        $this->fieldsetFactory = $fieldsetFactory;
        $this->dbConnection    = $dbConnection;
    }

    /**
     * Builds the form
     *
     * @param string $form The form to build
     *
     * @return \Examinr\Form\Form The built form
     */
    public function build($form)
    {
        $class = new \ReflectionClass($form);
        $constructor  = $class->getConstructor();

        if (count($constructor->getParameters()) === 2) {
            return new $form($this->csrfToken, $this->fieldsetFactory->build());
        }

        return new $form($this->csrfToken, $this->fieldsetFactory->build(), $this->dbConnection);
    }
}
