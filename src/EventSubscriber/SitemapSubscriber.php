<?php

namespace App\EventSubscriber;

use App\Repository\Page\PageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PageRepository $pageRepository,
    ) {
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPageUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    /**
     * @param UrlContainerInterface $urls
     * @param UrlGeneratorInterface $router
     */
    public function registerPageUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $pages = $this->pageRepository->findBy(['published' => 1]);

        foreach ($pages as $page) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'app_page_view',
                        [
                            'slug' => $page->getSlug()
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    $page->getUpdatedAt()
                ),
                'page'
            );
        }
    }
}
