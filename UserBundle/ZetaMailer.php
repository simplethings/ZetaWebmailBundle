<?php
/*
 * Copyright 2011 SimpleThings GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace SimpleThings\ZetaWebmailBundle\UserBundle;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class ZetaMailer implements MailerInterface
{
    /**
     * @var ezcMailTransport
     */
    protected $mailTransport;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct($mailTransport, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailTransport = $mailTransport;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user, $engine)
    {
        $template = $this->parameters['confirmation.template'];
        $url = $this->router->generate('fos_user_user_confirm', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template.'.txt.'.$engine, array(
            'user' => $user,
            'confirmationUrl' =>  $url
        ));
        $this->sendEmailMessage($rendered, $this->getSenderEmail('confirmation'), $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user, $engine)
    {
        $template = $this->parameters['resetting_password.template'];
        $url = $this->router->generate('fos_user_user_reset_password', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template.'.txt.'.$engine, array(
            'user' => $user,
            'confirmationUrl' => $url
        ));
        $this->sendEmailMessage($rendered, $this->getSenderEmail('resetting_password'), $user->getEmail());
    }

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        if (strlen($body) == 0 || strlen($subject) == 0) {
            throw new \RuntimeException(
                "No message was found, cannot send e-mail to " . $toEmail.". This " .
                "error can occur when you don't have set a confirmation template or using the default " .
                "without having translations enabled."
            );
        }

        if (is_string($fromEmail)) {
            $from = new \ezcMailAddress($fromEmail);
        } else if (is_array($fromEmail)) {
            $from = new \ezcMailAddress(key($fromEmail), current($fromEmail));
        } else {
            throw new \RuntimeException("Invalid from email format given in user bundle configuration.");
        }

        $message = new \ezcMailComposer();
        $message->from = $from;
        $message->addTo( new \ezcMailAddress($toEmail) );
        $message->subject = $subject;
        $message->subjectCharset = 'UTF-8';
        $message->charset = 'UTF-8';
        if (strpos($body, '<body') === false) {
            $message->plainText = $body;
        } else {
            $message->htmlText = $body;
        }
        $message->build();

        $this->mailTransport->send($message);
    }

    protected function getSenderEmail($type)
    {
        return $this->parameters['from_email'][$type];
    }
}