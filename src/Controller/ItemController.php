<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    /** @var ItemService */
    private $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function list(): JsonResponse
    {
        // With 71 record for the user this method is about 3x faster when using plain sql
        $allItems = $this->itemService->list($this->getUser());

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = $request->get('data');

        if (empty($data)) {
            return $this->json(['error' => 'No data parameter']);
        }

        // 1000 iterations: 19s with doctrine, 6s with plain sql
        $itemService->create($this->getUser(), $data);

        return $this->json([]);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     */
    public function delete(int $id): JsonResponse
    {
        if (empty($id)) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->findUserItemById($this->getUser(), $id);

        if ($item === null) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $this->itemService->delete($item);

        return $this->json([]);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, ItemService $itemService): JsonResponse
    {
        $id = $request->request->get('id');
        if (empty($id)) {
            return $this->errorJson('No id parameter');
        }

        $data = $request->get('data');

        if (empty($data)) {
            return $this->errorJson('No data parameter');
        }

        $itemService->update($this->getUser(), $id, $data);

        return $this->json(['id' => $id, 'data' => $data]);
    }

    private function errorJson(string $message): JsonResponse
    {
        return $this->json(['error' => $message], Response::HTTP_BAD_REQUEST);
    }
}
