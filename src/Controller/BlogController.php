<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_home')]
    public function home(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC'], 3);
    
        $categories = $categoryRepository->findAll();
    
        return $this->render('home/home.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }
    
}
