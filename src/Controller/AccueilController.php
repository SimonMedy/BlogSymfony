<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(EntityManagerInterface $em, Request $request,): Response
    {
        $categories = $em->getRepository(Categorie::class)->findBy([], ['id' => 'desc'], 3);

        return $this->render('accueil/accueil.html.twig', [
            'controller_name' => 'AccueilController',
            'categories' => $categories,
        ]);
    }
}
