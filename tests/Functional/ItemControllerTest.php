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

        $userRepository = static::$container->get(UserRepository::class);
        /** @var ItemRepository $itemRepository */
        $itemRepository = static::$container->get(ItemRepository::class);

        $user = $userRepository->findOneByUsername('john');

        $client->loginUser($user);
        
        $data = 'very secure new item data';

        $newItemData = ['data' => $data];

        $client->request('POST', '/item', $newItemData);
        $client->request('GET', '/item');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('very secure new item data', $client->getResponse()->getContent());

        /** @var Item $newItem */
        $newItem = $itemRepository->findOneByData($data);

        $this->update($newItem, $client);

        $this->assertNotNull($newItem);
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

    private function update(Item $item, KernelBrowser $client): void
    {
        $data = 'very secure updated item data';

        $updatedItemData = ['data' => $data, 'id' => $item->getId()];

        $client->request('PUT', '/item', $updatedItemData);

        $this->assertStringContainsString('very secure updated item data', $client->getResponse()->getContent());
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
        $em = static::$container->get(EntityManagerInterface::class);
        $em->remove($user);
        $em->flush();
    }
}
