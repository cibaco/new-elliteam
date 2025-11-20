<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * Save a Candidature entity
     */
    public function save(Candidature $candidature, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidature);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a Candidature entity
     */
    public function remove(Candidature $candidature, bool $flush = true): void
    {
        $this->getEntityManager()->remove($candidature);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find candidatures by poste recherché
     */
    public function findByPoste(string $poste): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.posteRecherche LIKE :poste')
            ->setParameter('poste', '%' . $poste . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find candidatures by statut
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find candidatures by disponibilité
     */
    public function findByDisponibilite(string $disponibilite): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.disponibilite = :disponibilite')
            ->setParameter('disponibilite', $disponibilite)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent candidatures (last 30 days)
     */
    public function findRecent(int $limit = 10): array
    {
        $dateLimit = new \DateTimeImmutable('-30 days');

        return $this->createQueryBuilder('c')
            ->andWhere('c.createdAt >= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search candidatures by keyword in posteRecherche or message
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.posteRecherche LIKE :keyword OR c.message LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find candidatures disponibles immédiatement
     */
    public function findDisponiblesImmediatement(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.disponibilite = :disponibilite')
            ->setParameter('disponibilite', 'immediatement')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', 'nouvelle')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count candidatures by statut
     */
    public function countByStatut(string $statut): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get statistics by disponibilité
     */
    public function getStatisticsByDisponibilite(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.disponibilite, COUNT(c.id) as total')
            ->groupBy('c.disponibilite')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find candidatures by email (to avoid duplicates)
     */
    public function findByEmail(string $email): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.email = :email')
            ->setParameter('email', $email)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get top postes recherchés
     */
    public function getTopPostesRecherches(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.posteRecherche, COUNT(c.id) as total')
            ->groupBy('c.posteRecherche')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}