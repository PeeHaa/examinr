<?php
/**
 * Filesystem download class
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
 * Filesystem download class
 *
 * @category   Examinr
 * @package    Download
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Filesystem implements Downloader
{
    /**
     * @var string The location to download to
     */
    private $location;

    /**
     * Creates instance
     *
     * @param string $location The location to download to
     */
    public function __construct($location)
    {
        $this->location = $location;
    }

    /**
     * Download a project to a temporary location to run tests
     *
     * @param string $identifier The location of the source to download
     *
     * @return string The path to the downloaded project
     *
     * @throws \Examinr\Download\NotFoundException When the source could not be found
     */
    public function download($identifier)
    {
        if (!is_dir($identifier) && !is_file($identifier)) {
            throw new NotFoundException('The resource (`' . $identifier . '`) could not be found.');
        }

        return $this->getProject($identifier, $this->createRoot());
    }

    /**
     * Creates the root path in which the projects will be downloaded
     *
     * @return string The root path
     */
    private function createRoot()
    {
        $root = $this->location . '/' . $this->generateRootName();

        mkdir($root, 0755);

        return $root;
    }

    /**
     * Generates the unique root path name
     *
     * @return string The unique name
     */
    private function generateRootName()
    {
        return md5(uniqid('', true));
    }

    /**
     * Download the project to the root path
     *
     * @param string $source      The source to download
     * @param string $destination The destination to download the project to
     *
     * @return string The destination with the downloaded source
     */
    private function getProject($source, $destination)
    {
        // this might take some time for "bigger" projects
        set_time_limit(10 * 60);

        $directoryIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach($directoryIterator as $item) {
            if ($item->isDir()) {
                mkdir($destination . '/' . $directoryIterator->getSubPathName());
            } else {
                copy($item, $destination . '/' . $directoryIterator->getSubPathName());
            }
        }

        return $destination;
    }
}
