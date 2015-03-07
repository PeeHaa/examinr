<?php
/**
 * Interface for translators
 *
 * PHP version 5.5
 *
 * @category   Examinr
 * @package    I18n
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Examinr\I18n;

/**
 * Interface for translators
 *
 * @category   Examinr
 * @package    I18n
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
interface Translator
{
    /**
     * Gets the translation by key if any or a placeholder otherwise
     *
     * @param string $key  The translation key for which to find the translation
     * @param array  $data Extra data to use in the translated string as variables
     *
     * @return string The translation or a placeholder when no translation is available
     */
    public function translate($key, array $data = []);
}
