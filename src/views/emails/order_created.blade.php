@component('mail::message')
# Hello

The following order has been created for your organisation:

@foreach($orderItems as $orderItem)
    *{{$orderItem->product->name}}
@endforeach

@component('mail::button', ['url' => $route, 'color' => 'primary'])
    Pay now
@endcomponent


Regards,

{{ config('app.name') }}

@component('mail::subcopy')
    If you’re having trouble clicking the "Pay now" button, copy and paste the URL below
    into your web browser: [{{ $route }}]({{ $route }})
@endcomponent
@endcomponent