<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Trouve un rôle par son nom
     */
    public function findOneByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Trouve ou crée un rôle
     */
    public function findOrCreate(string $name, ?string $description = null): Role
    {
        $role = $this->findOneByName($name);

        if (!$role) {
            $role = new Role();
            $role->setName($name);
            $role->setDescription($description);

            $this->getEntityManager()->persist($role);
            $this->getEntityManager()->flush();
        }

        return $role;
    }

    /**
     * Trouve tous les rôles triés par nom
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'utilisateurs par rôle
     */
    public function countUsersByRole(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.name, COUNT(u.id) as userCount')
            ->leftJoin('r.users', 'u')
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}