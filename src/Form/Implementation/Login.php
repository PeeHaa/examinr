<?php
/**
 * Login form
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
 * Login form
 *
 * @category   Examinr
 * @package    Form
 * @subpackage Implementation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Login extends AbstractForm
{
    /**
     * Sets up the fields of the form
     */
    protected function setupFields()
    {
        $this->fieldset->addField('email', 'text', [
            'required' => true,
            'type'     => 'email',
        ]);

        $this->fieldset->addField('password', 'password', [
            'required' => true,
        ]);

        $this->fieldset->addField('rememberme', 'rememberme', [
        ]);
    }
}
