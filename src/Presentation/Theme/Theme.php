<?php
/**
 * Theme and child theme manager
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
 * Theme and child theme manager
 *
 * @category   Examinr
 * @package    Presentation
 * @subpackage Theme
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Theme implements Loader
{
    /**
     * @var string Base path for themes
     */
    private $themePath;

    /**
     * @var array The main theme
     */
    private $theme;

    /**
     * @var null|array The child theme
     */
    private $childTheme;

    /**
     * Creates instance and sets the themes
     *
     * @param string $themePath Base path for themes
     * @param string $theme     The name of the active theme
     */
    public function __construct($themePath, $theme)
    {
        $this->themePath = rtrim($themePath, '/');

        $this->setTheme($theme);
    }

    /**
     * Sets a theme
     *
     * @param string $theme The name of the theme to set
     *
     * @throws \Examinr\Presentation\Theme\NotFoundException When the theme cannot be found
     * @throws \Examinr\Presentation\Theme\InvalidException  When the theme is not valid
     */
    public function setTheme($theme)
    {
        if (!$this->isPathValid($theme)) {
            throw new NotFoundException('The theme cannot be found.');
        }

        if (!$this->isThemeValid($theme)) {
            throw new InvalidException('The theme is not valid.');
        }

        $themeInfo = $this->getThemeInfo($theme);

        if (array_key_exists('parent', $themeInfo)) {
            $this->childTheme = $themeInfo;

            $this->setTheme($themeInfo['parent']);
        } else {
            $this->theme = $themeInfo;
        }
    }

    /**
     * Gets the theme info (based on the theme's info.json file)
     *
     * @param string $theme The name of the theme to get the info for
     *
     * @return array List containing the theme information
     */
    private function getThemeInfo($theme)
    {
        return json_decode(file_get_contents($this->themePath . '/' . $theme . '/info.json'), true);
    }

    /**
     * Checks whether the theme directory exists
     *
     * @param string $theme The name of the theme
     *
     * @return bool True when the directory of the theme exists
     */
    private function isPathValid($theme)
    {
        if (!is_dir(rtrim($this->themePath, '/') . '/' . $theme)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the layout of the theme directory is valid
     *
     * @param string $theme The name of the theme
     *
     * @return bool True when the layout of the theme directory is valid
     */
    private function isThemeValid($theme)
    {
        $requiredFields = ['name', 'description', 'type', 'version'];

        if (!file_exists($this->themePath . '/' . $theme . '/info.json')) {
            return false;
        }

        $themeInfo = $this->getThemeInfo($theme);

        if ($themeInfo === null) {
            return false;
        }

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $themeInfo)) {
                return false;
            }
        }

        return true;
    }

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
    public function load($file)
    {
        if ($this->childTheme && file_exists($this->themePath . '/' . $this->childTheme['name'] . $file)) {
            return $this->themePath . '/' . $this->childTheme['name'] . $file;
        }

        if (!file_exists($this->themePath . '/' . $this->theme['name'] . $file)) {
            throw new NotFoundException('The template file (`' . $file . '`) could not be found in the theme.');
        }

        return $this->themePath . '/' . $this->theme['name'] . $file;
    }
}
