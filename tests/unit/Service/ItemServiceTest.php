<?php

namespace App\Tests\unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;

class ItemServiceTest extends TestCase
{
    /**
     * @var ItemRepository $itemRepository
     */
    private $itemRepository;

    /**
     * @var ItemService
     */
    private $itemService;

    public function setUp(): void
    {
        /** @var EntityManagerInterface */
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush'])
            ->getMock()
        ;

        $this->itemRepository = $this->createMock(ItemRepository::class);

        $this->itemService = new ItemService($entityManager, $this->itemRepository);
    }

    public function testCreate(): void
    {
        /** @var User */
        $user = $this->createMock(User::class);
        $user
            ->method('getId')
            ->willReturn(1)
        ;

        $data = 'secret data';

        $expectedObject = new Item();
        $expectedObject->setUser($user);
        $expectedObject->setData($data);

        $this->itemRepository->expects($this->once())->method('create')->with(1, $data);

        $this->itemService->create($user, $data);
    }
}
