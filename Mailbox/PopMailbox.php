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

namespace SimpleThings\ZetaWebmailBundle\Mailbox;

class PopMailbox implements Mailbox
{
    private $popTransport;
    private $sourceName;
    private $name;

    public function __construct($source, $name, \ezcMailPopTransport $popTransport)
    {
        $this->sourceName = $source;
        $this->name = $name;
        $this->popTransport = $popTransport;
    }

    /**
     * Return name of the source this mailbox is managed from.
     *
     * @return string
     */
    public function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * Return name of the mailbox
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getMessage($messageId)
    {
        return $this->popTransport->fetchByMessageNr($messageId);
    }
    
    public function getMessageCount()
    {
        $messageCount = $messageSize = 0;
        if (!$this->popTransport->status($messageCount, $messageSize)) {
            throw MailboxException::sourceError();
        }
        return $messageCount;
    }

    public function getMessageList($offset = 0, $count = null, $sortBy = self::SORT_DATE, $reverse = true)
    {
        if ($sortBy != self::SORT_DATE) {
            throw new \InvalidArgumentException("POP transport only supports sorting by date.");
        }

        if ($reverse) {
            $offset = $this->getMessageCount() - $offset + 2;
        }
        $set = $this->popTransport->fetchFromOffset($offset, $count, false);
        if ($reverse) {
            $set = array_reverse($set);
        }
        return $set;
    }
}