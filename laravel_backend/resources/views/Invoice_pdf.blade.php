<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $bill->bill_number }}</title>
</head>
<body style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; padding: 0; line-height: 1.5;">

<div style="padding: 24px;">

    {{-- ── HEADER ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 3px solid #005bc4; padding-bottom: 12px; margin-bottom: 12px;">
        <tr>
            <td style="vertical-align: top; width: 55%;">
                <div style="font-size: 20px; font-weight: bold; color: #005bc4;">{{ $settings['clinic_name'] ?? 'MedBilling' }}</div>
                <div style="font-size: 9px; color: #555; margin-top: 3px; line-height: 1.6;">
                    {{ $settings['clinic_address'] ?? '' }}<br>
                    @if(!empty($settings['clinic_phone'])) Tel: {{ $settings['clinic_phone'] }} @endif
                    @if(!empty($settings['clinic_email'])) &nbsp;|&nbsp; {{ $settings['clinic_email'] }} @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 45%;">
                <div style="font-size: 24px; font-weight: bold; color: #005bc4; letter-spacing: 3px;">INVOICE</div>
                <div style="font-size: 9px; color: #555; margin-top: 4px; line-height: 1.7;">
                    <strong style="color:#1a1a2e;">Invoice #:</strong> {{ $bill->bill_number }}<br>
                    <strong style="color:#1a1a2e;">Bill Date:</strong> {{ \Carbon\Carbon::parse($bill->bill_date)->format('M d, Y') }}
                    @if($bill->due_date) &nbsp;&nbsp; <strong style="color:#1a1a2e;">Due:</strong> {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }} @endif<br>
                    <strong style="color:#1a1a2e;">Generated:</strong> {{ now()->format('M d, Y') }}
                </div>
                @php
                    $statusColors = [
                        'Paid'        => ['bg' => '#dcfce7', 'color' => '#166534'],
                        'Pending'     => ['bg' => '#fff7ed', 'color' => '#9a3412'],
                        'Partial'     => ['bg' => '#dbeafe', 'color' => '#1e40af'],
                        'Draft'       => ['bg' => '#f3e8ff', 'color' => '#6b21a8'],
                        'Cancelled'   => ['bg' => '#f1f5f9', 'color' => '#475569'],
                        'Written Off' => ['bg' => '#f1f5f9', 'color' => '#475569'],
                    ];
                    $sc = $statusColors[$bill->status] ?? $statusColors['Pending'];
                @endphp
                <div style="display:inline-block; margin-top:6px; padding: 3px 10px; background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border: 1px solid {{ $sc['color'] }};">
                    {{ $bill->status }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── PATIENT + CASE ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 8px;">
        <tr>
            {{-- Patient --}}
            <td style="width: 50%; padding-right: 6px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #cbd5e1;">
                    <tr>
                        <td colspan="2" style="background: #f1f5f9; padding: 4px 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #cbd5e1;">
                            Patient
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 7px 10px; vertical-align: top; width: 55%; border-right: 1px solid #e2e8f0;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Name</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $bill->visit->appointment->patientCase->patient->full_name }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->visit->appointment->patientCase->patient->gender }}</div>
                        </td>
                        <td style="padding: 7px 10px; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Contact</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $bill->visit->appointment->patientCase->patient->phone }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->visit->appointment->patientCase->patient->email }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Case & Visit --}}
            <td style="width: 50%; padding-left: 6px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #cbd5e1;">
                    <tr>
                        <td colspan="3" style="background: #f1f5f9; padding: 4px 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #cbd5e1;">
                            Visit &amp; Case
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 7px 10px; vertical-align: top; width: 38%; border-right: 1px solid #e2e8f0;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Case #</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $bill->visit->appointment->patientCase->case_number }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->visit->appointment->patientCase->case_type }}</div>
                        </td>
                        <td style="padding: 7px 10px; vertical-align: top; width: 38%; border-right: 1px solid #e2e8f0;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Visit Date</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ \Carbon\Carbon::parse($bill->visit->visit_date)->format('M d, Y') }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">Dr. {{ $bill->visit->appointment->doctor_name }}</div>
                        </td>
                        <td style="padding: 7px 10px; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Type</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $bill->visit->appointment->patientCase->car_accident ? 'Auto' : 'Health' }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->visit->appointment->patientCase->priority }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── INSURANCE ── --}}
    @if($bill->insurance_firm)
    <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #cbd5e1; margin-bottom: 10px;">
        <tr>
            <td colspan="4" style="background: #f1f5f9; padding: 4px 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #cbd5e1;">
                Insurance
            </td>
        </tr>
        <tr>
            <td style="padding: 7px 10px; vertical-align: top; width: 30%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Provider</div>
                <div style="font-weight: bold; font-size: 10px;">{{ $bill->insurance_firm->name }}</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->insurance_firm->firm_type }} Insurance</div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 20%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Carrier Code</div>
                <div style="font-weight: bold; font-size: 10px;">{{ $bill->insurance_firm->carrier_code ?? '—' }}</div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 28%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Contact</div>
                <div style="font-weight: bold; font-size: 10px;">{{ $bill->insurance_firm->contact_person ?? '—' }}</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $bill->insurance_firm->phone ?? '' }}</div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 22%;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Coverage</div>
                <div style="font-weight: bold; font-size: 11px; color: #005bc4;">{{ $bill->insurance_coverage ?? 0 }}%</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">= ${{ number_format(($bill->charges ?? 0) * ($bill->insurance_coverage / 100), 2) }} off</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- ── PROCEDURES ── --}}
    <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #005bc4; margin-bottom: 5px; padding-bottom: 3px; border-bottom: 1px solid #bfdbfe;">
        Procedure Details
    </div>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 10px;">
        <thead>
            <tr style="background: #005bc4; color: #ffffff;">
                <th style="padding: 6px 10px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; width: 15%;">Code</th>
                <th style="padding: 6px 10px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; width: 65%;">Description</th>
                <th style="padding: 6px 10px; text-align: right; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; width: 20%;">Charge</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->procedure_codes ?? [] as $index => $code)
            <tr style="background: {{ $index % 2 === 0 ? '#ffffff' : '#f8fafc' }}; border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 6px 10px;">
                    <span style="background: #dbeafe; color: #1e40af; padding: 2px 6px; font-size: 8px; font-weight: bold;">{{ $code['code'] }}</span>
                </td>
                <td style="padding: 6px 10px; color: #1a1a2e;">{{ $code['name'] }}</td>
                <td style="padding: 6px 10px; text-align: right; font-weight: bold;">${{ number_format($code['standard_charge'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── NOTES + PAYMENTS (left) | SUMMARY (right) ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
        <tr>
            {{-- Left: Notes + Payment History --}}
            <td style="width: 48%; vertical-align: top; padding-right: 10px;">
                @if($bill->notes)
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #005bc4; margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px solid #bfdbfe;">Notes</div>
                <div style="background: #f8fafc; border-left: 3px solid #005bc4; padding: 6px 10px; font-size: 9px; color: #475569; line-height: 1.5; margin-bottom: 8px; border: 1px solid #e2e8f0;">
                    {{ $bill->notes }}
                </div>
                @endif

                @if($bill->payments && $bill->payments->count() > 0)
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #005bc4; margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px solid #bfdbfe;">Payment History</div>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="padding: 4px 6px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #64748b; text-align: left; border-bottom: 1px solid #e2e8f0;">Payment #</th>
                            <th style="padding: 4px 6px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #64748b; text-align: left; border-bottom: 1px solid #e2e8f0;">Date</th>
                            <th style="padding: 4px 6px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #64748b; text-align: left; border-bottom: 1px solid #e2e8f0;">Mode</th>
                            <th style="padding: 4px 6px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #64748b; text-align: right; border-bottom: 1px solid #e2e8f0;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->payments as $payment)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 4px 6px; font-size: 9px;">{{ $payment->payment_number }}</td>
                            <td style="padding: 4px 6px; font-size: 9px;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                            <td style="padding: 4px 6px; font-size: 9px;">{{ $payment->payment_mode }}</td>
                            <td style="padding: 4px 6px; font-size: 9px; text-align: right; font-weight: bold; color: #16a34a;">${{ number_format($payment->amount_paid, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </td>

            {{-- Right: Financial Summary --}}
            <td style="width: 52%; vertical-align: top;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #005bc4; margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px solid #bfdbfe;">Financial Summary</div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 5px 0; font-size: 10px;">Gross Charges</td>
                        <td style="padding: 5px 0; font-size: 10px; text-align: right; font-weight: bold;">${{ number_format($bill->charges ?? 0, 2) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 5px 0; font-size: 10px;">Insurance ({{ $bill->insurance_coverage ?? 0 }}%)</td>
                        <td style="padding: 5px 0; font-size: 10px; text-align: right; font-weight: bold; color: #16a34a;">-${{ number_format(($bill->charges ?? 0) * ($bill->insurance_coverage / 100), 2) }}</td>
                    </tr>
                    @if(($bill->discount_amount ?? 0) > 0)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 5px 0; font-size: 10px;">Discount</td>
                        <td style="padding: 5px 0; font-size: 10px; text-align: right; font-weight: bold; color: #16a34a;">-${{ number_format($bill->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if(($bill->tax_amount ?? 0) > 0)
                    @php
                        $afterInsDisc = ($bill->charges ?? 0) - (($bill->charges ?? 0) * ($bill->insurance_coverage / 100)) - ($bill->discount_amount ?? 0);
                        $taxAmt = $afterInsDisc * ($bill->tax_amount / 100);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 5px 0; font-size: 10px;">Tax ({{ $bill->tax_amount }}%)</td>
                        <td style="padding: 5px 0; font-size: 10px; text-align: right; font-weight: bold; color: #9a3412;">+${{ number_format($taxAmt, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 8px 0 4px 0; font-size: 13px; font-weight: bold; color: #005bc4; border-top: 2px solid #005bc4;">Total Bill Amount</td>
                        <td style="padding: 8px 0 4px 0; font-size: 13px; font-weight: bold; color: #005bc4; text-align: right; border-top: 2px solid #005bc4;">${{ number_format($bill->bill_amount ?? 0, 2) }}</td>
                    </tr>
                    @if(($bill->paid_amount ?? 0) > 0)
                    <tr>
                        <td style="padding: 3px 0; font-size: 10px; font-weight: bold; color: #16a34a;">Amount Paid</td>
                        <td style="padding: 3px 0; font-size: 10px; font-weight: bold; color: #16a34a; text-align: right;">-${{ number_format($bill->paid_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 3px 0; font-size: 11px; font-weight: bold; color: #dc2626;">Outstanding Balance</td>
                        <td style="padding: 3px 0; font-size: 11px; font-weight: bold; color: #dc2626; text-align: right;">${{ number_format($bill->outstanding_amount ?? 0, 2) }}</td>
                    </tr>
                </table>

                @if($bill->due_date && $bill->status !== 'Paid')
                <div style="margin-top: 8px; padding: 6px 10px; background: #fff7ed; border: 1px solid #fed7aa; font-size: 8.5px; color: #9a3412;">
                    <strong>Payment Due:</strong> {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}
                    @if(\Carbon\Carbon::parse($bill->due_date)->isPast()) — <strong>OVERDUE</strong> @endif
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── FOOTER ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px;">
        <tr>
            <td style="font-size: 8px; color: #94a3b8; vertical-align: middle;">
                <span style="font-weight: bold; color: #005bc4; font-size: 9px;">{{ $settings['clinic_name'] ?? 'MedBilling' }}</span><br>
                {{ $settings['invoice_footer'] ?? 'Thank you for your payment.' }}
            </td>
            <td style="font-size: 8px; color: #94a3b8; text-align: right; vertical-align: middle;">
                Invoice {{ $bill->bill_number }}<br>
                Generated {{ now()->format('M d, Y') }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>