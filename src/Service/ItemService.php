<?php

namespace App\Service;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ItemService
{
    private $entityManager;

    /** @var ItemRepository */
    private $itemRepository;

    public function __construct(EntityManagerInterface $entityManager, ItemRepository $itemRepository)
    {
        $this->entityManager = $entityManager;
        $this->itemRepository = $itemRepository;
    }

    public function list(UserInterface $user): array
    {


//        for ($i=0; $i<10000; $i++) {

            $items = $this->itemRepository->findByUser($user);

//            dump($items);

            $allItems = [];
            foreach ($items as $item) {
                $oneItem['id'] = $item['id'];
                $oneItem['data'] = $item['data'];
                $oneItem['created_at'] = $item['created_at'];
                $oneItem['updated_at'] = $item['updated_at'];
                $allItems[] = $oneItem;
            }

//        }

        return $allItems;
    }

    public function create(UserInterface $user, string $data): void
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function update(UserInterface $user, string $id, string $data): Item
    {
        $item = $this->itemRepository->findUserItemById($user, $id);

        if (null === $item) {
            throw new NotFoundHttpException();
        }

        $item->setData($data);
        $this->entityManager->flush();

        return $item;
    }

    public function delete(Item $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }
} 