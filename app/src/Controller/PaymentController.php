<?php

namespace App\Controller;

use App\Entity\CartLine;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\OrderRepository;
use App\Service\Cart\CartHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PaymentController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em
    ) {}

    #[Route('/payment', name: 'app_payment')]
    #[IsGranted('ROLE_USER')]
    public function pay(
        Request $request, 
        UrlGeneratorInterface $urlGenerator,
        CartHandler $cartHandler
    ): Response
    {
        $user = $this->getUser();
        $order = $this->orderRepository->findOneBy(['user' => $user]);

        if (!$order) {
            $this->addFlash('danger', 'Aucune commande trouvée.');
            
            return $this->redirectToRoute('app_product_index');
        }

        //supprimer les lignes excistente
        foreach ($order->getOrderLines() as $existingLine) {
            $order->removeOrderLine($existingLine);
            $this->em->remove($existingLine);
        }

        $cart = $cartHandler->getCartDetails();

        $lineItems = [];
        foreach ($cart['items'] as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => $product->getPrice() * 100,
                    'product_data' => [
                        'name' => $product->getTitle(),
                    ],
                ],
                'quantity' => $quantity,
            ];

            $orderLine = new OrderLine;
            $orderLine->setProduct($product)
                ->setQuantity($quantity)
                ->setUnitPrice($product->getPrice())
                ;
            $order->addOrderLine($orderLine);
        }

        // Paiement Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate(
                'app_payment_success', 
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
                ),
            'cancel_url' => $urlGenerator->generate(
                'app_payment_cancel', 
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
                ),
        ]);

        $order->setTotalAmount($cart['totalPrice']);

        $this->em->flush();
     
        // Redirection vers le formulaire de paiement géré par Stripe
        return $this->redirect($session->url);    
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(): Response
    {
        $this->addFlash('success', 'Votre payement a été réalisé avec succès.');

        $payment = $this->orderRepository->findOneby(['user' => $this->getUser()]);
        $payment->setStatus(Order::PAID);
        $this->em->flush();

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('danger', 'Votre payement a échoue. Essayez à nouveau.');

        return $this->redirectToRoute('app_product_index');
    }
}
