<p>Hi {{ $invoice->client->name }},</p>
<p>Invoice {{ $invoice->number }} for {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
   is due on {{ $invoice->due_date->format('d M Y') }}.</p>