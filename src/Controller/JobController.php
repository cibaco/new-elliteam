<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class JobController extends AbstractController
{
    #[Route('/offres', name: 'app_job_offre', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('jobs/jobs.html.twig', [
            'controller_name' => 'JobController',
        ]);
    }
}
