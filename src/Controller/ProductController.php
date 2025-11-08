<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(CacheInterface $cache): Response
    {
        $products = $cache->get('product_list', function (ItemInterface $item) {
            $item->expiresAfter(1); // expire après 1 seconde

            // Exemple de données simulées
            return ['Laptop', 'Phone', 'Tablet'];
        });

        return $this->json($products);
    }
}
