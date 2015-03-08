<?php
/**
 * Add user form
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

/**
 * Add user form
 *
 * @category   Examinr
 * @package    Form
 * @subpackage Implementation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class AddUser extends AbstractForm
{
    /**
     * Sets up the fields of the form
     */
    protected function setupFields()
    {
        $this->fieldset->addField('name', 'text', [
            'required' => true,
        ]);

        $this->fieldset->addField('email', 'text', [
            'required' => true,
            'type'     => 'email',
            'unique'   => ['users' => 'email'],
        ]);

        $this->fieldset->addField('password', 'password', [
            'required' => true,
        ]);

        $this->fieldset->addField('password2', 'password', [
            'required' => true,
        ]);
    }
}
