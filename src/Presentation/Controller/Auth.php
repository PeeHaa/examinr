<?php
/**
 * Auth controller
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

use Examinr\Network\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Examinr\Presentation\Template\Html;
use Examinr\Storage\MySql\Auth as Storage;
use Examinr\Auth\User;
use Examinr\Form\Builder as FormBuilder;
use Examinr\Form\Implementation\Csrf as CsrfForm;
use Examinr\Form\Implementation\Login as LoginForm;
use Examinr\Form\Implementation\ForgotPassword as ForgotPasswordForm;
use Examinr\Form\Implementation\ResetPassword as ResetPasswordForm;
use Examinr\I18n\Translator;
use Examinr\Mail\Mailer;
use RandomLib\Generator;

/**
 * Auth controller
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Controller
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Auth
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
     * Renders the log in page
     *
     * @param \Examinr\Presentation\Template\Html $template    The template renderer
     * @param \Examinr\Network\Http\Request       $request     The HTTP request
     * @param \Examinr\Storage\Sql\Auth           $storage     The auth storage
     * @param \Examinr\Auth\User                  $user        The user object
     * @param \Examinr\Form\Implementation\Login  $form        The forgot password form
     * @param \Examinr\Form\Builder               $formBuilder The form builder
     * @param \RandomLib\Generator                $generator   The random generator
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function logIn(
        Html $template,
        Request $request,
        Storage $storage,
        User $user,
        LoginForm $form,
        FormBuilder $formBuilder,
        Generator $generator
    )
    {
        if (isset($_COOKIE['rememberme'])) {
            $rememberMe = json_decode(base64_decode($_COOKIE['rememberme']), true);

            $rememberMeData = $storage->getRememberMe($rememberMe['userId'], base64_decode($rememberMe['series']));

            if ($rememberMeData && base64_decode($rememberMe['token']) === $rememberMeData['token']) {
                $user->logInWithoutPassword($storage->getById($rememberMe['userId']));

                $this->createRememberMeCookie($rememberMe['userId'], $storage, $generator, $request, base64_decode($rememberMe['series']));

                $this->response->setStatusCode(Response::HTTP_FOUND);
                $this->response->headers->set('Location', $request->getSchemeAndHttpHost());

                return $this->response;
            }

            // if we ever hit this it means something went proper wrong and the user might be compromised
            // when this happens we will go full panic mode and invalidate all user state including sessions end cookies
            // never go full retard
            if ($rememberMeData && base64_decode($rememberMe['token']) !== $rememberMeData['token']) {
                $storage->invalidateUser($rememberMe['userId']);
            }
        }

        $this->response->setContent($template->renderPage('/auth/login.phtml', [
            'barePage'    => true,
            'form'        => $form,
            'formBuilder' => $formBuilder,
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Handles the login form
     *
     * @param \Examinr\Presentation\Template\Html $template    The template renderer
     * @param \Examinr\Network\Http\Request       $request     The HTTP request
     * @param \Examinr\Storage\Sql\Auth           $storage     The auth storage
     * @param \Examinr\Auth\User                  $user        The user object
     * @param \Examinr\Form\Implementation\Login  $form        The forgot password form
     * @param \Examinr\Form\Builder               $formBuilder The form builder
     * @param \RandomLib\Generator                $generator   The random generator
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function doLogIn(
        Html $template,
        Request $request,
        Storage $storage,
        User $user,
        LoginForm $form,
        FormBuilder $formBuilder,
        Generator $generator
    )
    {
        $form->bindRequest($request);

        if (!$form->isValid()) {
            return $this->login($template, $form, $formBuilder);
        }

        $userInfo = $storage->getByEmail($request->request->get('email'));

        if ($userInfo) {
            $user->logIn($request->request->get('email'), $request->request->get('password'), $userInfo);
        }

        if ($user->needsRehash()) {
            $storage->updatePasswordById($userInfo['id'], $user->rehash($request->request->get('email')));
        }

        if ($user->isLoggedIn() && $form['rememberme']->getValue()) {
            $this->createRememberMeCookie($userInfo['id'], $storage, $generator, $request, $generator->generate(32));
        }

        $this->response->setStatusCode(Response::HTTP_FOUND);
        $this->response->headers->set('Location', $request->getSchemeAndHttpHost());

        return $this->response;
    }

    /**
     * Creates the remember me cookie
     *
     * @param int                           $userId      The user id
     * @param \Examinr\Storage\Sql\Auth     $storage     The auth storage
     * @param \RandomLib\Generator          $generator   The random generator
     * @param \Examinr\Network\Http\Request $request     The HTTP request
     * @param string                        $seriesToken The series token
     */
    private function createRememberMeCookie(
        $userId,
        Storage $storage,
        Generator $generator,
        Request $request,
        $seriesToken
    )
    {
        $cookieData = [
            'userId' => $userId,
            'series' => $seriesToken,
            'token'  => $generator->generate(32),
        ];

        $storage->rememberMe($cookieData['userId'], $cookieData['series'], $cookieData['token']);

        $datetime = new \DateTime();
        $datetime->add(new \DateInterval('P30D'));

        setcookie(
            'rememberme',
            base64_encode(json_encode([
                'userId' => $cookieData['userId'],
                'series' => base64_encode($cookieData['series']),
                'token'  => base64_encode($cookieData['token']),
            ])),
            $datetime->format('U'),
            '/',
            $request->getHost(),
            $request->isSecure(),
            true
        );
    }

    /**
     * Handles the logout form
     *
     * @param \Examinr\Form\Csrf            $form     The CSRF form
     * @param \Examinr\Network\Http\Request $request  The HTTP request
     * @param \Examinr\Auth\User            $user     The user object
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function doLogOut(CsrfForm $form, Request $request, User $user)
    {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $user->logout();
        }

        $this->response->setStatusCode(Response::HTTP_FOUND);
        $this->response->headers->set('Location', $request->getSchemeAndHttpHost() . '/');

        return $this->response;
    }

    /**
     * Renders the forgot password form
     *
     * @param \Examinr\Presentation\Template\Html         $template    The template renderer
     * @param \Examinr\Form\Implementation\ForgotPassword $form        The forgot password form
     * @param \Examinr\Form\Builder                       $formBuilder The form builder
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function forgotPassword(Html $template, ForgotPasswordForm $form, FormBuilder $formBuilder)
    {
        $this->response->setContent($template->renderPage('/auth/forgot-password.phtml', [
            'barePage'    => true,
            'form'        => $form,
            'formBuilder' => $formBuilder,
        ]));

        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Handles the forgot password form
     *
     * @param \Examinr\Presentation\Template\Html         $template    The template renderer
     * @param \Examinr\I18n\Translator                    $translator  A translator object
     * @param \Examinr\Mail\Mailer                        $mailer      Instance of a mailer class
     * @param \Examinr\Storage\Sql\Auth                   $storage     The auth storage
     * @param \Examinr\Network\Http\Request               $request     The HTTP request
     * @param \Examinr\Form\Implementation\ForgotPassword $form        The forgot password form
     * @param \Examinr\Form\Builder                       $formBuilder The form builder
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function doForgotPassword(
        Html $template,
        Translator $translator,
        Mailer $mailer,
        Storage $storage,
        Request $request,
        ForgotPasswordForm $form,
        FormBuilder $formBuilder
    )
    {
        $form->bindRequest($request);

        if (!$form->isValid()) {
            return $this->forgotPassword($template, $form, $formBuilder);
        }

        $user = $storage->getByEmail($form['email']->getValue());

        if ($user) {
            $token = $storage->setPasswordResetToken($user['id']);

            $mailer->send(
                $translator->translate('forgot-password.mail.subject'),
                [$translator->translate('forgot-password.mail.sender.address') => $translator->translate('forgot-password.mail.sender.name')],
                [$user['email']],
                $template->render('/auth/forgot-password-mail.ptxt', ['token' => $token]),
                $template->render('/auth/forgot-password-mail.phtml', ['token' => $token])
            );
        }

        $this->response->setStatusCode(Response::HTTP_FOUND);
        $this->response->headers->set('Location', $request->getSchemeAndHttpHost() . '/forgot-password/sent');

        return $this->response;
    }

    /**
     * Renders the forgot password sent page
     *
     * @param \Examinr\Presentation\Template\Html $template The template renderer
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function forgotPasswordSent(Html $template)
    {
        $this->response->setContent($template->renderPage('/auth/forgot-password-sent.phtml', ['barePage' => true]));
        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Renders the reset password page
     *
     * @param \Examinr\Presentation\Template\Html        $template    The template renderer
     * @param \Examinr\Storage\Sql\Auth                  $storage     The auth storage
     * @param \Examinr\Form\Implementation\ResetPassword $form        The forgot password form
     * @param \Examinr\Form\Builder                      $formBuilder The form builder
     * @param string                                     $token       The token
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function resetPassword(
        Html $template,
        Storage $storage,
        ResetPasswordForm $form,
        FormBuilder $formBuilder,
        $token
    )
    {
        if ($storage->isTokenValid($token)) {
            $this->response->setContent($template->renderPage('/auth/reset-password.phtml', [
                'barePage'    => true,
                'form'        => $form,
                'formBuilder' => $formBuilder,
                'token'       => $token,
            ]));
            $this->response->setStatusCode(Response::HTTP_OK);
        } else {
            $this->response->setContent($template->renderPage('/auth/reset-password-invalid.phtml', ['barePage' => true]));
            $this->response->setStatusCode(Response::HTTP_OK);
        }

        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }

    /**
     * Handles the reset password form
     *
     * @param \Examinr\Presentation\Template\Html $template The template renderer
     * @param \Examinr\Storage\Sql\Auth           $storage  The auth storage
     * @param \Examinr\Auth\User                  $user     The user object
     * @param \Examinr\Network\Http\Request       $request The HTTP request
     * @param \Examinr\Form\Admin\ResetPassword   $form     The reset password form
     * @param string                              $token    The token
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function doResetPassword(
        Html $template,
        Storage $storage,
        User $user,
        Request $request,
        ResetPasswordForm $form,
        $token
    )
    {
        $form->bindRequest($request);

        if (!$form->isValid() || !$storage->isTokenValid($token) || $request->request->get('password') !== $request->request->get('password2')) {
            return $this->resetPassword($template, $storage, $token);
        }

        $storage->updatePasswordByToken($token, $user->rehash($request->request->get('password')));

        $this->response->setStatusCode(Response::HTTP_FOUND);
        $this->response->headers->set('Location', $request->getSchemeAndHttpHost() . '/reset-password/success');

        return $this->response;
    }

    /**
     * Renders the reset password success page
     *
     * @param \Examinr\Presentation\Template\Html $template The template renderer
     *
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function resetPasswordSuccess(Html $template)
    {
        $this->response->setContent($template->renderPage('/auth/reset-password-success.phtml', ['barePage' => true]));
        $this->response->setStatusCode(Response::HTTP_OK);
        $this->response->headers->set('Content-Type', 'text/html');

        return $this->response;
    }
}
