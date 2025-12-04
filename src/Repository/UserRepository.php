<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Sauvegarde un utilisateur
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un utilisateur
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un utilisateur par email
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les utilisateurs actifs
     */
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs vérifiés
     */
    public function findVerifiedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('verified', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les administrateurs
     */
    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs récemment inscrits
     */
    public function findRecentUsers(int $days = 7): array
    {
        $date = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs par rôle personnalisé
     */
    public function findByRole(string $roleName): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userRoles', 'r')
            ->andWhere('r.name = :roleName')
            ->setParameter('roleName', $roleName)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'utilisateurs
     */
    public function searchUsers(string $searchTerm): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.firstName LIKE :term OR u.lastName LIKE :term OR u.email LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques d'activité
     */
    public function getActivityStats(): array
    {
        $qb = $this->createQueryBuilder('u');

        $total = $qb->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $active = $this->count(['isActive' => true]);
        $verified = $this->count(['isVerified' => true]);

        $recent = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.lastLoginAt >= :date')
            ->setParameter('date', new \DateTimeImmutable('-30 days'))
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => (int) $total,
            'active' => (int) $active,
            'verified' => (int) $verified,
            'active_last_30_days' => (int) $recent,
            'inactive' => (int) ($total - $active),
        ];
    }

    /**
     * Utilisateurs n'ayant jamais connecté
     */
    public function findNeverConnected(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.lastLoginAt IS NULL')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Utilisateurs inactifs depuis X jours
     */
    public function findInactiveSince(int $days = 90): array
    {
        $date = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('u')
            ->andWhere('u.lastLoginAt < :date OR u.lastLoginAt IS NULL')
            ->andWhere('u.createdAt < :date')
            ->setParameter('date', $date)
            ->orderBy('u.lastLoginAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les utilisateurs par statut
     */
    public function countByStatus(): array
    {
        return [
            'active' => $this->count(['isActive' => true]),
            'inactive' => $this->count(['isActive' => false]),
            'verified' => $this->count(['isVerified' => true]),
            'not_verified' => $this->count(['isVerified' => false]),
        ];
    }

    /**
     * Utilisateurs créés par mois (12 derniers mois)
     */
    public function getUsersCreatedByMonth(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM user
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";

        return $connection->executeQuery($sql)->fetchAllAssociative();
    }
}