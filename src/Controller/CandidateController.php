<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CandidateController extends AbstractController
{
    #[Route('/candidats', name: 'app_candidate_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('candidates/candidates.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }
}
