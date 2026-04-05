<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NF2 Medical Bill</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
        h2, h3, h4 {
            margin: 0;
            padding: 0;
        }
        .center {
            text-align: center;
        }
        .section {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
        }
        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <h2 class="center">NF2 Medical Bill</h2>
    <hr>

    <div class="section">
        <p><strong>Bill #:</strong> {{ $bill->bill_number ?? $bill->id }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($bill->bill_date)->format('d-m-Y') }}</p>
    </div>

    <div class="section">
        <h4>Patient Information</h4>
        <p><strong>Name:</strong> {{ $bill->visit->appointment->patientCase->patient->full_name }}</p>
        <p><strong>Gender:</strong> {{ $bill->visit->appointment->patientCase->patient->gender }}</p>
        <p><strong>Case #:</strong> {{ $bill->visit->appointment->patientCase->id }}</p>
        <p><strong>Case Type:</strong> {{ $bill->visit->appointment->patientCase->case_type}}</p>
    </div>

    <div class="section">
        <h4>Charges</h4>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Charges</td>
                    <td>{{ number_format($bill->charges, 2) }}</td>
                </tr>
                <tr>
                    <td>Insurance Coverage</td>
                    <td>{{ number_format($bill->insurance_coverage, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>{{ number_format($bill->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td>{{ number_format($bill->tax_amount, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Total Amount</td>
                    <td>{{ number_format($bill->bill_amount, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Outstanding Amount</td>
                    <td>{{ number_format($bill->outstanding_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <p><strong>Notes:</strong> {{ $bill->notes ?? 'N/A' }}</p>
    </div>

</body>
</html>