<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function categories(EntityManagerInterface $em, Request $request,): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //le formulaire envoyé est valide
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('app_categories');
            $this->addFlash('success', '✅Categorie ajoutée avec succès !');
        }

        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
            'form' => $form->createView(),
            'categories' => $categories
        ]);
    }

    #[Route('/create/categorie', name: 'categorie_create')]
    public function categorieCreate(EntityManagerInterface $em, Request $request,): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //le formulaire envoyé est valide
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('app_accueil');
            $this->addFlash('success', '✅Categorie ajoutée avec succès !');
        }

        return $this->render('categorie/categorie_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/{id}', 'categorie', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function categorie(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $categorie = $em->getRepository(Categorie::class)->find($id);

        if ($categorie == null) {
            return $this->render('/categorie/categorie_notfound.html.twig');
        } else {
            $articles = $categorie->getArticles();
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', '✅Categorie modifiée avec succès !');
            return $this->redirectToRoute('categorie', ['id' => $categorie->getId()]);
        }

        return $this->render('/categorie/categorie.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'categorie' => $categorie,
            'articles' => $articles,
        ]);
    }

    #[Route('categorie/delete/{id}', 'deleteCategorie', methods: ['POST', 'GET'], requirements: ['id' => '\d+'])]
    public function deleteCategorie(Categorie $categorie = null, EntityManagerInterface $em)
    {
        if ($categorie === null) {
            return $this->render('/categorie/categorie_notfound.html.twig');
        }

        $em->remove($categorie);
        $em->flush();
        $this->addFlash('success', '✅Categorie supprimée avec succès !');
        return $this->redirectToRoute('app_accueil');
    }
}
