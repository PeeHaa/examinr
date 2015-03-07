<?php
/**
 * Mailer class
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Mail
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Mail;

/**
 * Mailer class
 *
 * @category   Examinr
 * @package    Mail
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Mailer
{
    /**
     * @var \Swift_Mailer Instance of a swift mailer
     */
    private $mailer;

    /**
     * Creates instance
     *
     * @param \Swift_Mailer $mailer Instance of a swift mailer
     */
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends an email message
     *
     * @param string $subject   The subject of the mail
     * @param array  $from      The from address
     * @param array  $to        The to address
     * @param string $plainBody The plain text body
     * @param string $htmlBody  The HTML body
     */
    public function send($subject, array $from, array $to, $plainBody, $htmlBody)
    {
        $message = \Swift_Message::newInstance($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($plainBody, 'text/plain')
            ->addPart($htmlBody, 'text/html')
        ;

        $this->mailer->send($message);
    }
}
