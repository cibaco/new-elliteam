<?php

namespace App\Controller;

use App\Entity\CompanyOffer;
use App\Form\CompanyOfferType;
use App\Repository\CompanyOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CompanyOfferController extends AbstractController
{
    #[Route('/deposer-offre', name: 'app_company_offer')]
    public function index(Request $request, CompanyOfferRepository $repository, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $companyOffer = new CompanyOffer();
        $form = $this->createForm(CompanyOfferType::class, $companyOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            $repository->save($companyOffer, true);

            $this->sendConfirmationEmail($companyOffer, $mailer);
            $this->sendNotificationToHR($companyOffer, $mailer);

            $this->addFlash('success', 'Votre demande a bien été enregistrée. Nous reviendrons vers vous très prochainement.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('company/offer.html.twig', [
            'form' => $form,
        ]);
    }

    private function sendConfirmationEmail(CompanyOffer $companyOffer, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@elliteam.com')
            ->to($companyOffer->getEmail())
            ->subject('Confirmation de réception de votre offre')
            ->html($this->renderView('emails/offre_confirmation.html.twig', [
                'companyOffer' => $companyOffer,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            // Ne pas bloquer le processus si l'email échoue
        }
    }

    private function sendNotificationToHR(CompanyOffer $companyOffer, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@elliteam.com')
            ->to('rh@elliteam.com')
            ->subject('Nouvelle offre reçue - ' . $companyOffer->getPosition() . ' (' . $companyOffer->getCompany() . ')')
            ->html($this->renderView('emails/offre_notification_hr.html.twig', [
                'companyOffer' => $companyOffer,
            ]));

        try {
            $mailer->send($email);
        } catch (\Exception $e) {
            // Ne pas bloquer le processus si l'email échoue
        }
    }
}
