<?php
/**
 * Project controller
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
use Examinr\Network\Http\Request;
use Examinr\Presentation\Template\Html;

/**
 * Project controller
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Project
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
     * Renders the overview page
     *
     * @param \Examinr\Presentation\Template\Html $template A HTML template renderer
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function overview(Html $template)
    {
        $this->response->setContent($template->renderPage('/project/overview.phtml', [
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }
}
