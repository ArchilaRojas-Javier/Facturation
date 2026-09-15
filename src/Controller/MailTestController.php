<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;


final class MailTestController extends AbstractController
{
    #[Route('/mail/test', name: 'app_mail_test')]
    public function index(MailerInterface $mailerInterface): Response
    {
        $email =new Email();
        $email->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Test Email')
            ->text('This is a test email.');

        $mailerInterface->send($email);

        return $this->render('mail_test/index.html.twig', [
            'controller_name' => 'MailTestController',
        ]);
    }
}
