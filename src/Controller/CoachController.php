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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CoachController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function home() {

        $coaches = $this->getDoctrine()->getRepository(User::class)->findRandomCoach(4);
        
        return $this->render('coach/accueil.html.twig', [
            'coaches'=> $coaches,
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
                $this->addFlash('success','Votre commentaire a bien été envoyé');
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
        // Utilisateur non connecté
        if(empty($this->getUser())){
            return $this->redirectToRoute('app_home');
        }
        
        $errors = [];
       
        $em = $this->getDoctrine()->getManager(); // Connexion
        // $this->getUser() contient l'utilisateur actuellement connecté. Je peux donc le passé dans le find()
        $user = $em->getRepository(User::class)->find($this->getUser());

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


            // $form->get('password')->getData() equivalent de $safe['password']
            if(!empty($form->get('password')->getData())){
                $hash = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($hash);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }
        /*
        if(!empty($_POST)){ // Mon formulaire n'est pas vide
            $safe = array_map('trim', array_map('strip_tags', $_POST));

            if(strlen($safe['lastName']) < 3 || strlen($safe['lastName']) > 120){
                $errors['lastName'] = 'Le nom doit comporter entre 3 et 120 caractères';
            }
            if(strlen($safe['firstName']) < 3 || strlen($safe['firstName']) > 120){
                $errors['firstName'] = 'Le prenom doit comporter entre 3 et 120 caractères';
            }
            if(strlen($safe['city']) < 3 || strlen($safe['city']) > 120 || !ctype_alpha($safe['city'])) {
                $errors['city'] = 'Votre ville doit comporter entre 3 et 120 caractères et doit etre alphabetique';
            }
            if(empty($safe['description'])){
                $errors['description'] = 'La description ne peut être vide';
            }
            if(empty($safe['experience'])){
                $errors['experience'] = "L'experience ne peut être vide";
            }
            if(empty($safe['education'])){
                $errors['education'] = 'La formation ne peut être vide';
            }
            if(empty($safe['sport'])){
                $errors['sport'] = 'La spécialité ne peut être vide';
            }
            if(!is_numeric($safe['tarif']) || $safe['tarif'] <= 0){
                $errors['tarif'] = 'Le tarif est invalide';
            }
            if(!is_numeric($safe['age']) || $safe['age'] <= 0){
                $errors['age'] = "L'age est invalide";
            }
            if (!isset($safe['email']) || filter_var($safe['email'],FILTER_VALIDATE_EMAIL) == false) {
                $errors['email'] = 'Votre email est invalide';
            }

            if(!empty($safe['password'])){
                if (!isset($safe['password']) || !ctype_alnum($safe['password']) || strlen($safe['password']) < 8) {
                    $errors['password'] = 'Le mot de passe doit etre aplhanumérique et supérieur à 8 caractere';
                }
            }
            

             // Ici mon tableau $errors n'a aucune entrée 
             if(count($errors) === 0){
                // Alors je peux enregistrer en base de données
                
                $user->setLastName($safe['lastName']);
                $user->setFirstName($safe['firstName']);
                $user->setEmail($safe['email']);
                $user->setAge($safe['age']);
                // Mise à jour du mot de passe seulement si rempli
                if(!empty($safe['password'])){
                    $hash = $encoder->encodePassword($user, $safe['password']);
                    $user->setPassword($hash);
                }
                $user->setDescription($safe['description']);
                $user->setCity($safe['city']);
                $user->setSport($safe['sport']);
                $user->setExperience($safe['experience']);
                $user->setEducation($safe['education']);
                $user->setTarif($safe['tarif']);
                //$user->setCreatedAt(new \DateTime('now')); // La date & heure de l'instant T
                
                $em->persist($user);
                $em->flush(); // Execute la requete (equivalent du $bdd->execute())
            }
            else {
                // Afficher les erreurs
            }
        }
        */

        return $this->render('coach/update.html.twig', [
            'form' => $form->createView(),
            'update' => $user,
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/recherche-coach", name="app_search")
     */
    public function searchCityCoach() {
        $cityCoach = $this->getDoctrine()->getRepository(User::class)->findCityCoach();
        
        return $this->render('coach/accueil.html.twig', [
            'cityCoach'=> $cityCoach,
        ]);
    }
}