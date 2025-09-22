<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    public $timeout = 120;

    public function __construct() {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);

        $recipient = $order->user?->email;
        if ($recipient === null) {
            return;
        }

        Mail::to($recipient)->send(new OrderPaidMail($order));
    }
}
