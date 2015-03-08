<?php
/**
 * Task controller
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <pieter@mindwarp.nl>
 * @copyright  Copyright (c) 2015 Mindwarp Rotterdam <http://mindwarp.nl>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Presentation\Controller;

use Symfony\Component\HttpFoundation\Response;
use Examinr\Network\Http\Request;
use Examinr\Presentation\Template\Html;

/**
 * Task controller
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <pieter@mindwarp.nl>
 */
class Task
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
     * Renders the task results
     *
     * @param \Examinr\Presentation\Template\Html $template A HTML template renderer
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function result(Html $template)
    {
        $this->response->setContent($template->renderPage('/task/result.phtml', [
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }
}
