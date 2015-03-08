<?php
/**
 * Edit user form
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Form
 * @subpackage Implementation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Form\Implementation;

use Examinr\Form\AbstractForm;
use Examinr\Security\Token;
use Examinr\Form\Fieldset;

/**
 * Edit user form
 *
 * @category   Examinr
 * @package    Form
 * @subpackage Implementation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class EditUser extends AbstractForm
{
    /**
     * @var \PDO A database connection
     */
    private $dbConnection;

    /**
     * Creates instance
     *
     * @param \TopXS\Security\Token $csrfToken    The CSRF token handler
     * @param \TopXS\Form\Fieldset  $fieldset     The field set of this form
     * @param \PDO                  $dbConnection The database connection
     */
    public function __construct(Token $csrfToken, Fieldset $fieldset, \PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;

        parent::__construct($csrfToken, $fieldset);
    }

    /**
     * Sets up the fields of the form
     */
    protected function setupFields()
    {
        $this->fieldset->addField('name', 'text', [
        ]);

        $this->fieldset->addField('email', 'text', [
            'required' => true,
            'type'     => 'email',
        ]);

        $this->fieldset->addField('password', 'password', [
        ]);

        $this->fieldset->addField('password2', 'password', [
        ]);
    }

    /**
     * Binds stored data to the fields
     *
     * @param int $id The id of the record to bind
     */
    public function bindData($id)
    {
        $query = 'SELECT name, email, password';
        $query.= ' FROM users';
        $query.= ' WHERE id = :id';

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute([
            'id' => $id,
        ]);

        $this->fieldset->bindData($stmt->fetch());
    }
}
