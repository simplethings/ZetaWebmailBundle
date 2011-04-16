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

namespace SimpleThings\ZetaWebmailBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SimpleThings\ZetaWebmailBundle\Mailbox\Mailbox;

/**
 * Every use with ROLE_ZETAMAIL has access.
 */
class ZetaMailRoleSecurity implements MailboxSecurity
{
    /**
     * @var SecurityContextInterface
     */
    private $context;

    public function __construct(SecurityContextInterface $context)
    {
        $this->context = $context;
    }

    public function accessAllowed(Mailbox $mailbox, UserInterface $user = null)
    {
        return $this->context->isGranted("ROLE_ZETAMAIL");
    }
}