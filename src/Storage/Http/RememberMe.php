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
     * @var string $key The key used to sign the cookie
     */
    private $key;

    /**
     * Creates instance
     *
     * @param array                         $cookies   The data of the cookies
     * @param \RandomLib\Generator          $generator The random generator
     * @param \Examinr\Network\Http\Request $request   The request object
     * @param string                        $key       The key used to sign the cookie
     */
    public function __construct(array $cookies, Generator $generator, Request $request, $key)
    {
        $this->cookies   = $cookies;
        $this->generator = $generator;
        $this->request   = $request;
        $this->key       = $key;
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
     * Checks whether the cookie is valid
     *
     * @return bool True when the cookie is valid
     */
    public function isValid()
    {
        if (!$this->exists()) {
            return false;
        }

        list($data, $signature) = explode(':', $this->cookies[self::NAME]);

        return $this->isSignatureValid($data, $signature);
    }

    /**
     * Gets the cookie data
     *
     * @return mixed The parsed cookie data
     */
    public function get()
    {
        $data = explode(':', $this->cookies[self::NAME]);

        return $this->parseData($data[0]);
    }

    /**
     * Sets the cookie
     *
     * @param mixed $data       The cookie data
     * @param int   $expiration The expiration timestamp
     */
    public function set($data, $exipration)
    {
        $data = $data . ':' . $this->generateSignature($data);

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

    /**
     * Generates a signature based on the cookie data
     *
     * @param string $data The data of the cookie
     *
     * @return string The cookie signature
     */
    private function generateSignature($data)
    {
        return hash_hmac('sha256', $data, $this->key);
    }

    /**
     * Checks whether the signature is valid
     *
     * @param string $data      The data of which the signature is based
     * @param string $signature The signature to match against
     *
     * @return bool True when the signature is valid
     */
    private function isSignatureValid($data, $signature)
    {
        return $signature === hash_hmac('sha256', $data, $this->key);
    }
}
