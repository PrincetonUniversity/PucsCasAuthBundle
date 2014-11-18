<?php

namespace Pucs\CasAuthBundle\Security\Firewall;

use Pucs\CasAuthBundle\Authentication\Token\CasUserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

/**
 * Class CasListener
 */
class CasListener extends AbstractAuthenticationListener
{
    /**
     * {@inheritdoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        // The request should have a ticket query string parameter that CAS server set when redirecting
        // back to the application. Initialize our token with this ticket and try and validate the token.
        $token = new CasUserToken($request->query->get('ticket'), $this->options['check_path']);

        return $this->authenticationManager->authenticate($token);
    }
}
