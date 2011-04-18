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

namespace SimpleThings\ZetaWebmailBundle\Mailbox\Loader;

use SimpleThings\ZetaWebmailBundle\Mailbox\MailboxException;
use SimpleThings\ZetaWebmailBundle\Mailbox\ImapMailbox;

class ImapLoader implements MailboxLoader
{
    private $sourceName;
    private $server;
    private $username;
    private $password;
    private $port;
    private $ssl;
    private $timeout;

    /**
     * @var ezcMailImapTransport
     */
    private $imap;

    function __construct($sourceName, $server, $username, $password, $port = null, $ssl = true, $timeout = 2)
    {
        $this->sourceName = $sourceName;
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->ssl = $ssl;
        $this->timeout = $timeout;
    }

        /**
     * Does this mailbox loader contain the given backend source?
     *
     * @param string $source
     * @return bool
     */
    public function contains($source)
    {
        return ($this->sourceName == $source);
    }

    /**
     * Load Mailbox by identifier.
     *
     * The identifier has to be unique accross all used mailbox loaders.
     *
     * @param string|integer
     * @param string|integer Mailbox identifier
     * @return Mailbox
     */
    public function loadMailbox($source, $mailbox)
    {
        if ($this->sourceName != $source) {
            throw MailboxException::unknownSource($source, 'imap');
        }

        return new ImapMailbox($source, $mailbox, $this->getImapTransport($mailbox));
    }

    private function getImapTransport($mailbox)
    {
        if (!$this->imap) {
            $options = new \ezcMailImapTransportOptions();
            $options->ssl = $this->ssl;
            $options->timeout = $this->timeout;

            $this->imap = new \ezcMailImapTransport( $this->server, $this->port, $options );
            $this->imap->authenticate($this->username, $this->password);
            $this->imap->selectMailbox($mailbox);
        }
        return $this->imap;
    }
}