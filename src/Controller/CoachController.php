<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CoachController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function home() {

        $coaches = $this->getDoctrine()->getRepository(User::class)->findRandomCoach(6);
        // $form = $this->createFormBuilder()
        // ->setAction($this>generateUrl('app_findcity'))
        // ->add('search' ,TextType::class)
        // ->getForm();
        return $this->render('coach/accueil.html.twig', [
            'coaches'=> $coaches
        ]);
    }
    /**
     * @Route("/coach", name="app_coach")
     */
    public function ListCoach() {
        $coaches = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('coach/coachList.html.twig', [
            'coaches'=> $coaches,
        ]);
    }
    /**
     * @Route("/view/{id}", name="app_view")
     */
    public function viewCoach(int $id, Request $request, EntityManagerInterface $manager, User $coaches) {
        $details = $this->getDoctrine()->getRepository(User::class);
        $coaches = $details->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if(
            $form->isSubmitted() && $form->isValid()){
                $comment->setPublishedAt(new \DateTime('now'))->setUser($coaches);
                $manager->persist($comment);
                $manager->flush();
                $this->addFlash('success','Votre commentaire a bien ??t?? envoy??');
                return $this->redirectToRoute('app_view',[
                    'id'=>$coaches->getId()
                ]);
            }
        
            
        return $this->render('coach/viewcoach.html.twig', [
            'coaches'=> $coaches,
            'commentForm'=> $form->createView()
        ]);
    }

    /**
     * @Route("/mon-compte/update", name="app_update")
     */
    public function update(UserPasswordEncoderInterface $encoder, Request $request,SluggerInterface $slugger ) 
    {
        // Utilisateur non connect??
        if(empty($this->getUser())){
            return $this->redirectToRoute('app_home');
        }
        
        $errors = [];
       
        $em = $this->getDoctrine()->getManager(); // Connexion
        // $this->getUser() contient l'utilisateur actuellement connect??. Je peux donc le pass?? dans le find()
        $user = $em->getRepository(User::class)->find($this->getUser());

        $form = $this->createForm(RegistrationType::class, $user);

        // Analyse la requ??te
        $form->handleRequest($request);

        // V??rification du formulaire
        if($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('picture')->getData();
            // Je v??rifie que l'image soit bien upload??
            if ($pictureFile) {

                // Je r??cup??re le nom original de l'image
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Je nettoie le nom du fichier original, je le slugify pour ??viter les caract??res accentu??s , espaces et autres caract??res sp??ciaux
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Je sauvegarde mon fichier
                try {
                    $pictureFile->move(
                        $this->getParameter('photo_profil_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    //En cas de gros probl??me (exemple le r??pertoire d'image n'existe pas)
                }

                // Modifie l ancienne  image dans la bdd
                $user->setPicture($newFilename);
            }


            // $form->get('password')->getData() equivalent de $safe['password']
            if(!empty($form->get('password')->getData())){
                $hash = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($hash);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('coach/update.html.twig', [
            'form' => $form->createView(),
            'update' => $user,
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/recherche-coach/{city}", name="app_search")
     */
    public function searchCityCoach(string $city) {
        $coaches = $this->getDoctrine()->getRepository(User::class)->findCityCoach($city);
        
        return $this->render('coach/cityCoach.html.twig', [
            'coaches'=> $coaches,
        ]);
    }
      /**
     * @Route("/recherche-ville", name="app_findcity")
     */
    public function findByCity(Request $request) {

       $searchcity = $request->request->get('search');
       $coaches = $this->getDoctrine()->getRepository(User::class)->findCityCoach($searchcity);
        
        return $this->render('coach/cityCoach.html.twig', [
            'coaches'=> $coaches,
        ]);
    }

    /**
     * @Route("/reservation", name="app_reservation")
     */
    public function reservation() {
        return $this->render('coach/reservation.html.twig');
    }

    #Supprimer les donn??es d'un coach
   /**
     * @Route("/delete/{id}", name="delete_coach")
     */
    public function delete(int $id): Response
    {
        $em = $this->getDoctrine()->getManager(); // Connexion
        $coach = $em->getRepository(User::class)->find($id);

        if(empty($coach)){
            throw $this->createNotFoundException("Le profil n'existe pas");
        }


        if(!empty($_POST)){
            if(isset($_POST['confirm']) && $_POST['confirm'] === 'yes'){

                $this->container->get('security.token_storage')->setToken(null);
                $em->remove($coach);
                $em->flush();

                $this->addFlash('success', 'Votre profil a bien ??t?? supprim??');
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('coach/delete.html.twig', [
            'coach' => $coach,
        ]);
    }
}