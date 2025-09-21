<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderPaidMail;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->loadMissing(['user', 'items']);

        $recipient = $order->user?->email;
        if ($recipient === null) {
            return;
        }

        $mailable = new OrderPaidMail($order);

        if (app()->environment('production')) {
            Mail::to($recipient)->queue($mailable);
        } else {
            Mail::to($recipient)->send($mailable);
        }
    }
}
