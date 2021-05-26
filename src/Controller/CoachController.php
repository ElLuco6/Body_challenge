<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoachController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function home() {
        return $this->render('coach/accueil.html.twig');
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
        $form = $this->createForm(CommentType::Class, $comment);
        $form->handleRequest($request);
        if(
            $form->isSubmitted() && $form->isValid()){
                $comment->setPublishedAt(new \DateTime('now'));
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

}
