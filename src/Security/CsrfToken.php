<?php
/**
 * CSRF token handler
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Security
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Security;

use RandomLib\Generator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * CSRF token handler
 *
 * @category   Examinr
 * @package    Security
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class CsrfToken implements Token
{
    /**
     * @var \RandomLib\Generator Secure token generator
     */
    private $generator;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface Session object
     */
    private $session;

    /**
     * Creates instance
     *
     * @param \RandomLib\Generator                                       $generator Secure token generator
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session   Session object
     */
    public function __construct(Generator $generator, SessionInterface $session)
    {
        $this->generator = $generator;
        $this->session   = $session;
    }

    /**
     * Gets the token
     *
     * @return string The token
     */
    public function get()
    {
        if (!$this->session->has('csrfToken')) {
            $this->generate();
        }

        return $this->session->get('csrfToken');
    }

    /**
     * Generates token
     */
    public function generate()
    {
        $this->session->set('csrfToken', $this->generator->generate(32));
    }

    /**
     * Validates a supplied token
     *
     * @param string $token The supplied token
     *
     * @return bool True when the token is valid
     */
    public function validate($token)
    {
        return \Examinr\Security\compare($this->get(), $token);
    }
}
