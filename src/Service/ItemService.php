<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
        $items = $this->itemRepository->findByUser($user);

        $allItems = [];
        foreach ($items as $item) {
            $oneItem['id'] = $item['id'];
            $oneItem['data'] = $item['data'];
            $oneItem['created_at'] = new DateTime($item['created_at']);
            $oneItem['updated_at'] = new DateTime($item['updated_at']);
            $allItems[] = $oneItem;
        }

        return $allItems;
    }

    public function create(UserInterface $user, string $data): void
    {
        /** @var User $user */
        $this->itemRepository->create($user->getId(), $data, $this->now());
    }

    public function update(UserInterface $user, string $id, string $data): void
    {
        /** @var User $user */
        $this->itemRepository->updateItem($user->getId(), $id, $data, $this->now());
    }

    public function delete(Item $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    private function now(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }
} 