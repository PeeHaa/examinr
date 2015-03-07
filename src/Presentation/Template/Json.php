<?php
/**
 * JSON template renderer
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Presentation\Template;

use Examinr\Presentation\Theme\Loader;

/**
 * JSON template renderer
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Json implements Renderer
{
    /**
     * @var array List of template variables
     */
    private $variables = [];

    /**
     * @var \Examinr\Presentation\Theme Theme loader
     */
    private $theme;

    /**
     * Creates instance
     *
     * @param \Examinr\Presentation\Theme $theme Theme loader
     */
    public function __construct(Loader $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Renders a template
     *
     * @param string $template The template to render
     * @param array  $data     The template variables
     *
     * @return string The rendered template
     */
    public function render($template, array $data = [])
    {
        if (!empty($data)) {
            $this->variables = $data;
        }

        ob_start();

        require $this->theme->load($template);

        return ob_get_clean();
    }

    /**
     * Magic setter
     *
     * @param mixed $key   The key of the variable
     * @param mixed $value The value of the variable
     *
     * @todo This method may be removed if it turns out to be not needed in the future
     */
    public function __set($key, $value)
    {
        $this->variables[$key] = $value;
    }

    /**
     * Magic getter
     *
     * @param mixed $key The key of the variable
     *
     * @return mixed The value of the variable
     *
     * @throws \Examinr\Presentation\Template\UndefinedVariableException
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->variables)) {
            throw new UndefinedVariableException('Undefined template variable (`' . $key . '`).');
        }

        return $this->variables[$key];
    }

    /**
     * Magic isset
     *
     * @param mixed $key The key of the variable
     *
     * @return bool True when the variable is set
     */
    public function __isset($key)
    {
        return isset($this->variables[$key]);
    }
}
