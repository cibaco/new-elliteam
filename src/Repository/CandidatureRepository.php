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
     * Sauvegarde une candidature
     */
    public function save(Candidature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une candidature
     */
    public function remove(Candidature $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Compte le nombre de candidatures par statut
     */
    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les candidatures par statut
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
     * Récupère les candidatures par disponibilité
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
     * Recherche par nom ou poste
     */
    public function searchByNomOrPoste(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nomPrenom LIKE :term OR c.posteRecherche LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les candidatures créées dans les dernières 24h
     */
    public function findRecentCandidatures(int $hours = 24): array
    {
        $date = new \DateTimeImmutable("-{$hours} hours");

        return $this->createQueryBuilder('c')
            ->andWhere('c.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques complètes par statut
     */
    public function getStatistics(): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.statut, COUNT(c.id) as count')
            ->groupBy('c.statut')
            ->getQuery()
            ->getResult();

        $stats = [
            'nouvelle' => 0,
            'en_cours' => 0,
            'retenue' => 0,
            'refusee' => 0,
            'archivee' => 0,
        ];

        foreach ($results as $result) {
            $stats[$result['statut']] = (int) $result['count'];
        }

        return $stats;
    }

    /**
     * Statistiques par disponibilité
     */
    public function getStatisticsByDisponibilite(): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.disponibilite, COUNT(c.id) as count')
            ->groupBy('c.disponibilite')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['disponibilite']] = (int) $result['count'];
        }

        return $stats;
    }

    /**
     * Candidatures par poste recherché (top 10)
     */
    public function getTopPosteRecherche(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.posteRecherche, COUNT(c.id) as count')
            ->groupBy('c.posteRecherche')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Candidatures récemment modifiées
     */
    public function findRecentlyUpdated(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.updatedAt IS NOT NULL')
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte total de candidatures
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Candidatures par mois (12 derniers mois)
     */
    public function getCandidaturesByMonth(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM candidature
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";

        return $connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Candidatures par statut et mois
     */
    public function getCandidaturesByStatusAndMonth(int $months = 6): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                statut,
                COUNT(*) as count
            FROM candidature
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY month, statut
            ORDER BY month ASC, statut ASC
        ";

        return $connection->executeQuery($sql, ['months' => $months])->fetchAllAssociative();
    }

    /**
     * Candidatures urgentes (disponibilité immédiate)
     */
    public function findUrgentCandidatures(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.disponibilite = :dispo')
            ->andWhere('c.statut IN (:statuts)')
            ->setParameter('dispo', 'immediatement')
            ->setParameter('statuts', ['nouvelle', 'en_cours'])
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}