<?php

namespace SimpleThings\ZetaWebmailBundle\Tests\Mailbox;

class MailTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRecipientUndisclosedRecipients()
    {
        $mail = <<<MAIL
Return-Path: <johngalt@example.com>
X-Original-To: benny@beberlei.de
Delivered-To: benny@beberlei.de
Message-ID: <110294.79320.qm@web32105.mail.mud.yahoo.com>
Date: Tue, 3 May 2011 10:15:01 -0700 (PDT)
From: "John Galt" <johngalt@example.com>
Reply-To: johngalt@example.com
Subject: Hello!
To: undisclosed recipients: ;
MIME-Version: 1.0
Content-Type: text/plain; charset=us-ascii

hello
   
MAIL;
        
        $options = new \ezcMailParserOptions(array(
            'mailClass' => 'SimpleThings\ZetaWebmailBundle\Mailbox\Mail',
        ));
        $parser = new \ezcMailParser($options);
        $mails = $parser->parseMail(new \ezcMailVariableSet($mail));
        
        $this->assertType('SimpleThings\ZetaWebmailBundle\Mailbox\Mail', $mails[0]);
    }
}