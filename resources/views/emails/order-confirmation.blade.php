<x-mail::message>
# Order Confirmed ✅

Hi **{{ $agentName }}**,

Your data bundle order has been placed successfully. Here's a summary:

<x-mail::panel>
| Detail | Value |
|:--|:--|
| **Order ID** | #{{ $orderId }} |
| **Network** | {{ $network }} |
| **Package** | {{ $packageSize }} |
| **Recipient** | {{ $phoneNumber }} |
| **Amount** | GH₵{{ number_format($amount, 2) }} |
| **Status** | {{ ucfirst($status) }} |
</x-mail::panel>

Your order is being processed. You will receive another notification once it is fulfilled.

<x-mail::button :url="$ordersUrl" color="blue">
View My Orders
</x-mail::button>

If you did not place this order, please contact support immediately at [support@xpressdatahub.com](mailto:support@xpressdatahub.com).

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
