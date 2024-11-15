<?php

namespace App\Theme\BaseTheme;

use App\Theme\ThemeHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultLayoutController extends AbstractController
{
    public function __construct(
        public ThemeHelper $theme,
        protected TranslatorInterface $translator
    )
    {
        $this->theme = $theme;
        $this->init();
    }

    public function init()
    {
        $this->theme->setLayoutFile('@base_theme/layout/_default.html.twig');
        $this->theme->setThemeDirectory('@base_theme');
    }
}