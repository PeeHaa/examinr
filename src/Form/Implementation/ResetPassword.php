<?php
/**
 * Reset password form
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
 * Reset password form
 *
 * @category   Examinr
 * @package    Form
 * @subpackage Implementation
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class ResetPassword extends AbstractForm
{
    /**
     * Sets up the fields of the form
     */
    protected function setupFields()
    {
        $this->fieldset->addField('password', 'password', [
            'required' => true,
        ]);

        $this->fieldset->addField('password2', 'password', [
            'required' => true,
        ]);
    }
}
