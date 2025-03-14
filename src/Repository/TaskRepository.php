<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByProject($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.project = :val')
            ->setParameter('val', $value->getId())
            ->orderBy('t.status', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function isEmployeeAssignedToTask(int $taskId, int $employeeId): bool
    {
        return (bool) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.id = :taskId')
            ->andWhere('t.employee = :employeeId')
            ->setParameter('taskId', $taskId)
            ->setParameter('employeeId', $employeeId)
            ->getQuery()
            ->getSingleScalarResult();
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
