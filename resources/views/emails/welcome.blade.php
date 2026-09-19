<x-mail::message>
# Welcome to {{ config('app.name') }}! 🎉

Hi **{{ $agentName }}**,

Your account has been created successfully. Here are your login details:

<x-mail::panel>
**Username:** {{ $username }}
</x-mail::panel>

You can now log in and start using the platform. If your account is pending approval, you'll be notified once an administrator reviews it.

<x-mail::button :url="$loginUrl" color="blue">
Login to Your Account
</x-mail::button>

If you have any questions, feel free to contact our support team at [support@xpressdatahub.com](mailto:support@xpressdatahub.com).

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
