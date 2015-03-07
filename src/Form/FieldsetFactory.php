<?php
/**
 * Factory for fieldsets
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
 * Factory for fieldsets
 *
 * @category   Examinr
 * @package    Form
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class FieldsetFactory
{
    /**
     * @var \Examinr\Form\FieldFactory Factory which builds fields
     */
    private $fieldFactory;

    /**
     * Creates instance
     *
     * @param \Examinr\Form\FieldFactory $fieldFactory Factory which builds fields
     */
    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Builds the fieldset
     *
     * @return \Examinr\Form\Fieldset The built field set
     */
    public function build()
    {
        return new Fieldset($this->fieldFactory);
    }
}
