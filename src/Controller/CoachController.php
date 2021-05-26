<?php

namespace App\Controller;

use App\Entity\User;
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
    public function viewCoach(int $id) {
        $details = $this->getDoctrine()->getRepository(User::class);
        $coaches = $details->find($id);

        return $this->render('coach/viewcoach.html.twig', [
            'coaches'=> $coaches,
        ]);
    }

}
