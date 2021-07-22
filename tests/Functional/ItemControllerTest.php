<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class ItemControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $userRepository = static::$container->get(UserRepository::class);
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

        $this->assertNotNull($newItem);
    }
}
