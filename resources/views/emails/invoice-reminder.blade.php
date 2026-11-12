<p>Hi {{ $invoice->client->name }},</p>

<p>Invoice {{ $invoice->number }} for {{ $invoice->currency }} {{ number_format($invoice->balance(), 2) }}
   was due on {{ $invoice->due_date->format('d M Y') }}, {{ $invoice->due_date->diffInDays(today()) }} days ago.</p>

<p>Please arrange payment at your earliest convenience. If you've already paid, please ignore this reminder.</p>