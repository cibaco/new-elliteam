<?php

namespace App\Controller;

use App\Entity\CompanyOffer;
use App\Form\CompanyOfferType;
use App\Repository\CompanyOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompanyOfferController extends AbstractController
{
    #[Route('/deposer-offre', name: 'app_company_offer')]
    public function index(Request $request, CompanyOfferRepository $repository, SluggerInterface $slugger): Response
    {
        $companyOffer = new CompanyOffer();
        $form = $this->createForm(CompanyOfferType::class, $companyOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier uploadé
            $attachmentFile = $form->get('attachment')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('company_offers_directory'),
                        $newFilename
                    );
                    $companyOffer->setAttachmentFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');
                }
            }

            // Sauvegarde en base de données
            $repository->save($companyOffer, true);

            // Message de succès
            $this->addFlash('success', 'Votre demande a bien été enregistrée. Nous reviendrons vers vous très prochainement.');

            // Redirection pour éviter la resoumission du formulaire
            return $this->redirectToRoute('app_home');
        }

        return $this->render('company/offer.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Route pour la soumission AJAX du formulaire
     */
    #[Route('/deposer-offre/submit', name: 'app_company_offer_ajax', methods: ['POST'])]
    public function submitAjax(Request $request, CompanyOfferRepository $repository, SluggerInterface $slugger): JsonResponse
    {
        $companyOffer = new CompanyOffer();
        $form = $this->createForm(CompanyOfferType::class, $companyOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier uploadé
            $attachmentFile = $form->get('attachment')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('company_offers_directory'),
                        $newFilename
                    );
                    $companyOffer->setAttachmentFilename($newFilename);
                } catch (FileException $e) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Une erreur est survenue lors de l\'upload du fichier.'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Sauvegarde en base de données
            $repository->save($companyOffer, true);

            return $this->json([
                'success' => true,
                'message' => 'Votre demande a bien été enregistrée.'
            ]);
        }

        // Récupération des erreurs de validation
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'success' => false,
            'message' => 'Le formulaire contient des erreurs.',
            'errors' => $errors
        ], Response::HTTP_BAD_REQUEST);
    }


}