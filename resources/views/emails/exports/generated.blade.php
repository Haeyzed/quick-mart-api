<x-mail::message>
# Hello {{ $name }},

Your export file **{{ $fileName }}** has been generated successfully.

Please find the file attached to this email.

<x-mail::panel>
**Note:** If you did not request this export, please contact our support team at {{ $supportEmail }}.
</x-mail::panel>

Thanks,<br>
{{ $companyName }}
</x-mail::message>
