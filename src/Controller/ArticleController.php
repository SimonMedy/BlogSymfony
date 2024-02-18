<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'app_articles')]
    public function articles(EntityManagerInterface $em, Request $request,): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();

        return $this->render('article/articles.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    #[Route('/create/article', name: 'article_create')]
    public function articleCreate(EntityManagerInterface $em, Request $request, Security $security,): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($article->isEtat() == true) {
                $article->updateDateParution();
            } else {
                $article->setDateParution(null);
            }
            
            $user = $security->getUser();
            $article->setAuteur($user);

            $em->persist($article);
            $em->flush();
            $this->addFlash('success', '✅Article enregistrée avec succès !');
            return $this->redirectToRoute('app_articles');
        }

        return $this->render('article/article_create.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    #[Route('/article/{id}', 'article', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function article(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $article = $em->getRepository(Article::class)->find($id);

        if ($article == null) {
            return $this->render('/article/article_notfound.html.twig');
        } else {
            $commentaires = $article->getCommentaires();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($article->isEtat() == true) {
                $article->updateDateParution();
            } else {
                $article->setDateParution(null);
            }

            $em->persist($article);
            $em->flush();
            $this->addFlash('success', '✅Article modifiée avec succès !');
            return $this->redirectToRoute('article', ['id' => $article->getId()]);
        }

        return $this->render('/article/article.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'article' => $article,
            'commentaires' => $commentaires
        ]);
    }

    #[Route('article/delete/{id}', 'deleteArticle', methods: ['POST', 'GET'], requirements: ['id' => '\d+'])]
    public function deleteArticle(Article $article = null, EntityManagerInterface $em)
    {
        if ($article === null) {
            return $this->render('/article/article_notfound.html.twig');
        }

        $em->remove($article);
        $em->flush();
        $this->addFlash('success', '✅Article supprimée avec succès !');
        return $this->redirectToRoute('app_articles');
    }
}
