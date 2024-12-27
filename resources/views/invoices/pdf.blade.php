<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Invoice {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6f9fc;
        }

        .invoice-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 50px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section p {
            margin: 5px 0;
            color: #555;
        }

        .info-section strong {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: #f6f9fc;
            color: #333;
        }

        .plan-details {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .total {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="header">
            <img src="https://via.placeholder.com/150x50?text=Brand" alt="Brand">
            <h1>Subscription Invoice</h1>
        </div>
        <div class="info-section">
            <p><strong>Invoice Number:</strong> {{ $invoice->number }}</p>
            <p><strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</p>
            <p><strong>Bill To:</strong> {{ $user->name }} ({{ $user->email }})</p>
            <p><strong>Bill From:</strong> Your Company Name</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Details</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $subscription->plan->name }}</td>
                    <td>
                        <td>
                            <div class="plan-details">
                                @if(isset($subscription->plan->features))
                                    <p><strong>Features:</strong> {{ $subscription->plan->features }}</p>
                                @endif
                                <p><strong>Duration:</strong> {{ $subscription->plan->duration() ?? 'N/A' }}</p>
                                <p><strong>Billing Cycle:</strong> {{ $subscription->plan->billing_cycle ?? 'N/A' }}</p>
                            </div>
                        </td>
                    </td>
                    <td>${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="total">
            <p>Total: ${{ number_format($invoice->total, 2) }}</p>
        </div>
        <div class="footer">
            <p>Thank you for subscribing to {{ $subscription->plan->name }}!</p>
        </div>
    </div>
</body>

</html>
