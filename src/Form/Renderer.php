<?php
/**
 * Interface for form builders
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

/**
 * Interface for form builders
 *
 * @category   MindwarpLib
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Renderer
{
    /**
     * Renders a form
     *
     * @param \Examinr\Form\Form $form      The form to render
     * @param string             $namespace The namespace to be used for string translations
     *
     * @return string The rendered form
     */
    public function render(Form $form, $namespace);
}
