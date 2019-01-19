<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\LoggedInUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LoggedInUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoggedInUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoggedInUsers[]    findAll()
 * @method LoggedInUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoggedInUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LoggedInUsers::class);
    }

    // /**
    //  * @return LoggedInUsers Returns a LoggedInUsers object
    //  */
    // public function findBySessionid($sessionid)
    // {
    //     $result = $this->createQueryBuilder('l')
    //         ->andWhere('l.sessionid = :val')
    //         ->setParameter('val', $sessionid)
    //         ->setMaxResults(1)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    //     if(count($result) > 0)
    //     {
    //         return $result[0];
    //     }

    //     return new LoggedInUsers();
    // }

    /**
     * @return LoggedInUsers Returns a LoggedInUsers object
     */
    public function findOneByUsername($username)
    {
        $result = $this->createQueryBuilder('l')
            ->andWhere('l.username = :val')
            ->setParameter('val', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result;
    }

    /**
     * @return LoggedInUsers Returns a LoggedInUsers object
     */
    public function findOneBySessionid($sessionid)
    {
        $result = $this->createQueryBuilder('l')
            ->andWhere('l.sessionid = :val')
            ->setParameter('val', $sessionid)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result;
    }
}
