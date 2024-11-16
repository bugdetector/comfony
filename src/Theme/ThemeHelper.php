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
    public $javascriptFiles = [];
    public $cssFiles = [];
    public $metaTags = [];

    public $themeDirectory = "@base_theme";
    public $layoutFile = "@base_theme/layout/_default.html.twig";

    public function setThemeDirectory($themeDirectory)
    {
        $this->themeDirectory = $themeDirectory;
    }

    public function setLayoutFile($layoutFile)
    {
        $this->layoutFile = $layoutFile;
    }

    public function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
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
    public function addJavascriptFile($file)
    {
        $this->javascriptFiles[] = $file;
    }

    /**
     * Add custom CSS file to the page
     *
     * @param $file
     *
     * @return void
     */
    public function addCssFile($file)
    {
        $this->cssFiles[] = $file;
    }

    /**
     * Add custom Metatag to the page
     *
     * @param $file
     *
     * @return void
     */
    public function addMetaTag($index, $attributes)
    {
        $this->metaTags[$index] = $attributes;
    }
}
