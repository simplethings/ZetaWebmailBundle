# Zeta Webmail Bundle

A flexible Mail bundle for [Symfony2](http://www.symfony.com) for sending and displaying purposes
You can show mailbox contents and mails loaded from arbitrary sources. Zeta Components is used to parse mails and
access IMAP/POP sources, a simple interface is provided to allow any source of mails such as
a database or the filesystem.

Zeta Components sending and composing capabilities are also leveraged, a transporting service is provided,
making this bundle a dropin replacement for SwiftMailerBundle.

The transporting service is integrated into the WebDebugToolbar and shows the number of sent mail
per request and shows their headers. Zeta Webmail Bundle ships with a NullTransport that can be
used for sending in development.

## Features

* Send mails using transport services (SwiftMailerBundle replacement)
* Integration into WebDebugToolbar
* Mailer for FOS\UserBundle (SwiftMailerBundle replacement)
* List Mails from Imap/Pop accounts (pagination included)
* Add arbitrary backend that provides mails.
* Download mail as .eml to open with associated Outlook, Thunderbird or other mail clients.
* View HTML, Text and Multipart Mails
* HTML XSS Injection prevented by Washtml library
* Privacy protected by not displaying images in html mails by default.
* Security abstraction to configure access to mail sources and mailboxes.
* Interactive Example using reusable jQuery plugin (list_layout: SimpleThingsZetaWebmailBundle::interactive.html.twig)

## Installation

Install Zeta Components

Currently this has still to be done through the old ezcomponents.org PEAR channel:

    pear channel-discover pear.ezcomponents.org
    pear install ezc/Mail

Add the following lines to your autoload.php:

    require_once "ezc/Base/base.php";
    spl_autoload_register(array("ezcBase", "autoload"));

Download this bundle into vendor/bundles/SimpleThings/ZetaWebmail:

    git clone git://github.com/simplethings/ZetaWebmailBundle.git vendor/bundles/SimpleThings/ZetaWebmail

Register Autoloading namespace SimpleThings into app/autoload.php

Add Bundle to app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            //..
            new SimpleThings\ZetaWebmail\SimpleThingsZetaWebmailBundle(),
            //..
        );
        return $bundles;
    }

Configure bundle in app/config.yml

    simple_things_zeta_webmail:
      security: admin_party
      list_layout: SimpleThingsZetaWebmailBundle::standalone.html.twig
      sources:
        gmail:
          type: imap
          host: imap.gmail.com
          username: xxx@gmail.com
          password: s3cr3t
          ssl: true
        other:
          type: pop
          host: pop.foo.de
          username: user
          password: s3cr3t
          ssl: true

You can also add the following routing files for the mailing viewer and the composer:

    zeta_webmail:
        resource: "@SimpleThingsZetaWebmailBundle/Resources/config/routing.yml"
        prefix: /zeta

    zeta_webmail_composer:
        resource: "@SimpleThingsZetaWebmailBundle/Resources/config/routing_composer.yml"
        prefix: /zeta

## Loaders

To add your own source for mails implement `SimpleThings\ZetaWebmail\Mailbox\Loader\MailboxLoader` and
`SimpleThings\ZetaWebmail\Mailbox`. You can then register this loader by specifying its service-id
in the "type" key of your source:

    simple_things_zeta_webmail:
      security: admin_party
      sources:
        test:
          type: my.zetawebmail.loader.service

Be aware that the pagination in the Mailbox interface is based on message numbers, i.e. ascending
by date starting with message number 1 and ending with message number equaling the message count.

## Security

### Access to mails

To protect mails from being read without proper access there is a very small abstraction layer for security
built into the Webmail bundle. By default there are two very simple security roles shipped with the bundle:

* admin_party - Everybody is allowed to read all sources, mailboxes and mails.
* zeta_mail_role - Only users with the role "ROLE_ZETAMAIL" can view all sources and their mailboxxes.

### XSS Prevention (HTML-Mails)

This bundle uses [washtml](http://ubixis.com/washtml/) to sanitize HTML mail content before displaying.
Not the original code but an object-oriented modification written by [Roundcube](http://www.roundcube.net) developers
is used for this task.

### HTML Images

By default images are not displayed and replaced with an empty local image. A message box appears
on top of the mailing and allows users to decide to display the images or not.

## Integrate into your Application

There are two routes that you can use to integrate either a list of mails or a view of a mail into
your application:

* simplethings_zetawebmail_list_mail with parameters "source", "mailbox"
* simplethings_zetawebmail_view_mail with parameters "source", "mailbox", "mail"

The view mail route should ALWAYS be contained into an iframe, since otherwise HTML mails will render themselves
into your application layout. I suggest using at least 600 width for the iframe, which a standard size
for preview windows.

The list view does not use a layout itself so you can use the {% render %} command from Twig to
display this as an widget where ever you please.

## Sending

To send mails you have to configure the transport to send with:

    simple_things_zeta_webmail:
        transport:
            type: smtp
            host: localhost
            user: foo
            password: bar
            port: 1234

You can access this service by accessing:

    $mail = new \ezcMailComposer();
    // compose the mail here...

    $transport = $container->get("simplethings.zetawebmail.transport");
    $transport->send($mail);

The following transports are shipped:

* smtp - SMTP Transport (ezcMailSmtpTransport)
* mta - MTA Transport (ezcMailMtaTransport)
* null - Send nothing (good for development). MUST BE PUT IN QUOTES

## Using Zeta Components with FOS\UserBundle

The FOS\UserBundle has an abstraction layer for mails that are sent for confirmation and reset password
purposes. The ZetaWebmailBundle ships with a replacement of the default SwiftMailer implementation.

Just put the following in your app/config/config.yml to get rid of Swiftmailer:

    fos_user:
      # ...
      service:
        mailer: simplethings.zetawebmail.fosuser.mailer

This service uses the transport service defined before for sending mails.

## TODO

* View source of mail action.
* Make sorting of subject, from, to configurable for developers and for webmail users.
* Add functionality for sorting of messages by criteria to mailbox interface.
* Stream download of mails and attachments?
* Add write support: Read Status, Marking as read/unread, delete, move between mailboxes in a source.
* Allow to hook into operations on mails to allow application specific workflows (such as Add To address book)
