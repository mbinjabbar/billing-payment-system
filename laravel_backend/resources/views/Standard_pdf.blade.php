<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Medical Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 12px; color: #111; margin:0; padding:0; line-height:1.4;">

    <!-- Container -->
    <div style="max-width: 800px; margin: 20px auto; border:1px solid #ccc; border-radius:8px; overflow:hidden; padding:20px;">

        <!-- Header -->
        <div style="text-align:center; margin-bottom:20px;">
            <h1 style="margin:0; font-size:24px; font-weight:bold;">Medical Invoice</h1>
            <p style="margin:4px 0 0 0; font-size:12px; color:#555;">MedBilling</p>
        </div>

        <!-- Bill Info -->
        <div style="margin-bottom:20px;">
            <p><strong>Invoice #: </strong>{{ $bill->bill_number ?? $bill->id }}</p>
            <p><strong>Date: </strong>{{ \Carbon\Carbon::parse($bill->bill_date)->format('d-m-Y') }}</p>
            @if($bill->due_date)
            <p><strong>Due Date: </strong>{{ \Carbon\Carbon::parse($bill->due_date)->format('d-m-Y') }}</p>
            @endif
        </div>

        <!-- Patient Info -->
        <div style="margin-bottom:20px; border-top:1px solid #ccc; border-bottom:1px solid #ccc; padding:10px 0;">
            <h3 style="margin:0 0 10px 0; font-size:14px; font-weight:bold;">Patient Information</h3>
            <p><strong>Name: </strong>{{ $bill->visit->appointment->patientCase->patient->full_name }}</p>
            <p><strong>Gender: </strong>{{ $bill->visit->appointment->patientCase->patient->gender }}</p>
            <p><strong>Case #: </strong>{{ $bill->visit->appointment->patientCase->case_number }}</p>
            <p><strong>Case Type: </strong>{{ $bill->visit->appointment->patientCase->case_type }}</p>
        </div>

        <!-- Procedure Table -->
        <div style="margin-bottom:20px;">
            <h3 style="margin:0 0 10px 0; font-size:14px; font-weight:bold;">Procedures</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f0f0f0;">
                        <th style="border:1px solid #ccc; padding:6px; text-align:left;">Code</th>
                        <th style="border:1px solid #ccc; padding:6px; text-align:left;">Description</th>
                        <th style="border:1px solid #ccc; padding:6px; text-align:right;">Charge</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->procedure_codes ?? [] as $code)
                    <tr>
                        <td style="border:1px solid #ccc; padding:6px;">{{ $code['code'] }}</td>
                        <td style="border:1px solid #ccc; padding:6px;">{{ $code['name'] }}</td>
                        <td style="border:1px solid #ccc; padding:6px; text-align:right;">${{ number_format($code['standard_charge'] ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Charges Summary -->
        <div style="margin-bottom:20px; border-top:1px solid #ccc; padding-top:10px;">
            <h3 style="margin:0 0 10px 0; font-size:14px; font-weight:bold;">Summary</h3>
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                    <tr>
                        <td>Gross Charges</td>
                        <td style="text-align:right;">${{ number_format($bill->charges ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Insurance ({{ $bill->insurance_coverage ?? 0 }}%)</td>
                        <td style="text-align:right;">-${{ number_format(($bill->charges ?? 0) * ($bill->insurance_coverage/100), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td style="text-align:right;">-${{ number_format($bill->discount_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax</td>
                        <td style="text-align:right;">+${{ number_format($bill->tax_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr style="font-weight:bold; border-top:1px solid #ccc;">
                        <td>Total Amount</td>
                        <td style="text-align:right;">${{ number_format($bill->bill_amount ?? 0, 2) }}</td>
                    </tr>
                    <tr style="font-weight:bold;">
                        <td>Outstanding Amount</td>
                        <td style="text-align:right;">${{ number_format($bill->outstanding_amount ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes -->
        <div style="margin-bottom:20px;">
            <h3 style="margin:0 0 10px 0; font-size:14px; font-weight:bold;">Notes</h3>
            <p>{{ $bill->notes ?? 'N/A' }}</p>
        </div>

        <!-- Footer -->
        <div style="text-align:center; font-size:10px; color:#555; border-top:1px solid #ccc; padding-top:10px;">
            Generated by Medical Billing System • {{ \Carbon\Carbon::parse($bill->bill_date)->format('d-m-Y') }}
        </div>

    </div>

</body>
</html>