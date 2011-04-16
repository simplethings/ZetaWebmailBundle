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

namespace SimpleThings\ZetaWebmailBundle\Http;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MailResponse extends Response
{
    public function __construct(array $mails)
    {
        if (count($mails) == 0) {
            throw new NotFoundHttpException("Mail not found.");
        }

        $raw = $mails[0]->generateHeaders() . "\n\n" . $mails[0]->generateBody();
        $filename = preg_replace('(([^a-zA-Z0-9]+))', '', str_replace(" ", "_", $mails[0]->subject));
        parent::__construct($raw, 200, array(
            'Content-Type' => 'application/vnd.ms-outlook',
            'Content-disposition' => 'attachment; filename=' . $filename . '.eml'
        ));
    }
}