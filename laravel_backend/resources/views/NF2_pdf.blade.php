<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NF2 Medical Bill - {{ $bill->bill_number }}</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; line-height: 1.4; margin: 0; padding: 0; }
    h2, h3 { margin: 0; padding: 0; }
    .container { width: 100%; padding: 20px; }
    .center { text-align: center; }
    .section { margin-top: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    table, th, td { border: 1px solid #000; }
    th, td { padding: 5px; font-size: 11px; vertical-align: top; }
    th { background: #f0f0f0; }
    .bold { font-weight: bold; }
    .small { font-size: 10px; }
    .right { text-align: right; }
</style>
</head>
<body>
<div class="container">

    <h2 class="center">NF2 Medical Bill</h2>
    <p class="center small">Bill #: {{ $bill->bill_number ?? $bill->id }} | Date: {{ \Carbon\Carbon::parse($bill->bill_date)->format('d-m-Y') }}</p>

    <!-- Patient Information -->
    <div class="section">
        <h3>Patient Information</h3>
        <table>
            <tr>
                <th>Name</th>
                <td>{{ $bill->visit->appointment->patientCase->patient->full_name }}</td>
                <th>Case Number</th>
                <td>{{ $bill->visit->appointment->patientCase->case_number }}</td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td>{{ \Carbon\Carbon::parse($bill->visit->appointment->patientCase->patient->date_of_birth)->format('d-m-Y') }}</td>
                <th>Gender</th>
                <td>{{ $bill->visit->appointment->patientCase->patient->gender }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $bill->visit->appointment->patientCase->patient->address }}, {{ $bill->visit->appointment->patientCase->patient->city }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $bill->visit->appointment->patientCase->patient->phone }}</td>
                <th>Case Type / Category</th>
                <td>{{ $bill->visit->appointment->patientCase->case_type }} / {{ $bill->visit->appointment->patientCase->case_category }}</td>
            </tr>
        </table>
    </div>

    <!-- Insurance Information -->
    <div class="section">
        <h3>Insurance Information</h3>
        <table>
            <tr>
                <th>Provider</th>
                <td>{{ $bill->insurance_firm->name }}</td>
                <th>Carrier Code</th>
                <td>{{ $bill->insurance_firm->carrier_code }}</td>
            </tr>
            <tr>
                <th>Contact Person</th>
                <td>{{ $bill->insurance_firm->contact_person }}</td>
                <th>Phone / Email</th>
                <td>{{ $bill->insurance_firm->phone }} / {{ $bill->insurance_firm->email }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td colspan="3">{{ $bill->insurance_firm->address }}</td>
            </tr>
        </table>
    </div>

    <!-- Clinical & Procedures -->
    <div class="section">
        <h3>Clinical Information</h3>
        <table>
            <tr>
                <th>Visit Date</th>
                <td>{{ \Carbon\Carbon::parse($bill->visit->visit_date)->format('d-m-Y') }}</td>
                <th>Diagnosis</th>
                <td>{{ $bill->visit->diagnosis }}</td>
            </tr>
        </table>

        <h4>Procedure Codes</h4>
        <table>
            <tr>
                <th>Code</th>
                <th>Description</th>
                <th>Standard Charge</th>
            </tr>
            @foreach($bill->procedure_codes as $proc)
            <tr>
                <td class="center">{{ $proc['code'] ?? '-' }}</td>
                <td>{{ $proc['name'] ?? '-' }}</td>
                <td class="right">${{ number_format((float)$proc['standard_charge'],2) }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Charges Summary -->
    <div class="section">
        <h3>Charges Summary</h3>
        <table>
            <tr>
                <th>Gross Charges</th>
                <td class="right">${{ number_format((float)$bill->charges,2) }}</td>
            </tr>
            <tr>
                <th>Insurance Coverage ({{ $bill->insurance_coverage }}%)</th>
                <td class="right">-${{ number_format((float)($bill->charges * ($bill->insurance_coverage/100)),2) }}</td>
            </tr>
            <tr>
                <th>Discount</th>
                <td class="right">-${{ number_format((float)$bill->discount_amount,2) }}</td>
            </tr>
            <tr>
                <th>Tax</th>
                <td class="right">${{ number_format((float)$bill->tax_amount,2) }}</td>
            </tr>
            <tr class="bold">
                <th>Total Amount</th>
                <td class="right">${{ number_format((float)$bill->bill_amount,2) }}</td>
            </tr>
            <tr class="bold">
                <th>Outstanding Amount</th>
                <td class="right">${{ number_format((float)$bill->outstanding_amount,2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Notes & Footer -->
    <div class="section">
        <h4>Notes</h4>
        <p>{{ $bill->notes ?? 'N/A' }}</p>
    </div>

    <div class="section small center">
        <p>Generated by MedBilling • {{ \Carbon\Carbon::parse($bill->bill_date)->format('F d, Y') }}</p>
    </div>

</div>
</body>
</html>