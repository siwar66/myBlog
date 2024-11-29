<?php

namespace App\Controller;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\CommentType;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement de fichier pour 'picture'
            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('profile_pictures_directory'), // Configurez ce paramètre dans services.yaml
                    $fileName
                );
    
                $post->setPicture($fileName);
            }
    
            $user = $this->getUser();
            $post->setUser($user);
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_post_show', methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
    
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $user = $this->getUser();
            $comment->setUser($user);
    
            $comment->setPost($post);
    
            $comment->setCreatedAt(new \DateTimeImmutable());
    
            $entityManager->persist($comment);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }
    
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('post/show.html.twig', [
                'post' => $post,
                'commentForm' => $commentForm->createView(),
            ]);
        }
    
        return $this->render('post/user_post.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
              /** @var UploadedFile $file */
              $file = $form->get('picture')->getData();
              if ($file) {
                  $fileName = uniqid() . '.' . $file->guessExtension();
                  $file->move(
                      $this->getParameter('profile_pictures_directory'), // Configurez ce paramètre dans services.yaml
                      $fileName
                  );
      
                  $post->setPicture($fileName);
              }
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
