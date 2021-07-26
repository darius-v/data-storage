<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findUserItemById(UserInterface $user, int $id): ?Item
    {
        return $this->findOneBy(['user' => $user, 'id' => $id]);
    }

    /**
     * @param UserInterface|User $user
     */
    public function findByUser(UserInterface $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT * FROM item WHERE user_id = :userId";
        $stmt = $conn->prepare($sql);
//        $stmt->bindValue(, $id);
        $stmt->execute(['userId' => $user->getId()]);

        return $stmt->fetchAllAssociative();
    }
}
