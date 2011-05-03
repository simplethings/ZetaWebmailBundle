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

namespace SimpleThings\ZetaWebmailBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ezcMailTransport;

/**
 * MessageDataCollector.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class MessageDataCollector extends DataCollector
{
    private $transport;
    
    public function __construct(ezcMailTransport $transport = null)
    {
        $this->transport = $transport;
    }
    
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if ($this->transport instanceof \SimpleThings\ZetaWebmailBundle\Transport\LoggingTransport) {
            $this->data['messages'] = $this->transport->getLoggedMessages();
            $this->data['messageCount'] = $this->transport->getLoggedMessageCount();
        } else {
            $this->data['messages'] = array();
            $this->data['messageCount'] = 0;
        }
    }
    
    public function getMessageCount()
    {
        return $this->data['messageCount'];
    }

    public function getMessages()
    {
        return $this->data['messages'];
    }
    
    public function getName()
    {
        return 'zetawebmail';
    }
}