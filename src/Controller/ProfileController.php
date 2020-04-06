<?php

namespace App\Controller;

use App\Form\UserType;
use App\Form\UserPasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
class ProfileController extends BaseController
{
    /**
     * @Route("/", name="profile_show", methods={"GET"})
     */
    public function show(): Response
    {
        return $this->render('profile/show.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/edit", name="profile_edit", methods={"GET","POST"})
     */
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/password", name="profile_edit_password", methods={"GET","POST"})
     */
    public function editPassword(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserPasswordType::class);

        $data = $request->request->get('user_password');
        if ($request->isMethod('POST') && ('' != $data['new_password'])) {
            if ($this->passwordEncoder->isPasswordValid($user, $data['old_password'])) {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $data['new_password']));
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('danger', 'Password updated');

                return $this->redirectToRoute('profile_show');
            }
            $this->addFlash('danger', 'Old password is wrong');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
