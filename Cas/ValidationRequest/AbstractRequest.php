<?php

/*
 * This file is part of the PucsCasAuthBundle package.
 *
 * (c) 2014 The Trustees of Princeton University
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
