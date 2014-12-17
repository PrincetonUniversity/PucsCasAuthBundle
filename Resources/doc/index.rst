Overview
========

This bundle provides an authentication provider to the Symfony Security Component.
If you are unfamiliar with how authentication and authorization works in Symfony,
`read about it`_.

.. _`read about it`: http://symfony.com/doc/current/book/security.html

Installation
============

Step 1: Install
---------------

In your project's ``composer.json`` file, add this to your require block::

    {
        "require": {
            "pucs/cas-auth-bundle": "dev-master"
        }
    }

And run ``composer update pucs/cas-auth-bundle`` to download the bundle and its dependencies.

Step 2: Enable the Bundle
-------------------------

Enable the bundle and its dependency by adding the following lines in the ``app/AppKernel.php``
file of your project::

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        // ...

        $bundles = array(
            // ...
            new Pucs\CasAuthBundle\PucsCasAuthBundle(),
            new Misd\GuzzleBundle\MisdGuzzleBundle(),
            // ...
        );

        // ...
    }

Notice that we're adding the `guzzle bundle`_.

.. _`guzzle bundle`: https://github.com/misd-service-development/guzzle-bundle

Firewall Configuration
======================

Add the new ``cas`` authentication provider and options to your firewall::

    security:
        // ...
        firewalls:
            my_firewall:
                // ...
                cas:
                    login_path: ~
                    check_path: ~

The only required options are ``login_path`` and ``check_path``.
There are also several other options that are defined by the Security Component that you can specify.
They are listed below, and descriptions of each can be found in the `security documentation`_.

.. _`security documentation`: http://symfony.com/doc/current/reference/configuration/security.html

* ``always_use_default_target_path``
* ``default_target_path``
* ``target_path_parameter``
* ``use_referrer``
* ``failure_path``
* ``failure_path_forward``
* ``failure_path_parameter``
* ``require_previous_session``

This bundle provides extra options as well:

* ``create_users``: When ``true``, users that don't exist (in your ``UserProvider``) will be created during login.

Bundle Configuration
====================

Configure the bundle in your apps configuration file like so::

    pucs_cas_auth:
        server:
            version: ~
            base_server_uri: ~
            ca_validation: ~

``version`` must be either ``1`` or ``2`` and corresponds with your CAS server protocol version.

``base_server_uri`` is the location of your CAS server, like ``https://sample.com/cas``

``ca_validation`` indicates how validation of the TLS certificate of your CAS server is handled.
When ``true``, it will use a default cert chain (that mozilla provides) and verify authenticity of
the certificate as well as the host. When ``false``, no verification is done at all. You can also
specify the path on your server to your own certificate file for validating the certificate authority.

Usage
=====

When an anonymous user enters a path protected by your firewall, they will be redirected
to your CAS server for authentication.

If the user is already logged into the CAS server, the user is immediately redirected back
to your app and will be authenticated locally.

If the user is not logged into the CAS server, the user will first be prompted to log in
to the CAS server, and then will be redirected back to your app to be authenticated
locally.

Events
======

After a user has authenticated with CAS, but before the user is authenticated with your
app locally, an event containing user data is dispatched. The event is an instance of
``CasAuthenticationEvent`` and contains an instance of ``CasLoginData``.

You can subscribe to this kernel event with the name ``pucs.cas_auth.event.authentication``.

Attached to this event is an instance  of ``CasLoginData``, which contains the username
of the authenticated user. You can prevent login by calling the ``setFailure()`` method
on that object.
