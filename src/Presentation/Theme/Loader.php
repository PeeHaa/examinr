<?php
/**
 * Interface for file loaders (from themes)
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Theme
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\Presentation\Theme;

/**
 * Interface for file loaders (from themes)
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Theme
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Loader
{
    /**
     * Loads a file from the theme
     *
     * This method will first try to load the file from the child theme (if available)
     *
     * @param string $file The file to load (relative to the theme directory)
     *
     * @return string The filename to load
     *
     * @throws \Examinr\Presentation\Theme\NotFoundException When the file cannot be found in either the theme or
     *                                                       the child theme
     */
    public function load($file);
}
