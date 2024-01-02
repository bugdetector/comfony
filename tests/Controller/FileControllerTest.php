<?php

namespace App\Test\Controller\File;

use App\Entity\File\File;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/admin/files/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(File::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('File index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'file[file_name]' => 'Testing',
            'file[file_path]' => 'Testing',
            'file[file_size]' => 'Testing',
            'file[mime_type]' => 'Testing',
            'file[extension]' => 'Testing',
            'file[status]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new File();
        $fixture->setFile_name('My Title');
        $fixture->setFile_path('My Title');
        $fixture->setFile_size('My Title');
        $fixture->setMime_type('My Title');
        $fixture->setExtension('My Title');
        $fixture->setStatus('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('File');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new File();
        $fixture->setFile_name('Value');
        $fixture->setFile_path('Value');
        $fixture->setFile_size('Value');
        $fixture->setMime_type('Value');
        $fixture->setExtension('Value');
        $fixture->setStatus('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'file[file_name]' => 'Something New',
            'file[file_path]' => 'Something New',
            'file[file_size]' => 'Something New',
            'file[mime_type]' => 'Something New',
            'file[extension]' => 'Something New',
            'file[status]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/files/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFile_name());
        self::assertSame('Something New', $fixture[0]->getFile_path());
        self::assertSame('Something New', $fixture[0]->getFile_size());
        self::assertSame('Something New', $fixture[0]->getMime_type());
        self::assertSame('Something New', $fixture[0]->getExtension());
        self::assertSame('Something New', $fixture[0]->getStatus());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new File();
        $fixture->setFile_name('Value');
        $fixture->setFile_path('Value');
        $fixture->setFile_size('Value');
        $fixture->setMime_type('Value');
        $fixture->setExtension('Value');
        $fixture->setStatus('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/files/');
        self::assertSame(0, $this->repository->count([]));
    }
}
