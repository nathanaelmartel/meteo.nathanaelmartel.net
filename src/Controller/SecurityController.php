<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends BaseController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/forgotten-password", name="app_forgotten_password")
     */
    public function forgottenPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);
            /* @var $user User */

            if (null === $user) {
                $this->addFlash('danger', 'Email Inconnu');

                return $this->redirectToRoute('default');
            }
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute('default');
            }

            $content = $this->getSetting('mail_password_forgotten_content');
            $params = [
                '[FULLNAME]' => $user->getFullname(),
                '[URL]' => $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL),
            ];
            foreach ($params as $key => $param) {
                $content = str_replace($key, $param, $content);
            }
            $subject = $this->getSetting('mail_password_forgotten_subject');
            $content = $this->wrapMail($content, $subject);

            $message = (new \Swift_Message($subject))
                ->setFrom($this->getSetting('email_from'))
                ->setTo($user->getEmail())
                ->setBcc(['formulaire@simplement-web.com', $this->getSetting('email_bcc', 'formulaire@simplement-web.com')])
                ->setBody($content, 'text/html');

            if ($mailer->send($message)) {
                $this->addFlash('success', 'Mail envoyé');
            }

            return $this->redirectToRoute('default');
        }

        return $this->render('security/forgotten_password.html.twig');
    }

    /**
     * @Route("/reset_password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = $entityManager->getRepository(User::class)->findOneByResetToken($token);
            /* @var $user User */

            if (null === $user) {
                $this->addFlash('danger', 'Token Inconnu');

                return $this->redirectToRoute('default');
            }

            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');

            return $this->redirectToRoute('default');
        } else {
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }
}
