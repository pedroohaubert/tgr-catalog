<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {

        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento confirmado - Pedido #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.orders.paid',
            with: [
                'userName' => (string) ($this->order->user?->name ?? ''),
                'orderId' => (int) $this->order->id,
                'orderCode' => (string) $this->order->code,
                'itemCount' => (int) $this->order->items->sum('quantity'),
                'total' => (float) $this->order->total,
                'ordersUrl' => route('orders.index'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
