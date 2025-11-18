<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanieController extends AbstractController
{
    #[Route('/entreprise', name: 'app_companie_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('companie/companie.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }
}
