<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Repository\ItemRepository;
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

        $this->update($newItem, $client, $itemRepository);

        $this->assertNotNull($newItem);
    }

    private function update(Item $item, KernelBrowser $client, ItemRepository $itemRepository): void
    {
        $data = 'very secure updated item data';

        $updatedItemData = ['data' => $data, 'id' => $item->getId()];

        $client->request('PUT', '/item', $updatedItemData);

        /** @var Item $updatedItem */
        $updatedItem = $itemRepository->find($item->getId());

//        var_dump($updatedItem); die;

        $this->assertEquals($data, $updatedItem->getData());
    }
}
