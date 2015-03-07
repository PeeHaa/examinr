<?php
/**
 * This class adds some "utility" methods to the Symfony HTTP request class
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Network
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Network\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * This class adds some "utility" methods to the Symfony HTTP request class
 *
 * @category   Examinr
 * @package    Network
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Request extends SymfonyRequest
{
    /**
     * Checks whether the URI path starts with a pattern
     *
     * @param string $pattern The pattern to match against
     *
     * @return bool True when the URI path starts with the pattern
     */
    public function startsWith($pattern)
    {
        return strpos($this->getPathInfo(), $pattern) === 0;
    }

    /**
     * Checks whether the URI path matches a pattern
     *
     * @param string $pattern The pattern to match against
     *
     * @return bool True when the URI path matches the pattern
     */
    public function matches($pattern)
    {
        return $this->getPathInfo() === $pattern;
    }
}
