<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use App\Entity\File\File;
use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * See https://symfony.com/doc/current/templating/twig_extension.html.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Julien ITARD <julienitard@gmail.com>
 */
final class AppExtension extends AbstractExtension
{
    /**
     * @var string[]
     */
    private readonly array $localeCodes;

    /**
     * @var list<array{code: string, name: string}>|null
     */
    private ?array $locales = null;

    // The $locales argument is injected thanks to the service container.
    // See https://symfony.com/doc/current/service_container.html#binding-arguments-by-name-or-type
    public function __construct(
        string $locales,
        private string $uploads_directory
    ) {
        $localeCodes = explode('|', $locales);
        sort($localeCodes);
        $this->localeCodes = $localeCodes;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', $this->getLocales(...)),
            new TwigFunction('upload_asset', $this->uploadAsset(...))
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter("format_bytes", [$this, 'sizeConvertToString'])
        ];
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     *
     * @return array<int, array<string, string>>
     */
    public function getLocales(): array
    {
        if (null !== $this->locales) {
            return $this->locales;
        }

        $this->locales = [];
        foreach ($this->localeCodes as $localeCode) {
            $this->locales[] = ['code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode)];
        }

        return $this->locales;
    }

    /**
     * Get uploaded file url
     * @return string URL
     */
    public function uploadAsset($path)
    {
        return $this->uploads_directory . $path;
    }

    /**
     * Converts bytes to human readable string
     * @return string Human readable size
     */
    public function sizeConvertToString($bytes)
    {
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = ['B', 'KB', 'MB', 'GB'];
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        return $bytes;
    }
}
