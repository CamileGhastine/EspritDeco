<?php

namespace App\EventSubscriber;

use App\Service\Cart\CartHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class CartSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CartHandler $cartHandler)
    {
    }
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $this->cartHandler->persistCart();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }
}
