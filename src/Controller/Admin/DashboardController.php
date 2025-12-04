<?php

namespace App\Controller\Admin;

use App\Entity\Candidature;
use App\Entity\CompanyOffer;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Repository\CompanyOfferRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private CompanyOfferRepository $companyOfferRepository,
        private CandidatureRepository $candidatureRepository,
        private UserRepository $userRepository,
        private ?ChartBuilderInterface $chartBuilder = null,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Statistiques CompanyOffer
        $companyOfferStats = [
            'pending' => $this->companyOfferRepository->countByStatus('pending'),
            'processing' => $this->companyOfferRepository->countByStatus('processing'),
            'completed' => $this->companyOfferRepository->countByStatus('completed'),
            'rejected' => $this->companyOfferRepository->countByStatus('rejected'),
            'total' => $this->companyOfferRepository->count([]),
        ];

        // Statistiques Candidature
        $candidatureStats = [
            'nouvelle' => $this->candidatureRepository->countByStatut('nouvelle'),
            'en_cours' => $this->candidatureRepository->countByStatut('en_cours'),
            'retenue' => $this->candidatureRepository->countByStatut('retenue'),
            'refusee' => $this->candidatureRepository->countByStatut('refusee'),
            'archivee' => $this->candidatureRepository->countByStatut('archivee'),
            'total' => $this->candidatureRepository->count([]),
        ];


        // Statistiques Utilisateurs
        $userStats = [
            'total' => $this->userRepository->count([]),
            'active' => $this->userRepository->count(['isActive' => true]),
            'verified' => $this->userRepository->count(['isVerified' => true]),
            'admins' => count(array_filter(
                $this->userRepository->findAll(),
                fn($user) => $user->isAdmin()
            )),
        ];

        // Dernières offres
        $recentCompanyOffers = $this->companyOfferRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentUsers = $this->userRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentCandidatures = $this->candidatureRepository->findBy([], ['createdAt' => 'DESC'], 5);


        return $this->render('admin/dashboard.html.twig', [
            'company_offer_stats' => $companyOfferStats,
            'candidature_stats' => $candidatureStats,
            'user_stats' => $userStats,
            'recent_candidatures' => $recentCandidatures,
            'recent_company_offers' => $recentCompanyOffers,
            'recent_users' => $recentUsers,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Elliteam - Administration')
            ->setFaviconPath('favicon.ico')
            ->setLocales(['fr'])
            ->renderContentMaximized()
            ->renderSidebarMinimized()
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
       // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Section Offres Entreprises
        yield MenuItem::section('Offres Entreprises', 'fa fa-briefcase');

        yield MenuItem::linkToCrud('Toutes les offres', 'fa fa-list', CompanyOffer::class)
            ->setBadge($this->companyOfferRepository->count([]), 'info');


        // Section Candidatures
        yield MenuItem::section('Candidatures', 'fa fa-file-alt');

        yield MenuItem::linkToCrud('Toutes les candidatures', 'fa fa-list', Candidature::class)
            ->setBadge($this->candidatureRepository->count([]), 'info');

        // Section Utilisateurs
        yield MenuItem::section('Gestion des utilisateurs', 'fa fa-users');

        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class)
            ->setBadge($this->userRepository->count([]), 'info');

        yield MenuItem::linkToCrud('Rôles', 'fa fa-shield-alt', Role::class);

        // Section Administration
        yield MenuItem::section('Administration', 'fa fa-cog');

        yield MenuItem::linkToRoute('Paramètres', 'fa fa-wrench', 'admin_settings')
            ->setPermission('ROLE_SUPER_ADMIN');

        // Section Navigation
        yield MenuItem::section('Navigation');

        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'app_home');

        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out-alt');
    }

    private function createCandidatureChart(array $stats): ?Chart
    {
        if (!$this->chartBuilder) {
            return null;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chart->setData([
            'labels' => ['Nouvelles', 'En cours', 'Retenues', 'Refusées', 'Archivées'],
            'datasets' => [
                [
                    'label' => 'Candidatures',
                    'backgroundColor' => ['#ffc107', '#007bff', '#28a745', '#dc3545', '#6c757d'],
                    'data' => [
                        $stats['nouvelle'],
                        $stats['en_cours'],
                        $stats['retenue'],
                        $stats['refusee'],
                        $stats['archivee'],
                    ],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }

    private function createCompanyOfferChart(array $stats): ?Chart
    {
        if (!$this->chartBuilder) {
            return null;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chart->setData([
            'labels' => ['En attente', 'En cours', 'Terminées', 'Rejetées'],
            'datasets' => [
                [
                    'label' => 'Offres Entreprises',
                    'backgroundColor' => ['#ffc107', '#007bff', '#28a745', '#dc3545'],
                    'data' => [
                        $stats['pending'],
                        $stats['processing'],
                        $stats['completed'],
                        $stats['rejected'],
                    ],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }

    private function createOffreChart(array $stats): ?Chart
    {
        if (!$this->chartBuilder) {
            return null;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => ['Nouvelles', 'En cours', 'Traitées', 'Archivées'],
            'datasets' => [
                [
                    'label' => 'Offres d\'emploi',
                    'backgroundColor' => ['#ffc107', '#007bff', '#28a745', '#6c757d'],
                    'data' => [
                        $stats['nouvelle'],
                        $stats['en_cours'],
                        $stats['traitee'],
                        $stats['archivee'],
                    ],
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ]);

        return $chart;
    }

    #[Route('/admin/statistics', name: 'admin_statistics')]
    public function statistics(): Response
    {
        return $this->render('admin/statistics.html.twig', [
            'company_offers_by_type' => $this->companyOfferRepository->getStatisticsByType(),
            'offres_by_type' => $this->offreRepository->getStatisticsByType(),
            'users_activity' => $this->userRepository->getActivityStats(),
        ]);
    }

    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return $this->render('admin/settings.html.twig');
    }
}