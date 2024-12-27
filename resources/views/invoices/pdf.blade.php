<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Invoice {{ $invoice->number }}</title>
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #6366F1;
            --text-primary: #1F2937;
            --text-secondary: #4B5563;
            --background: #F9FAFB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
        }

        .invoice-header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand img {
            height: 40px;
            width: auto;
        }

        .invoice-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .invoice-number {
            font-size: 1rem;
            opacity: 0.9;
        }

        .invoice-body {
            padding: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .info-section h2 {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-item {
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        .info-item strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }

        .invoice-table th {
            background-color: var(--background);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }

        .invoice-table td {
            padding: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .plan-details {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .plan-details p {
            margin: 0.25rem 0;
        }

        .total-section {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 2rem;
            background-color: var(--background);
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .invoice-footer {
            text-align: center;
            padding: 2rem;
            background-color: white;
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid #E5E7EB;
        }

        .paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 4rem;
            color: rgba(39, 174, 96, 0.3);
            font-weight: bold;
            text-transform: uppercase;
            border: 0.5rem solid rgba(39, 174, 96, 0.3);
            padding: 1rem;
            border-radius: 1rem;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .invoice-container {
                margin: 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .invoice-header-content {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container" style="position: relative;">
        <div class="paid-stamp">PAID</div>
        <div class="invoice-header">
            <div class="invoice-header-content">
                <div class="brand">
                    <img src="https://via.placeholder.com/150x50?text=Brand" alt="Brand Logo">
                    <div>
                        <div class="invoice-title">Invoice</div>
                        <div class="invoice-number">#{{ $invoice->number }}</div>
                    </div>
                </div>
                <div class="company-info">
                    <strong>Your Company Name</strong>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="info-grid">
                <div class="info-section">
                    <h2>Bill To</h2>
                    <div class="info-item">
                        <strong>{{ $user->name }}</strong>
                    </div>
                    <div class="info-item">{{ $user->email }}</div>
                </div>

                <div class="info-section">
                    <h2>Invoice Details</h2>
                    <div class="info-item">
                        <strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}
                    </div>
                    <div class="info-item">
                        <strong>Expires At:</strong> {{ $invoice->expires_at->format('F d, Y') }}
                    </div>
                    <div class="info-item">
                        <strong>Payment Status:</strong> <span style="color: #27AE60;">Paid</span>
                    </div>
                    <div class="info-item">
                        <strong>Payment Date:</strong> {{ $invoice->paid_at->format('F d, Y') }}
                    </div>
                </div>
            </div>

            <table class="invoice-table">
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
                            <div class="plan-details">
                                @if(isset($subscription->plan->features))
                                <p><strong>Features:</strong> {{ $subscription->plan->features->pluck('name')->implode(', ') }}</p>
                                @endif
                                <p><strong>Duration:</strong> {{ $subscription->plan->duration() ?? 'N/A' }}</p>
                                <p><strong>Billing Cycle:</strong> {{ $subscription->plan->billing_cycle ?? 'N/A' }}</p>
                            </div>
                        </td>
                        <td>${{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-amount">
                    Total: ${{ number_format($invoice->total, 2) }}
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your payment and for subscribing to {{ $subscription->plan->name }}!</p>
            <p>Payment received on {{ $invoice->paid_at->format('F d, Y') }}</p>
        </div>
    </div>
</body>
</html>
