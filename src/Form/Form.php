<?php
/**
 * Interface for forms
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
 * Interface for forms
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Form
{
    /**
     * Binds the request to the form
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request object
     */
    public function bindRequest(Request $request);

    /**
     * Checks whether the form is valid
     *
     * @return bool True when the form is valid
     */
    public function isValid();

    /**
     * Gets the fieldset of the form
     *
     * @return \Examinr\Form\Fieldset The fieldset
     */
    public function getFieldset();
}
