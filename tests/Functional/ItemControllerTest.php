<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class ItemControllerTest extends WebTestCase
{
    public function testCRUD()
    {
        $client = static::createClient();

        $userRepository = $this->getUserRepository();

        $user = $userRepository->findOneByUsername('john');
        $client->loginUser($user);

        $newItem = $this->createItem($client);
        $this->getItem($client);
        $id = $this->updateItem($newItem, $client);
        $this->deleteItem($id, $client);
    }

    public function testDoesNotDeleteOtherUserItem(): void
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');
        $johnItem = $user->getItems()->first();

        if (!$johnItem) {
            throw new LogicException('John should have item');
        }

        // this user will try to delete John's item
        $tempUser = $this->createUser();
        $client->loginUser($tempUser);

        /** @var ItemRepository $itemRepository */
        $itemRepository = static::$container->get(ItemRepository::class);
        $johnItem = $itemRepository->find($johnItem->getId());
        $client->request('DELETE', '/item/' . $johnItem->getId());

        $this->deleteUser($tempUser);

        $this->assertNotNull($johnItem->getId()); // when deletes - getId returns null
    }

    private function updateItem(Item $item, KernelBrowser $client): int
    {
        $data = 'very secure updated item data';

        $updatedItemData = ['data' => $data, 'id' => $item->getId()];

        $client->request('PUT', '/item', $updatedItemData);

        $updatedItem = $this->getItemRepository()->find($item->getId());

        $this->assertEquals($data, $updatedItem->getData());

        return $item->getId();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setUsername('test');
        $user->setPassword('test');
        $em = static::$container->get(EntityManagerInterface::class);
        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function deleteUser(User $user): void
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();
    }

    private function getItemRepository(): ItemRepository
    {
        return static::$container->get(ItemRepository::class);
    }

    private function getUserRepository(): UserRepository
    {
        return static::$container->get(UserRepository::class);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::$container->get(EntityManagerInterface::class);
    }

    private function findLatestItem(): ?Item
    {
        $qb = $this->getItemRepository()->createQueryBuilder('i');
        $qb
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function deleteItem(int $id, KernelBrowser $client)
    {
        $client->request('DELETE', '/item/' . $id);
        $this->assertResponseIsSuccessful();

        $itemRepository = $this->getItemRepository();

        $this->assertNull($itemRepository->find($id));
    }

    private function createItem(KernelBrowser $client): Item
    {
        $data = 'very secure new item data';

        $newItemData = ['data' => $data];

        $latestItem = $this->findLatestItem();

        $client->request('POST', '/item', $newItemData);

        $newItem = $this->findLatestItem();

        // checking if data is equal to what we posted is not enough, because older items could have same data.
        // so checking if really new item with data was created
        $this->assertTrue($newItem->getId() > $latestItem->getId());
        $this->assertEquals($data, $newItem->getData());

        return $newItem;
    }

    private function getItem(KernelBrowser $client)
    {
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();

        $listArray = json_decode($content, true);

        $newItem = end($listArray);

        $this->assertEquals('very secure new item data', $newItem['data']);

        $this->assertArrayHasKey('date', $newItem['created_at']);
        $this->assertArrayHasKey('timezone_type', $newItem['created_at']);
        $this->assertArrayHasKey('timezone', $newItem['created_at']);

        $this->assertArrayHasKey('date', $newItem['updated_at']);
        $this->assertArrayHasKey('timezone_type', $newItem['updated_at']);
        $this->assertArrayHasKey('timezone', $newItem['updated_at']);
    }
}
