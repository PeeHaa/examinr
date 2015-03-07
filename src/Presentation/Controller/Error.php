<?php
/**
 * Error controller
 *
 * Used to render all error responses
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Presentation\Controller;

use Symfony\Component\HttpFoundation\Response;
use Examinr\Presentation\Template\Html;
use Examinr\Auth\User;

/**
 * Error controller
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Error
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    private $response;

    /**
     * Creates instance
     *
     * @param \Symfony\Component\HttpFoundation\Response $response The HTTP response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Renders the not found page
     *
     * @param \Examinr\Presentation\Template\Html $template A HTML template renderer
     * @param \Examinr\Auth\User                  $user     The user object
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function notFound(Html $template, User $user)
    {
        $templateVars = [];

        if (!$user->isLoggedIn()) {
            $templateVars['barePage'] = true;
        }

        $this->response->setContent($template->renderPage('/error/not-found.phtml', $templateVars));

        $this->response->setStatusCode(Response::HTTP_NOT_FOUND);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Renders the moethod not allowed page
     *
     * @param \Examinr\Presentation\Template\Html $template A HTML template renderer
     * @param \Examinr\Auth\User                  $user     The user object
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function notAllowed(Html $template, User $user)
    {
        $this->notFound($template, $user);

        $this->response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);

        return $this->response;
    }
}
