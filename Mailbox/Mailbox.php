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

/**
 * A mailbox
 */
interface Mailbox
{
    /**
     * Does this mailbox have subfolders and allows to traverse them?
     *
     * @return bool
     */
    public function hasFolders();

    /**
     * @param int
     * @param int
     * @return ezcMailParserSet
     */
    public function getMessageList($offset = 0, $count = null);

    /**
     * @param string|int
     * @return ezcMailParserSet
     */
    public function getMessage($messageId);


    /**
     * Get total count of the mailbox.
     *
     * @param string|integer
     * @param string|integer Mailbox identifier
     * @return int
     */
    public function getMessageCount();
}