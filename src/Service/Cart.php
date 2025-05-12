<?php 

namespace App\Service;
use App\Repository\ProductRepository;

class Cart {
    public function __construct(private readonly ProductRepository $productRepository) {}

    public function getCart($session): array {
        $cart = $session->get('cart', []);
        $cartWithData = [];
        $total = 0;
        
        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            
            if ($product) {
                // Vérifier que la quantité ne dépasse pas le stock
                $maxQuantity = $product->getStock();
                $quantity = min($quantity, $maxQuantity);
                
                // Mettre à jour la session avec la quantité corrigée si nécessaire
                if ($cart[$id] != $quantity) {
                    $cart[$id] = $quantity;
                    $session->set('cart', $cart);
                }
                
                $cartWithData[] = [
                    "product" => $product,
                    "quantity" => $quantity,
                    "maxQuantity" => $maxQuantity // Ajouté pour utilisation dans le template
                ];
                $total += $product->getPrice() * $quantity;
            } else {
                unset($cart[$id]);
                $session->set('cart', $cart);
            }
        }
    
        return [
            'cart' => $cartWithData,
            'total' => $total,
        ];
    }
}