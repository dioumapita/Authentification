<?php

namespace App\Controller;

use App\Form\EditProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="app_profil")
     */
    public function index(): Response
    {
        if (! $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('profil/index.html.twig');
    }
    /**
     * @Route("/update_profil", name="app_update_profil")
     */
    public function update_profil(Request $request)
    {
        if (! $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();
        $form = $this->createForm(EditProfilType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() and $form->isValid())
        {
            /**
             * on gère l'upload d'image
             */
            $avatar = $form->get('avatar')->getData();

            if($avatar)
            {
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads/avatar';
                $fileName = time().'.'.$avatar->guessExtension();
                $avatar->move($destination,$fileName);
                $user->setAvatar($fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            /**
             * Message flash de succès
             */
            $this->addFlash('success','Votre profil à été mis à jour avec succès');
            return $this->redirectToRoute('app_profil');
        }
        
        $this->getDoctrine()->getManager()->refresh($user);
        return $this->render('profil/edit_profil.html.twig',[
            'EditProfilForm' => $form->createView()
        ]);
    }
    
}
