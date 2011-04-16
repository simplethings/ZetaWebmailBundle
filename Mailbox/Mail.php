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

use ezcMail;
use ezcMailFile;
use ezcMailText;
use ezcMailHtmlPart;
use ezcMailFilePart;
use ezcMailMultipart;
use ezcMailMultipartRelated;
use ezcMailAddress;

class Mail extends ezcMail
{
    private $parsed = false;
    private $textPart;
    private $htmlPart;
    private $attachments;

    public function getRecipient()
    {
        // Handle To: undisclosed-recipients;
        if (count($this->to) > 0 && $this->to[0] instanceof ezcMailAddress && isset($this->to[0]->email)) {
            $recipient =  $this->to[0];
            return $recipient;
        } else {
            return new ezcMailAddress($this->headers['envelope-to'][0]);
        }
    }

    private function parseParts()
    {
        if (!$this->parsed) {
            $this->attachments = array();
            if ($this->body instanceof ezcMailMultipart) {
                $parts = $this->extractParts();

                foreach ($parts AS $part) {
                    if ($part instanceof ezcMailText && $part->subType == "plain" && $this->textPart === null) {
                        $this->textPart = $part;
                    } else if ($part instanceof ezcMailText && $part->subType == "html" && $this->htmlPart === null) {
                        $this->htmlPart = $part;
                    } else {
                        $this->attachments[] = $part;
                    }
                }
            } else if ($this->body instanceof ezcMailText && $this->body->subType == "plain") {
                $this->textPart = $this->body;
            } else if ($this->body instanceof ezcMailText && $this->body->subType == "html") {
                $this->htmlPart = $this->body;
            }
            $this->parsed = true;
        }
    }

    public function render($preferredFormat, $showImages = false, $blockImageSrc = false)
    {
        $part = $this->getPart($preferredFormat);

        if ($part->subType == "html") {
            $washtml = new \SimpleThings\ZetaWebmailBundle\Util\washtml(array(
                'charset' => 'UTF-8',
                'allow_remote' => $showImages,
                'blocked_src'  => $blockImageSrc,
            ));
            return $washtml->wash($part->text);
        } else {
            return '<pre>' . $part->text . '</pre>';
        }
    }

    public function getPart($preferredFormat = null)
    {
        if (!$preferredFormat) {
            $preferredFormat = "html";
        }
        $this->parseParts();

        if ($preferredFormat == "html") {
            return ($this->htmlPart) ? $this->htmlPart : $this->textPart;
        } else {
            return ($this->textPart) ? $this->textPart : $this->htmlPart;
        }
    }

    public function hasTextPart()
    {
        $this->parseParts();

        return ($this->textPart !== null);
    }

    public function showPricacyMessage($preferredFormat, $showImages)
    {
        $part = $this->getPart($preferredFormat);
        return ($part->subType == "html" && !$showImages);
    }

    public function hasHtmlPart()
    {
        $this->parseParts();

        return ($this->htmlPart !== null);
    }

    public function hasAttachments()
    {
        $this->parseParts();

        return count($this->attachments) > 0;
    }

    public function getAttachments()
    {
        $parts = $this->extractParts();
        $attach = array();
        foreach ($parts AS $part) {
            if ( $part instanceof ezcMailFile) {
                $attach[$i] = $part;
            }
            $i++;
        }
        return $attach;
    }

    /**
     * @param  int $partNum
     * @return \ezcMailPart
     */
    public function getAttachment($partNum)
    {
        $parts = $this->extractParts();
        if (!isset($parts[$partNum])) {
            throw new \OutOfBoundsException("Accessed part that does not exist in mail.");
        }
        return $parts[$partNum];
    }

    public function formattedSize()
    {
        if ($this->size > 1024 * 1024) {
            return ceil($this->size / 1024 / 1024) . "MB";
        } else {
            return ceil($this->size / 1024) . "KB";
        }
    }

    private function extractParts()
    {
        if ($this->body instanceof ezcMailMultipart) {
            if ($this->body instanceof ezcMailMultipartRelated) {
                $parts = array_merge(array($this->body->getMainPart()), $this->body->getRelatedParts());
            } else {
                $parts = $this->body->getParts();
            }
        } else {
            $parts = array($this->body);
        }
        return $parts;
    }
}