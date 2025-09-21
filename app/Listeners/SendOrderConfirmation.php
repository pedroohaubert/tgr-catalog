<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    /**
     * The number of seconds the listener can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

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

        Mail::to($recipient)->send(new OrderPaidMail($order));
    }
}
