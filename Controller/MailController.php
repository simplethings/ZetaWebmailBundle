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

namespace SimpleThings\ZetaWebmailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MailController extends Controller
{
    public function listAction()
    {
        $request = $this->get('request');
        $source = $request->get('source');
        $mailbox = $request->get('mailbox');
        $offset = $request->get('offset', 1);
        $limit = $request->get('limit', 20);
        
        $loader = $this->get('simplethings.zetawebmail.loader.'.$source);
        $box = $loader->loadMailbox($source, $mailbox);
        $this->assertAccessAllowed($box);
        $set = $box->getMessageList($offset, $limit);

        $parser = $this->get("simplethings.zetawebmail.mailparser");
        $mails = $parser->parseMail( $set );
        $messageCount = $box->getMessageCount();

        return $this->render("SimpleThingsZetaWebmailBundle:Mail:list.html.twig", array(
            'mails'             => $mails,
            'source'            => $source,
            'mailbox'           => $mailbox,
            'count'             => $messageCount,
            'start'             => $offset,
            'end'               => min($offset + $limit - 1, $messageCount),
            'limit'             => $limit,
            'last'              => (floor($messageCount  / $limit) * $limit) + 1,
            'parent_template'   => $this->container->getParameter('simplethings.zetawebmail.listlayout'),
        ));
    }

    public function viewAction()
    {
        $request = $this->get('request');
        $source = $request->get('source');
        $mailbox = $request->get('mailbox');
        $message = $request->get('mail');
        $preferredFormat = $request->get('format', 'html');
        $showImages = (bool)$request->get('showImages', 0);

        $loader = $this->get('simplethings.zetawebmail.loader.'.$source);
        $box = $loader->loadMailbox($source, $mailbox);
        $this->assertAccessAllowed($box);
        $set = $box->getMessage($message);

        if (count($set) == 0) {
            throw new NotFoundHttpException("Mail not found.");
        }

        $parser = $this->get("simplethings.zetawebmail.mailparser");
        $mails = $parser->parseMail( $set );

        return $this->render('SimpleThingsZetaWebmailBundle:Mail:view.html.twig', array(
            'mail'              => $mails[0],
            'preferredFormat'   => $preferredFormat,
            'showImages'        => $showImages,
            'source'            => $source,
            'mailbox'           => $mailbox,
            'message'           => $message,
        ));
    }

    public function downloadAction()
    {
        $request = $this->get('request');
        $source = $request->get('source');
        $mailbox = $request->get('mailbox');
        $message = $request->get('mail');

        $loader = $this->get('simplethings.zetawebmail.loader.'.$source);
        $box = $loader->loadMailbox($source, $mailbox);
        $this->assertAccessAllowed($box);
        $set = $box->getMessage($message);

        $parser = $this->get("simplethings.zetawebmail.mailparser");
        $mails = $parser->parseMail( $set );

        return new Http\MailResponse($mails);
    }

    public function attachmentAction()
    {
        $request = $this->get('request');
        $source = $request->get('source');
        $mailbox = $request->get('mailbox');
        $message = $request->get('mail');
        $part = $request->get('attachment');

        $loader = $this->get('simplethings.zetawebmail.loader.'.$source);
        $box = $loader->loadMailbox($source, $mailbox);
        $this->assertAccessAllowed($box);
        $set = $box->getMessage($message);

        if (count($set) == 0) {
            throw new NotFoundHttpException("Mail not found.");
        }

        $parser = $this->get("simplethings.zetawebmail.mailparser");
        $mails = $parser->parseMail( $set );

        return Http\MailPartResponse($mails[0]->getAttachment($part));
    }

    private function assertAccessAllowed($box)
    {
        $token = $this->get('security.context')->getToken();
        $user = ($token) ? $token->getUser() : null;
        if ( !$this->get('simplethings.zetawebmail.security')->accessAllowed($box, $user) ) {
            throw new HttpException(403, "Access to mailbox not allowed");
        }
    }

}