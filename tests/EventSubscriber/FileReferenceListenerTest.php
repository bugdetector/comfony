<?php

namespace App\Test\EventSubscriber;

use App\Entity\Auth\User;
use App\Entity\File\File;
use App\Entity\File\FileStatus;
use App\EventSubscriber\FileReferenceListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;

class FileReferenceListenerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UnitOfWork $unitOfWork;
    private FileReferenceListener $listener;
    private ClassMetadata $fileMetadata;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->fileMetadata = $this->createMock(ClassMetadata::class);

        $this->entityManager
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);

        $this->entityManager
            ->method('getClassMetadata')
            ->with(File::class)
            ->willReturn($this->fileMetadata);

        $this->listener = new FileReferenceListener($this->entityManager);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([\Doctrine\ORM\Events::onFlush], $this->listener->getSubscribedEvents());
    }

    public function testScheduledEntityInsertionsSetsFileToPermanent(): void
    {
        $file = new File();
        $file->setStatus(FileStatus::Temporary);

        $user = new User();
        $user->setProfilePhoto($file);

        $this->unitOfWork
            ->method('getScheduledEntityInsertions')
            ->willReturn([$user]);
        $this->unitOfWork
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->unitOfWork
            ->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->fileMetadata, $file);

        $eventArgs = new OnFlushEventArgs($this->entityManager);
        $this->listener->onFlush($eventArgs);

        $this->assertSame(FileStatus::Permanent->value, $file->getStatus());
    }

    public function testReplacingFileMarksOldFileAsDeletedAndNewAsPermanent(): void
    {
        $oldFile = new File();
        $oldFile->setStatus(FileStatus::Permanent);

        $newFile = new File();
        $newFile->setStatus(FileStatus::Temporary);

        $user = new User();
        $user->setProfilePhoto($newFile);

        $this->unitOfWork
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityUpdates')
            ->willReturn([$user]);
        $this->unitOfWork
            ->method('getEntityChangeSet')
            ->with($user)
            ->willReturn([
                'profile_photo' => [$oldFile, $newFile],
            ]);
        $this->unitOfWork
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        // Expected to recompute change set for both oldFile (Deleted) and newFile (Permanent)
        $recomputedFiles = [];
        $this->unitOfWork
            ->expects($this->exactly(2))
            ->method('recomputeSingleEntityChangeSet')
            ->with(
                $this->fileMetadata,
                $this->callback(function (File $file) use (&$recomputedFiles) {
                    $recomputedFiles[] = $file;
                    return true;
                })
            );

        $eventArgs = new OnFlushEventArgs($this->entityManager);
        $this->listener->onFlush($eventArgs);

        $this->assertSame(FileStatus::Permanent->value, $newFile->getStatus());
        $this->assertSame(FileStatus::Deleted->value, $oldFile->getStatus());
        $this->assertContains($oldFile, $recomputedFiles);
        $this->assertContains($newFile, $recomputedFiles);
    }

    public function testCollectionUpdateMarksRemovedFileAsDeleted(): void
    {
        $removedFile = new File();
        $removedFile->setStatus(FileStatus::Permanent);

        $collection = new class ($removedFile) {
            public function __construct(private File $file)
            {
            }

            public function getDeleteDiff(): array
            {
                return [$this->file];
            }
        };

        $this->unitOfWork
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionUpdates')
            ->willReturn([$collection]);
        $this->unitOfWork
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);

        $this->unitOfWork
            ->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->fileMetadata, $removedFile);

        $eventArgs = new OnFlushEventArgs($this->entityManager);
        $this->listener->onFlush($eventArgs);

        $this->assertSame(FileStatus::Deleted->value, $removedFile->getStatus());
    }

    public function testEntityDeletionMarksAttachedFileAsDeleted(): void
    {
        $file = new File();
        $file->setStatus(FileStatus::Permanent);

        $user = new User();
        $user->setProfilePhoto($file);

        $this->unitOfWork
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);
        $this->unitOfWork
            ->method('getScheduledEntityDeletions')
            ->willReturn([$user]);

        $this->unitOfWork
            ->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($this->fileMetadata, $file);

        $eventArgs = new OnFlushEventArgs($this->entityManager);
        $this->listener->onFlush($eventArgs);

        $this->assertSame(FileStatus::Deleted->value, $file->getStatus());
    }
}
