<x-mail::message>
# Hello {{ $mailData['name'] ?? 'Employee' }},

Your payroll for the recent period has been successfully processed. Here is a quick update on your payment status and details.

<x-mail::panel>
**Status:** Processed &#10003;<br>
**Reference No:** {{ $mailData['reference_no'] ?? 'N/A' }}<br>
**Total Net Amount:** {{ $mailData['currency'] ?? '$' }} {{ number_format((float) ($mailData['amount'] ?? 0), 2) }}
</x-mail::panel>

You can view your detailed payslip, including your basic salary, commissions, overtime, and deductions, by logging into your employee dashboard.

<x-mail::button :url="config('app.url') . '/login'" color="primary">
Access Dashboard
</x-mail::button>

If you have any questions or notice any discrepancies, please feel free to contact the HR or Finance department.

Best regards,<br>
**The {{ config('app.name') }} HR Team**
</x-mail::message>
