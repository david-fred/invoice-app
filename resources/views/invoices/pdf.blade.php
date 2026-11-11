<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px; border-bottom: 1px solid #ddd; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->number }}</h1>
    <p>Bill to: <strong>{{ $invoice->client->name }}</strong></p>
    <p>Issued {{ $invoice->issue_date->format('d M Y') }} · Due {{ $invoice->due_date->format('d M Y') }}</p>

    <table>
        <thead>
            <tr><th>Description</th><th class="right">Qty</th><th class="right">Price</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="right">
        Subtotal {{ number_format($invoice->subtotal, 2) }}<br>
        Discount −{{ number_format($invoice->discount, 2) }}<br>
        Tax ({{ $invoice->tax_rate }}%) {{ number_format($invoice->tax, 2) }}<br>
        <strong>Total {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong>
    </p>
</body>
</html>