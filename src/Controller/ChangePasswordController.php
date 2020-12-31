<?php

namespace App\Controller;

use App\Form\UpdatePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangePasswordController extends AbstractController
{
    /**
     * @Route("/change/password", name="app_change_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        if (! $this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createForm(UpdatePasswordType::class);
        $form->handleRequest($request);
        $user = $this->getUser();
        if($form->isSubmitted() and $form->isValid())
        {
            /**
             * on verifit si l'ancien mot de passe de l'utilisateur est valide
             */
            $verifit_password = $encoder->isPasswordValid($user,$form->get('oldPassword')->getData());
            /**
             * si l'ancien mot de passe est valide on donne l'utilisateur la possibilite de modifier
             * son mot de passe, au contraire on l'affiche un message d'erreur
             */
            if($verifit_password)
            {
                $em = $this->getDoctrine()->getManager();
                /**
                 * on encode le mot de passe avant de l'enregistrer
                 */
                $new_password = $encoder->encodePassword($user,$form->get('oldPassword')->getData());
                $user->setPassword($new_password);
                $em->persist($user);
                $em->flush();

                /**
                 * message flash
                 */
                $this->addFlash('success','Votre mot de passe a été modifié avec succès');

                /**
                 * une fois le mot de passe l'utilisateur modifier on le deconnect
                 */
                return  $this->redirectToRoute('app_logout');
            }
            else
            {
               $form->get('oldPassword')->addError(new FormError('Votre mot de passe actuel est invalid'));
            }
        }

        return $this->render('change_password/index.html.twig', [
            'UpdatePasswordForm' => $form->createView()
        ]);
    }
}
