<x-mail::message>
# Password Reset Request 🔒

Hi **{{ $agentName }}**,

We received a request to reset the password for your account. Use the OTP code or button below to proceed.

<x-mail::panel>
**Your OTP Code:** `{{ $otp }}`

This code expires in **60 minutes**.
</x-mail::panel>

<x-mail::button :url="$resetUrl" color="blue">
Reset My Password
</x-mail::button>

If you did not request a password reset, please ignore this email. Your account remains secure.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
