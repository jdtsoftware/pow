@component('mail::message')
    # Hello

    The following order has been approved:

    <ul>
    @foreach($orderItems as $orderItem)
        <li>{{$orderItem->product->name}}</li>
    @endforeach
    </ul>

    @component('mail::button', ['url' => $route, 'color' => 'primary'])
        Pay now
    @endcomponent

    Regards,<br>{{ config('app.name') }}

    @component('mail::subcopy')
        If you’re having trouble clicking the "Pay now" button, copy and paste the URL below
        into your web browser: [{{ $route }}]({{ $route }})
    @endcomponent
@endcomponent
