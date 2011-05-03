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

class ComposerController extends Controller
{
    public function formAction()
    {
        // Create a new mail composer object
        $mail = new \ezcMailComposer();
        $mail->from = new \ezcMailAddress( 'root' );
        $mail->addTo( new \ezcMailAddress( 'root' ) );
        $mail->subject = "This is the subject of the example mail";
        $mail->plainText = "This is the body of the example mail.";
        $mail->build();

        $transport = $this->container->get('simplethings.zetawebmail.transport');
        $transport->send($mail);
        
        return $this->render("SimpleThingsZetaWebmailBundle:Composer:form.html.twig", array());
    }
}