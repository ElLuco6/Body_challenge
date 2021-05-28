<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_inscription")
     */
    public function Registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, SluggerInterface $slugger): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        // Analyse la requête
        $form->handleRequest($request);

        // Vérification du formulaire
        if($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();
            // Je vérifie que l'image soit bien uploadé
            if ($pictureFile) {
                // Je récupère le nom original de l'image
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Je nettoie le nom du fichier original, je le slugify pour éviter les caractères accentués , espaces et autres caractères spéciaux
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Je sauvegarde mon fichier
                try {
                    $pictureFile->move(
                        $this->getParameter('photo_profil_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    //En cas de gros problème (exemple le répertoire d'image n'existe pas)
                }

                // Ligne a modifier
                $user->setPicture($newFilename);
            }


            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setCreatedAt(new \DateTime());
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/registration.html.twig',[
           'form' => $form->createView()
       ]);
    }

    /**
     * @Route("/login", name="app_login")
     */

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_home');
         }

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
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
