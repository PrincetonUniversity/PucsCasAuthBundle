<?php

namespace Pucs\CasAuthBundle\Cas\ValidationRequest;

/**
 * Class AbstractRequest
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $caPem;

    /**
     * @param string $caPem
     */
    public function __construct($caPem)
    {
        $this->caPem = $caPem;
    }
}
