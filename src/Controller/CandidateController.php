<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use App\Form\CompanyOfferType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;

final class CandidateController extends AbstractController
{
    #[Route('/candidats', name: 'app_candidate_index')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        MailerInterface $mailer
    ): Response
    {
        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Gérer l'upload du CV
                $cvFile = $form->get('cvFile')->getData();
                if ($cvFile) {
                    $cvFileName = $fileUploader->upload($cvFile, 'cv');
                    $candidature->setCv($cvFileName);
                }

                // Sauvegarder en base de données
                $entityManager->persist($candidature);
                $entityManager->flush();

                // Envoyer un email de confirmation au candidat
                $this->sendConfirmationEmail($candidature, $mailer);

                // Envoyer un email de notification à l'équipe RH
                $this->sendNotificationToHR($candidature, $mailer);

                $this->addFlash('success', 'Votre candidature a été envoyée avec succès ! Nous reviendrons vers vous rapidement.');

                return $this->redirectToRoute('app_home');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre candidature. Veuillez réessayer.');

            }
        }

        return $this->render('candidates/candidates.html.twig', [
            'form' => $form->createView(),
        ]);

    }
    private function sendConfirmationEmail(Candidature $candidature, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@elliteam.com')
            ->to($candidature->getEmail())
            ->subject('Confirmation de réception de votre candidature')
            ->html($this->renderView('emails/candidature_confirmation.html.twig', [
                'candidature' => $candidature,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre candidature. Veuillez réessayer.');
            // Log l'erreur mais ne pas bloquer le processus
        }
    }

    private function sendNotificationToHR(Candidature $candidature, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@elliteam.com')
            ->to('rh@elliteam.com')
            ->subject('Nouvelle candidature reçue - ' . $candidature->getPosteRecherche())
            ->html($this->renderView('emails/candidature_notification_hr.html.twig', [
                'candidature' => $candidature,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreurf est survenue lors de l\'envoi de votre candidature. Veuillez réessayer.');
            // Log l'erreur mais ne pas bloquer le processus
        }
    }
}
