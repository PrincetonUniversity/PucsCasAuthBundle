<?php

namespace Pucs\CasAuthBundle\Cas\Protocol;

/**
 * Interface ProtocolInterface
 */
interface ProtocolInterface
{
    /**
     * Returns the login URI of the CAS Server.
     *
     * @param string $service
     *
     * @return string
     */
    function getLoginUri($service);

    /**
     * Returns the logout URL of the CAS Server.
     *
     * @param string $service
     *
     * @return string
     */
    function getLogoutUri($service);

    /**
     * Returns the validation URI of the CAS Server.
     *
     * @param string $service
     * @param string $ticket
     *
     * @return string
     */
    function getValidationUri($service, $ticket);
}
