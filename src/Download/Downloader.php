<?php
/**
 * Interface for project downloaders
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Download
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Download;

/**
 * Interface for project downloaders
 *
 * @category   Examinr
 * @package    Download
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Downloader
{
    /**
     * Download a project to a temporary location to run tests
     *
     * @param string $identifier The location of the source to download
     *
     * @throws \Examinr\Download\NotFoundException When the source could not be found
     */
    public function download($identifier);
}
