<?php

namespace App\Controller;

use App\Repository\EmpruntRepository;
use App\Repository\OrderProductsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    // src/Controller/DashboardController.php
public function index(UserRepository $userRepository, ProductRepository $productRepository, OrderRepository $orderRepository, OrderProductsRepository $orderProductsRepository, EmpruntRepository $empruntRepository): Response
{
    $userCount = $userRepository->count([]);
    $productCount = $productRepository->count([]);
    $orderCount = $orderRepository->count([]);
    $empruntCount = $empruntRepository->count([]);
    $mostPurchasedProducts = $orderProductsRepository->findMostPurchasedProducts();
    
    // Calcul du revenu total (sans frais de livraison)
    $totalRevenue = $orderRepository->getTotalRevenue();

    // Préparer les données pour le graphique
    $productNames = [];
    $productQuantities = [];
    foreach ($mostPurchasedProducts as $product) {
        $productNames[] = $product['name'];
        $productQuantities[] = $product['totalQuantity'];
    }

    $monthlyRevenue = $orderRepository->getMonthlyRevenue();
    
    // Préparer les données pour le graphique de revenus
    $months = [];
    $revenues = [];
    foreach ($monthlyRevenue as $revenue) {
        $months[] = DateTime::createFromFormat('!m', $revenue['month'])->format('F');
        $revenues[] = $revenue['total'];
    }

    return $this->render('dashboard/index.html.twig', [
        'userCount' => $userCount,
        'productCount' => $productCount,
        'orderCount' => $orderCount,
        'mostPurchasedProducts' => $mostPurchasedProducts,
        'productNames' => $productNames,
        'productQuantities' => $productQuantities,
        'empruntCount' => $empruntCount,
        'monthlyRevenue' => $monthlyRevenue,
        'months' => $months,
        'revenues' => $revenues,
        'totalRevenue' => $totalRevenue, // Ajout du revenu total
    ]);
}
}