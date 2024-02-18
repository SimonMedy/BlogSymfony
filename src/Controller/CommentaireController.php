<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentaireController extends AbstractController
{
    #[Route('/commentaires', name: 'app_commentaires')]
    public function index(EntityManagerInterface $em, Request $request, Security $security,): Response
    {
        $articles = $em->getRepository(Article::class)->findAll();
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            $commentaire->setAuteur($user);
            $em->persist($commentaire);
            $em->flush();
            $this->addFlash('success', '✅Commentaire enregistrée avec succès !');
            return $this->redirectToRoute('app_articles');
        }

        return $this->render('commentaire/commentaires.html.twig', [
            'controller_name' => 'CommentaireController',
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

    #[Route('commentaire/delete/{id}', 'deleteCommentaire', methods: ['POST', 'GET'], requirements: ['id' => '\d+'])]
    public function deleteCommentaire(Commentaire $commentaire = null, EntityManagerInterface $em, Request $request, Security $security)
    {
        if ($commentaire === null) {
            return $this->render('/commentaire/commentaire_notfound.html.twig');
        }

        $user = $security->getUser();

        if ($user === $commentaire->getAuteur() || $this->isGranted('ROLE_ADMIN')) {
            $em->remove($commentaire);
            $em->flush();
            $this->addFlash('success', '✅Commentaire supprimée avec succès !');
            return $this->redirect($request->headers->get('referer'));
        }else{
            $this->addFlash('error', '❌Vous ne pouvez pas supprimer ce commentaire');
            return $this->redirectToRoute('app_articles');
        }
        

    }
}
