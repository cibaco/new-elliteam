<?php

namespace App\Repository;

use App\Entity\CompanyOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyOffer>
 */
class CompanyOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyOffer::class);
    }

    /**
     * Sauvegarde une offre en base de données
     */
    public function save(CompanyOffer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une offre de la base de données
     */
    public function remove(CompanyOffer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère toutes les offres triées par date de création (plus récentes en premier)
     */
    public function findAllOrderedByCreatedAt(): array
    {
        return $this->createQueryBuilder('co')
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les offres par statut
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('co')
            ->andWhere('co.status = :status')
            ->setParameter('status', $status)
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les offres par type de besoin
     */
    public function findByNeedType(string $needType): array
    {
        return $this->createQueryBuilder('co')
            ->andWhere('co.needType = :needType')
            ->setParameter('needType', $needType)
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des offres par entreprise ou nom
     */
    public function searchByCompanyOrName(string $searchTerm): array
    {
        return $this->createQueryBuilder('co')
            ->andWhere('co.company LIKE :term OR co.name LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'offres par statut
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('co')
            ->select('COUNT(co.id)')
            ->andWhere('co.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les offres créées dans les dernières 24 heures
     */
    public function findRecentOffers(): array
    {
        $yesterday = new \DateTimeImmutable('-24 hours');

        return $this->createQueryBuilder('co')
            ->andWhere('co.createdAt >= :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->orderBy('co.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total d'offres
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('co')
            ->select('COUNT(co.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques complètes par statut
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('co');

        $results = $qb->select('co.status, COUNT(co.id) as count')
            ->groupBy('co.status')
            ->getQuery()
            ->getResult();

        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'rejected' => 0,
        ];

        foreach ($results as $result) {
            $stats[$result['status']] = (int) $result['count'];
        }

        return $stats;
    }
}