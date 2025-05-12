<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Entity\User;
use App\Entity\UserProduct;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserProductRepository;
use App\Service\Cart;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;

final class OrderController extends AbstractController
{

    #[Route('/order', name: 'app_order')]
    public function index(
        Request $request, 
        ProductRepository $productRepository, 
        SessionInterface $session, 
        EntityManagerInterface $entityManager, 
        Cart $cart
    ): Response
    {
        $data = $cart->getCart($session);
    
        // Vérifier si le panier n'est pas vide
        if (empty($data['cart'])) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_home');
        }
    
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $order->setUser($user);
            $order->setTotalPrice($data['total']);
            $order->setCreateAt(new \DateTimeImmutable());
            
            $entityManager->persist($order);
            
            foreach ($data['cart'] as $value) {
                $orderProduct = new OrderProducts();
                $orderProduct->setOrder($order);
                $orderProduct->setProduct($value['product']);
                $orderProduct->setQte($value['quantity']);
                $entityManager->persist($orderProduct);
    
                // Décrémenter le stock du produit
                $product = $value['product'];
                $currentStock = $product->getStock();
                $newStock = $currentStock - $value['quantity'];
                $product->setStock($newStock);
                $entityManager->persist($product);
            }
            
            $entityManager->flush();
            $session->set('cart', []);
            
            $this->addFlash('success', 'Votre commande a été enregistrée avec succès!');
            return $this->redirectToRoute('app_my_orders');
        }
    
        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $data['total']
        ]);
    }

    #[Route('/city/{id}/shipping/cost', name:'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response{
        $cityShippingPrice = $city->getShippingCost();
        return new Response(json_encode(['status'=>200,'message'=>'on','content'=>$cityShippingPrice]));
    }

    #[Route('/editor/order', name: 'app_orders_show')]
    public function getAllOrder(OrderRepository $orderRepository, Request $request, SecurityController $security): Response
{
    $user = $security->getUser(); 
    $searchId = $request->query->get('search_id');
    
    if ($searchId) {
        $order = $orderRepository->find($searchId);
        if ($order && !$order->IsArchived()) {
            $orders = [$order];
        } else {
            $this->addFlash('danger', 'Aucune commande trouvée avec cet ID.');
            $orders = [];
        }
    } else {
        $orders = $orderRepository->findBy(['isArchived' => false]);
    }
    
    return $this->render('Order/order.html.twig', [
        "orders" => $orders,
    ]);
}

    #[Route('/{id<\d+>}', name: 'app_order_show', methods: ['GET'])]
    public function showOrder(int $id, OrderRepository $orderRepository): Response
{
    $order = $orderRepository->find($id);
    return $this->render('Order/show.html.twig', [
        'order' => $order,
        
    ]);
}

    #[Route("editor/order/{id}/is-completed/update", name:"app_orders_is_completed_update")]
    public function isCompletedUpdate($id , OrderRepository $orderRepository , EntityManagerInterface $entityManager): Response {
        $order = $orderRepository->find($id);
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash("success","Modification effectuée");
        return $this->redirectToRoute("app_orders_show");
    }

    #[Route("editor/order/{id}/remove", name:"app_orders_remove")] 
    public function removeOrder($id , Order $order , EntityManagerInterface $entityManager): Response {
       $entityManager->remove($order);
       $entityManager->flush();
       $this->addFlash("danger","Votre commande a été supprimer");
       return $this->redirectToRoute("app_orders_show");
    }
    
    #[Route('/add-to-cart/{id}', name: 'app_add_to_cart')]
public function addToCart($id, SessionInterface $session): Response
{
    // Récupérer le panier depuis la session
    $cart = $session->get('cart', []);

    // Ajouter le produit au panier
    if (!isset($cart[$id])) {
        $cart[$id] = 1; // Quantité initiale
    } else {
        $cart[$id]++; // Incrémenter la quantité
    }

    // Mettre à jour la session
    $session->set('cart', $cart);

    // Rediriger vers la page précédente ou une autre page
    return $this->redirectToRoute('app_home');
}

    #[Route("/my_orders", name:"app_my_orders")] 
    public function myOrders(OrderRepository $orderRepository): Response {
        $user =$this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos commandes.');
        }
        $orders = $orderRepository->findBy(['user'=>$user]);
        return $this->render('order/my_orders.html.twig' , [
            'orders'=>$orders,
        ]);
    
    }

    #[Route("editor/order/{id}/archive", name:"app_ordes_archive")] 
    public function archiveOrder(Order $order, EntityManagerInterface $entityManager): Response
    {
        $order->setIsArchived(true);
        $entityManager->flush();
        
        $this->addFlash("success", "La commande a été archivée avec succès");
        return $this->redirectToRoute("app_orders_show");
    }

    #[Route('/editor/archived-orders', name: 'app_archived_orders')]
    public function getArchivedOrders(OrderRepository $orderRepository): Response
    {
        $archivedOrders = $orderRepository->findBy(['isArchived' => true]);
        
        return $this->render('order/archivd.html.twig', [
            "orders" => $archivedOrders,
        ]);
    }
    #[Route('/order/restore/{id}', name: 'app_order_restore')]

    public function restoreOrder(Order $order, EntityManagerInterface $entityManager): Response
{
    // Supposons que vous ayez un champ 'archived' dans votre entité Order
    $order->setisArchived(false);
    $entityManager->persist($order);
    $entityManager->flush();

    $this->addFlash('success', 'La commande #'.$order->getId().' a été restaurée avec succès.');
    
    return $this->redirectToRoute('app_archived_orders');
}

}