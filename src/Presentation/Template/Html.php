<?php
/**
 * HTML template renderer
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
use Examinr\Security\Token;
use Examinr\Auth\User;
use Examinr\I18n\Translator;
use Examinr\Network\Http\Request;

/**
 * HTML template renderer
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Template
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Html implements Renderer
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
     * @var string The base (skeleton) page template
     */
    private $basePage;

    /*
     * @var \Examinr\Security\Token The CSRF token handler
     */
    private $csrfToken;

    /**
     * @var \Examinr\Auth\User The user object
     */
    private $user;

    /**
     * @var \Examinr\I18n\Translator Instance of a translator
     */
    private $translator;

    /**
     * @var \Examinr\Network\Http\Request The HTTP request
     */
    private $request;

    /**
     * Creates instance
     *
     * @param \Examinr\Presentation\Theme   $theme         Theme loader
     * @param string                        $basePage      The base (skeleton) page template
     * @param \Examinr\Security\Token       $csrfToken     The CSRF token handler
     * @param \Examinr\Auth\User            $user          The user object
     * @param \Examinr\I18n\Translator      $translator    Instance of a translator
     * @param \Examinr\Network\Http\Request $request       The HTTP request
     */
    public function __construct(
        Loader $theme,
        $basePage,
        Token $csrfToken,
        User $user,
        Translator $translator,
        Request $request
    )
    {
        $this->theme         = $theme;
        $this->basePage      = $basePage;
        $this->csrfToken     = $csrfToken;
        $this->user          = $user;
        $this->translator    = $translator;
        $this->request       = $request;
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
     * Renders a page
     *
     * @param string $template The template to render
     * @param array  $data     The template variables
     *
     * @return string The rendered page
     */
    public function renderPage($template, array $data = [])
    {
        $content = $this->render($template, $data);

        ob_start();

        require $this->theme->load($this->basePage);

        return ob_get_clean();
    }

    /**
     * Escapes the data used in templates
     *
     * @param string $data The data to escape
     *
     * @return string The escaped data
     */
    private function escape($data)
    {
        return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Normlizes data to be used in URIs
     *
     * @param string $data The data to normalize
     *
     * @return string The normalized data
     */
    private function urlNormalize($data)
    {
        $data = strtolower($data);

        $replacements = [
            ' ' => '-',
            '(' => '',
            ')' => '',
        ];

        return str_replace(array_keys($replacements), $replacements, $data);
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

    /**
     * Translates a given key
     *
     * @param string $key  The key to translate
     * @param array  $data The data to use to fill in dynamic parts of the translations
     *
     * @return string The translated string
     */
    private function translate($key, array $data = [])
    {
        return $this->translator->translate($key, $data);
    }
}
