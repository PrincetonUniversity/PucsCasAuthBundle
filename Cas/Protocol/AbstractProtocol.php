<?php

namespace Pucs\CasAuthBundle\Cas\Protocol;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AbstractProtocol
 */
abstract class AbstractProtocol implements ProtocolInterface
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
     * @var string
     */
    protected $baseServerUri;

    /**
     * @param RequestStack    $requestStack
     * @param RouterInterface $router
     * @param string          $baseServerUri
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router, $baseServerUri)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->baseServerUri = rtrim($baseServerUri, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUri($service)
    {
        $fullServiceUrl = $this->getCompleteServiceUri($service);

        return $this->buildUri('login', array('service' => $fullServiceUrl));
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutUri($service)
    {
        $fullServiceUrl = $this->getCompleteServiceUri($service);

        return $this->buildUri('logout', array('service' => $fullServiceUrl));
    }

    /**
     * {@inheritdoc}
     */
    public abstract function getValidationUri($ticket, $service);

    /**
     * Construct a URL to the CAS server for the provided action.
     *
     * @param string $action
     * @param string $params
     *
     * @return string
     */
    protected function buildUri($action, $params)
    {
        return $this->baseServerUri . '/' . $action . '?' . http_build_query($params);
    }

    /**
     * Convert the passed in service path or route to complete URI
     *
     * @param string $service
     *
     * @return string
     */
    protected function getCompleteServiceUri($service)
    {
        // If the service path is a route, we need to generate a path for that route.
        if ($service[0] != '/') {
            return $this->router->generate($service, array(), UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            return $this->requestStack->getCurrentRequest()->getUriForPath($service);
        }
    }
}
