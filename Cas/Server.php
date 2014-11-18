<?php

namespace Pucs\CasAuthBundle\Cas;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class Server
 */
class Server
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var
     */
    protected $version;

    /**
     * @var
     */
    protected $loginUrl;

    /**
     * @var
     */
    protected $logoutUrl;

    /**
     * @var
     */
    protected $validateUrl;

    /**
     * @param RequestStack    $requestStack
     * @param RouterInterface $router
     * @param int             $version
     * @param string          $loginUrl
     * @param string          $logoutUrl
     * @param string          $validateUrl
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router, $version, $loginUrl, $logoutUrl, $validateUrl)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->version = $version;
        $this->loginUrl = $loginUrl;
        $this->logoutUrl = $logoutUrl;
        $this->validateUrl = $validateUrl;
    }

    /**
     * The login URL of the CAS server with the service URL attached.
     *
     * @param string $checkPath
     *
     * @return string
     */
    public function getLoginUrl($checkPath)
    {
        // We need a better way of getting the service path from the Authentication Listener configuration,
        // so we don't have to accept it via a parameter here.
        return sprintf("%s?service=%s", $this->loginUrl, urlencode($this->getServiceUrl($checkPath)));
    }

    /**
     * The logout URL of the CAS server with the service URL attached.
     *
     * @param string $checkPath
     *
     * @return string
     */
    public function getLogoutUrl($checkPath)
    {
        return sprintf("%s?service=%s", $this->logoutUrl, urlencode($this->getServiceUrl($checkPath)));
    }

    /**
     * The validation URL of the CAS server with the service URL attached.
     *
     * @param string $checkPath
     *
     * @return string
     */
    public function getValidateUrl($checkPath)
    {
        return sprintf("%s?service=%s", $this->validateUrl, urlencode($this->getServiceUrl($checkPath)));
    }

    /**
     * Generate the path to our service URL, which is the path that the Security component uses for authentication.
     *
     * @param string $checkPath
     *
     * @return string
     */
    private function getServiceUrl($checkPath)
    {
        // If the service path is a route, we need to generate a path for that route.
        if ($checkPath[0] != '/') {
            $path = $this->router->generate($checkPath, array(), UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $path = $this->requestStack->getCurrentRequest()->getUriForPath($checkPath);
        }

        return $path;
    }
}
