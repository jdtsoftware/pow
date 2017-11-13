<?php

namespace JDT\Pow\Mail;

use Illuminate\Mail\Mailable;
use JDT\Pow\Interfaces\Entities\Order;

class OrderCreated extends Mailable
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('pow::emails.order_created')
            ->subject('Order '.$this->order->getUuid().' created')
            ->with([
                'orderItems' => $this->order->items,
                'route' => route('order-checkout', [
                    'uuid' => $this->order->getUuid(),
                ]),
            ]);
    }
}
