<?php

namespace App\Test\Controller\Auth;

use App\Entity\Auth\User;
use App\Repository\Auth\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/admin/user/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(User::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[username]' => 'Testing',
            'user[password]' => 'Testing',
            'user[name]' => 'Testing',
            'user[surname]' => 'Testing',
            'user[email]' => 'Testing',
            'user[status]' => 'Testing',
            'user[created_at]' => 'Testing',
            'user[last_updated]' => 'Testing',
            'user[roles]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setUsername('My Title');
        $fixture->setPassword('My Title');
        $fixture->setName('My Title');
        $fixture->setSurname('My Title');
        $fixture->setEmail('My Title');
        $fixture->setStatus('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setLast_updated('My Title');
        $fixture->setRoles('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setUsername('Value');
        $fixture->setPassword('Value');
        $fixture->setName('Value');
        $fixture->setSurname('Value');
        $fixture->setEmail('Value');
        $fixture->setStatus('Value');
        $fixture->setCreated_at('Value');
        $fixture->setLast_updated('Value');
        $fixture->setRoles('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[username]' => 'Something New',
            'user[password]' => 'Something New',
            'user[name]' => 'Something New',
            'user[surname]' => 'Something New',
            'user[email]' => 'Something New',
            'user[status]' => 'Something New',
            'user[created_at]' => 'Something New',
            'user[last_updated]' => 'Something New',
            'user[roles]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/user/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getUsername());
        self::assertSame('Something New', $fixture[0]->getPassword());
        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getSurname());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getLast_updated());
        self::assertSame('Something New', $fixture[0]->getRoles());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setUsername('Value');
        $fixture->setPassword('Value');
        $fixture->setName('Value');
        $fixture->setSurname('Value');
        $fixture->setEmail('Value');
        $fixture->setStatus('Value');
        $fixture->setCreated_at('Value');
        $fixture->setLast_updated('Value');
        $fixture->setRoles('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/user/');
        self::assertSame(0, $this->repository->count([]));
    }
}
