<?php
/**
 * Represents the remember me cookie
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Storage\Http;

use RandomLib\Generator;
use Examinr\Network\Http\Request;

/**
 * Represents the remember me cookie
 *
 * @category   Examinr
 * @package    Storage
 * @subpackage Http
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class RememberMe implements Cookie
{
    /**
     * @var string The name of the cookie
     */
    const NAME = 'remember_me';

    /**
     * @var string The lifetime of the cookie
     */
    const LIFETIME = 'P30D';

    /**
     * @var array The data of the cookies
     */
    private $cookies;

    /**
     * @var \RandomLib\Generator The random generator
     */
    private $generator;

    /**
     * @var \Examinr\Network\Http\Request The request object
     */
    private $request;

    /**
     * Creates instance
     *
     * @param array                         $cookies   The data of the cookies
     * @param \RandomLib\Generator          $generator The random generator
     * @param \Examinr\Network\Http\Request $request   The request object
     */
    public function __construct(array $cookies, Generator $generator, Request $request)
    {
        $this->cookies   = $cookies;
        $this->generator = $generator;
        $this->request   = $request;
    }

    /**
     * Checks whether the cookie exists
     *
     * @return bool True when it exists
     */
    public function exists()
    {
        return array_key_exists(self::NAME, $this->cookies);
    }

    /**
     * Gets the cookie data
     *
     * @return mixed The parsed cookie data
     */
    public function get()
    {
        return $this->parseData($this->cookies[self::NAME]);
    }

    /**
     * Sets the cookie
     *
     * @param mixed $data       The cookie data
     * @param int   $expiration The expiration timestamp
     */
    public function set($data, $exipration)
    {
        setcookie(self::NAME, $data, $exipration, '/', $this->request->getHost(), $this->request->isSecure(), true);
    }

    /**
     * Invalidate the cookie
     */
    public function invalidate()
    {
        setcookie(self::NAME, '', 1);
    }

    /**
     * Creates the cookie
     *
     * @param int $userId The user id
     *
     * @return array The cookie data
     */
    public function create($userId)
    {
        $data =  base64_encode(json_encode([
            'userId' => $userId,
            'series' => base64_encode($this->generator->generate(32)),
            'token'  => base64_encode($this->generator->generate(32)),
        ]));

        $this->set($data, $this->getExpiration());

        return $this->parseData($data);
    }

    /**
     * Updates the cookie
     *
     * @return array The cookie data
     */
    public function update()
    {
        $oldData = $this->get();

        $data =  base64_encode(json_encode([
            'userId' => $oldData['userId'],
            'series' => base64_encode($oldData['series']),
            'token'  => base64_encode($this->generator->generate(32)),
        ]));

        $this->set($data, $this->getExpiration());

        return $this->parseData($data);
    }

    /**
     * Parses the cookie data
     *
     * @param array $data The raw cookie data
     *
     * @return array The parsed cookie data
     */
    private function parseData($data)
    {
        $data = json_decode(base64_decode($data), true);

        $encodedFields = ['series', 'token'];

        foreach ($data as $key => $value) {
            if (!in_array($key, $encodedFields, true)) {
                continue;
            }

            $data[$key] = base64_decode($value);
        }

        return $data;
    }

    /**
     * Gets the expiration date in UNIX timestamp
     *
     * @return int The expiration date
     */
    private function getExpiration()
    {
        $datetime = new \DateTime();
        $datetime->add(new \DateInterval(self::LIFETIME));

        return $datetime->format('U');
    }
}
