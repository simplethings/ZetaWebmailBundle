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

namespace SimpleThings\ZetaWebmailBundle\Transport;

use ezcMailTransport, ezcMail;

class LoggingTransport implements ezcMailTransport
{
    /**
     * @param ezcMailTransport
     */
    private $transport;
    
    private $messages = array();
    
    public function __construct(ezcMailTransport $transport)
    {
        $this->transport = $transport;
    }
    
    /**
     * Sends the contents of $mail.
     *
     * @param ezcMail $mail
     */
    public function send( ezcMail $mail )
    {
        $ret = $this->transport->send($mail);
        $this->messages[] = clone $mail;
        return $ret;
    }
    
    public function getLoggedMessages()
    {
        return $this->messages;
    }
    
    public function getLoggedMessageCount()
    {
        return count($this->messages);
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->transport, $method), $args);
    }
    
    public function __isset($name)
    {
        return isset($this->transport->$name);
    }
    
    public function __set($name, $value)
    {
        $this->transport->$name = $value;
    }
    
    public function __get($name)
    {
        return $this->transport->$name;
    }
}