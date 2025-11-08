<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/posts')]
class PostController extends AbstractController
{
    private EntityManagerInterface $em;
    private PostRepository $postRepository;
    private CacheInterface $cache;

    public function __construct(
        EntityManagerInterface $em,
        PostRepository $postRepository,
        #[\Symfony\Contracts\Service\Attribute\Required] CacheInterface $postsCache
    ) {
        $this->em = $em;
        $this->postRepository = $postRepository;
        $this->cache = $postsCache;
    }

    #[Route('', name: 'get_posts', methods: ['GET'])]
    public function getPosts(): JsonResponse
    {
        $data = $this->cache->get('posts_all', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 heure

            $posts = $this->postRepository->findAll();
            $result = [];

            foreach ($posts as $post) {
                $result[] = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'description' => $post->getDescription(),
                ];
            }

            return $result;
        });

        return $this->json($data);
    }

    #[Route('', name: 'create_post', methods: ['POST'])]
    public function createPost(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $title = $requestData['title'] ?? null;
        $description = $requestData['description'] ?? null;

        if (!$title) {
            return $this->json(['error' => 'Title is required'], 400);
        }

        $post = new Post();
        $post->setTitle($title);
        $post->setDescription($description);

        $this->em->persist($post);
        $this->em->flush();

        // Supprimer le cache pour forcer la mise à jour
        $this->cache->delete('posts_all');

        return $this->json([
            'message' => 'Post created successfully',
            'post' => [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'description' => $post->getDescription(),
            ],
        ], 201);
    }
}
