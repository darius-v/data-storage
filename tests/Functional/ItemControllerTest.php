<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use DateTime;
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

        $user = $this->findJohn();
        $client->loginUser($user);

        $newItem = $this->createItem($client);
        $this->getItem($client);
        $id = $this->updateItem($newItem, $client);
        $this->deleteItem($id, $client);
    }

    public function testDoesNotDeleteOtherUserItem(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('john');

        $this->makeSureUserHasItem($user);

        $johnItem = $user->getItems()->first();

        // this user will try to delete John's item
        $tempUser = $this->createUser();
        $client->loginUser($tempUser);

        /** @var ItemRepository $itemRepository */
        $itemRepository = static::getContainer()->get(ItemRepository::class);
        $johnItem = $itemRepository->find($johnItem->getId());
        $client->request('DELETE', '/item/' . $johnItem->getId());

        $this->deleteUser($tempUser);

        $this->assertNotNull($johnItem->getId()); // when deletes - getId returns null
    }

    public function testDeletePerformance()
    {
        $client = static::createClient();
        $user = $this->getNewLoggedInUser($client);

        $em = $this->getEntityManager();

        $count = 100;
        $conn = $em->getConnection();
        for ($i=0; $i<$count; $i++) {
            $conn->insert(
                'item',
                ['user_id' => $user->getId(), 'data' => 'test', 'created_at' => $this->now(), 'updated_at' => $this->now()]
            );
        }

        $items = $this->getItemRepository()->findByUser($user);

        $start = time();
        foreach ($items as $item) {
            $client->request('DELETE', '/item/' . $item['id']);
        }
        $deleteTime = time() - $start;

        echo "Deleted $count items in $deleteTime seconds \n";
        $this->assertTrue($deleteTime < 4);

        $conn->delete('user', ['id' => $user->getId()]);
    }

    private function updateItem(Item $item, KernelBrowser $client): int
    {
        $data = 'very secure updated item data';

        $updatedItemData = ['data' => $data, 'id' => (string)$item->getId()];

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
        return static::getContainer()->get(ItemRepository::class);
    }

    private function getUserRepository(): UserRepository
    {
        return static::getContainer()->get(UserRepository::class);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
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

        $item = $itemRepository->find($id);

        $this->assertNull($item);
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
        $this->assertTrue(($latestItem === null && $newItem !== null) || // case when there are no items in database
            $newItem->getId() > $latestItem->getId());  // case when there are items in db before running this test
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

    private function getNewLoggedInUser(KernelBrowser $client): User
    {
        $user = new User();
        $user->setUsername(time());
        $user->setPassword('asd');
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        return $user;
    }

    private function now(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }

    private function findJohn(): User
    {
        return $this->getUserRepository()->findOneByUsername('john');
    }

    private function makeSureUserHasItem(User $user)
    {
        if ($user->getItems()->count() === 0) {
            $item = new Item();
            $item->setData('makeSureUserHasItem');
            $user->addItem($item);

            $em = self::getEntityManager();
            $em->persist($item);
            $em->persist($user);
            $em->flush();
        }
    }
}
