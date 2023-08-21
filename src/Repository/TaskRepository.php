<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function TaskFilter($value): array
    {

        $query = $this->createQueryBuilder('t');

        if(isset($value['title']) && !empty($value['title'])) {
            
            $query->andWhere('t.title LIKE :title')
            ->setParameter('title', '%' . $value['title'] . '%');
        }

        if(isset($value['status']) && !empty($value['status'])) {

            $query->andWhere('t.status = :status')
            ->setParameter('status', Task::STATUS[$value['status']]);
        }

        if(isset($value['priority']) && !empty($value['priority'])) {

            $query->andWhere('t.priority = :priority')
            ->setParameter('priority', Task::PRIORITY[$value['priority']]);
        }

        if(isset($value['createdAt']) && !empty($value['createdAt'])) {

            $query->addOrderBy('t.createdAt', 'DESC');
        }

        if(isset($value['updatedAt']) && !empty($value['updatedAt'])) {

            $query->addOrderBy('t.updatedAt', 'DESC');
        }

        if(isset($value['completedAt']) && !empty($value['completedAt'])) {

            $query->addOrderBy('t.completedAt', 'DESC');
        }

        return $query->getQuery()
                    ->getResult();
    }

//    /**
//     * @return Task[] Returns an array of Task objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
