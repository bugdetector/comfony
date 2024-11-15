<?php

namespace App\Theme;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ThemeHelper extends AbstractController
{

    /**
     * Keep page level assets
     *
     * @var array
     */
    public static $javascriptFiles = [];
    public static $cssFiles = [];
    public static $metatags = [];

    public $themeDirectory = "@base_theme";
    public $layoutFile = "@base_theme/layout/_default.html.twig";

    public function setThemeDirectory($themeDirectory) {
        $this->themeDirectory = $themeDirectory;
    }

    public function setLayoutFile($layoutFile) {
        $this->layoutFile = $layoutFile;
    }

    function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        return parent::getParameter($name);
    }
    
    /**
     * Add custom javascript file to the page
     *
     * @param $file
     *
     * @return void
     */
    function addJavascriptFile($file)
    {
        self::$javascriptFiles[] = $file;
    }

    /**
     * Add custom CSS file to the page
     *
     * @param $file
     *
     * @return void
     */
    function addCssFile($file)
    {
        self::$cssFiles[] = $file;
    }

    /**
     * Get HTML attribute based on the scope
     *
     * @param $scope
     * @param $attribute
     *
     * @return array
     */
    function getHtmlAttributes($scope, $attribute)
    {
        return self::$htmlAttributes[$scope][$attribute] ?? [];
    }

    public function addMetaTag($index, $attributes)
    {
        self::$metatags[$index] = $attributes;
    }
}
