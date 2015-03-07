<?php
/**
 * Form builder
 *
 * This class is responsible for rendering form fields dynamically
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

use Examinr\Presentation\Template\Renderer as TemplateRenderer;
use Examinr\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form builder
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Builder implements Renderer
{
    /**
     * @var \Examinr\Presentation\Template\Renderer A template renderer
     */
    private $template;

    /**
     * Creates instance
     *
     * @param \Examinr\Presentation\Template\Renderer $template A template renderer
     */
    public function __construct(TemplateRenderer $template)
    {
        $this->template = $template;
    }

    /**
     * Renders a form
     *
     * @param \Examinr\Form\Form $form      The form to render
     * @param string             $namespace The namespace to be used for string translations
     *
     * @return string The rendered form
     */
    public function render(Form $form, $namespace)
    {
        foreach ($form->getFieldset() as $name => $field) {
            echo $this->template->render('/form/field-' . $field->getType() . '.phtml', [
                'form'      => $form,
                'name'      => $name,
                'field'     => $field,
                'namespace' => $namespace,
            ]);
        }
    }
}
