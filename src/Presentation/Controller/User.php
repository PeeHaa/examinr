<?php
/**
 * User controller
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
use Examinr\Storage\MySql\User as Storage;
use Examinr\Auth\User as Auth;
use Examinr\Form\Builder as FormBuilder;
use Examinr\Form\Implementation\EditUser as EditUserForm;

/**
 * User controller
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <pieter@mindwarp.nl>
 */
class User
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
     * Renders the edit profile page
     *
     * @param \Examinr\Presentation\Template\Html $template A HTML template renderer
     * @param \Examinr\Storage\MySql\User         $storage  The user storage
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function overview(Html $template, Storage $storage)
    {
        $this->response->setContent($template->renderPage('/user/overview.phtml', [
            'recordset' => $storage->getOverview(),
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Renders the edit profile page
     *
     * @param \Examinr\Presentation\Template\Html   $template    A HTML template renderer
     * @param \Examinr\Network\Http\Request         $request     The request object
     * @param \Examinr\Auth\User                    $user        The user object
     * @param \Examinr\Form\Implementation\EditUser $form        The edit user form
     * @param \Examinr\Form\Builder                 $formBuilder The form builder
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function info(Html $template, Request $request, Auth $user, EditUserForm $form, FormBuilder $formBuilder)
    {
        if ($request->isMethod('GET')) {
            $form->bindData($user->id);
        }

        $this->response->setContent($template->renderPage('/user/edit.phtml', [
            'form'        => $form,
            'formBuilder' => $formBuilder,
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Handles the edit profile form
     *
     * @param \Examinr\Presentation\Template\Html   $template    A HTML template renderer
     * @param \Examinr\Network\Http\Request         $request     The request object
     * @param \Examinr\Storage\MySql\User           $storage     The user storage
     * @param \Examinr\Auth\User                    $user        The usee object
     * @param \Examinr\Form\Implementation\EditUser $form        The edit user form
     * @param \Examinr\Form\Builder                 $formBuilder The form builder
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function doInfo(
        Html $template,
        Request $request,
        Storage $storage,
        Auth $user,
        EditUserForm $form,
        FormBuilder $formBuilder
    )
    {
        $form->bindRequest($request);

        if (!$form->isValid()) {
            return $this->info($template, $request, $user, $form, $formBuilder);
        }

        $password = null;

        if ($form['password']->getValue()) {
            $password = $user->rehash($form['password']->getValue());
        }

        $storage->update($user->id, $form, $password);

        $this->response->setStatusCode(Response::HTTP_FOUND);
        $this->response->headers->set('Location', $request->getSchemeAndHttpHost());

        return $this->response;
    }
}
