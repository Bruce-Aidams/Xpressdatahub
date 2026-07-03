<x-mail::message>
# Password Reset Request

Hello {{ $agentName }},

An administrator has requested a password reset for your account. Click the button below to set a new password. This link will expire in 60 minutes.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

If you did not request this reset, please ignore this email or contact support.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
