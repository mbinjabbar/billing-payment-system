<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $payment->payment_number }}</title>
</head>
<body style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; padding: 0; line-height: 1.5;">

<div style="padding: 24px;">

    {{-- ── HEADER ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 3px solid #16a34a; padding-bottom: 12px; margin-bottom: 14px;">
        <tr>
            <td style="vertical-align: top; width: 55%;">
                <div style="font-size: 20px; font-weight: bold; color: #16a34a;">{{ $settings['clinic_name'] ?? 'MedBilling' }}</div>
                <div style="font-size: 9px; color: #555; margin-top: 3px; line-height: 1.6;">
                    {{ $settings['clinic_address'] ?? '' }}<br>
                    @if(!empty($settings['clinic_phone'])) Tel: {{ $settings['clinic_phone'] }} @endif
                    @if(!empty($settings['clinic_email'])) &nbsp;|&nbsp; {{ $settings['clinic_email'] }} @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 45%;">
                <div style="font-size: 24px; font-weight: bold; color: #16a34a; letter-spacing: 3px;">RECEIPT</div>
                <div style="font-size: 9px; color: #555; margin-top: 4px; line-height: 1.7;">
                    <strong style="color:#1a1a2e;">Receipt #:</strong> {{ $payment->payment_number }}<br>
                    <strong style="color:#1a1a2e;">Payment Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}<br>
                    <strong style="color:#1a1a2e;">Generated:</strong> {{ now()->format('M d, Y') }}
                </div>
                @php
                    $statusColors = [
                        'Completed' => ['bg' => '#dcfce7', 'color' => '#166534'],
                        'Pending'   => ['bg' => '#fff7ed', 'color' => '#9a3412'],
                        'Failed'    => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                        'Refunded'  => ['bg' => '#f1f5f9', 'color' => '#475569'],
                    ];
                    $sc = $statusColors[$payment->payment_status] ?? $statusColors['Completed'];
                @endphp
                <div style="display:inline-block; margin-top:6px; padding: 3px 10px; background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border: 1px solid {{ $sc['color'] }};">
                    {{ $payment->payment_status }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── PATIENT + BILL INFO ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
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
                            <div style="font-weight: bold; font-size: 10px;">{{ $payment->bill->visit->appointment->patientCase->patient->full_name }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $payment->bill->visit->appointment->patientCase->patient->gender }}</div>
                        </td>
                        <td style="padding: 7px 10px; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Contact</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $payment->bill->visit->appointment->patientCase->patient->phone }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $payment->bill->visit->appointment->patientCase->patient->email }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Bill Info --}}
            <td style="width: 50%; padding-left: 6px; vertical-align: top;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #cbd5e1;">
                    <tr>
                        <td colspan="2" style="background: #f1f5f9; padding: 4px 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #cbd5e1;">
                            Bill Reference
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 7px 10px; vertical-align: top; width: 50%; border-right: 1px solid #e2e8f0;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Bill #</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $payment->bill->bill_number }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ \Carbon\Carbon::parse($payment->bill->bill_date)->format('M d, Y') }}</div>
                        </td>
                        <td style="padding: 7px 10px; vertical-align: top;">
                            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Case #</div>
                            <div style="font-weight: bold; font-size: 10px;">{{ $payment->bill->visit->appointment->patientCase->case_number }}</div>
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 1px;">{{ $payment->bill->visit->appointment->patientCase->case_type }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── PAYMENT DETAILS ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #cbd5e1; margin-bottom: 10px;">
        <tr>
            <td colspan="4" style="background: #f1f5f9; padding: 4px 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #cbd5e1;">
                Payment Details
            </td>
        </tr>
        <tr>
            <td style="padding: 7px 10px; vertical-align: top; width: 25%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Payment Mode</div>
                <div style="font-weight: bold; font-size: 10px;">{{ $payment->payment_mode }}</div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 25%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">
                    @if($payment->payment_mode === 'Check') Check Number
                    @elseif($payment->payment_mode === 'Insurance') Claim Reference
                    @else Transaction Ref @endif
                </div>
                <div style="font-weight: bold; font-size: 10px;">
                    @if($payment->payment_mode === 'Check')
                        {{ $payment->check_number ?? '—' }}
                    @else
                        {{ $payment->transaction_reference ?? '—' }}
                    @endif
                </div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 25%; border-right: 1px solid #e2e8f0;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">
                    @if($payment->payment_mode === 'Insurance') Insurance Company
                    @elseif($payment->bank_name) Bank
                    @else Bank @endif
                </div>
                <div style="font-weight: bold; font-size: 10px;">{{ $payment->bank_name ?? '—' }}</div>
            </td>
            <td style="padding: 7px 10px; vertical-align: top; width: 25%;">
                <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 2px;">Received By</div>
                <div style="font-weight: bold; font-size: 10px;">{{ $payment->receiver->first_name ?? '' }} {{ $payment->receiver->last_name ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- ── FINANCIAL SUMMARY ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 14px;">
        <tr>
            {{-- Left: Notes --}}
            <td style="width: 48%; vertical-align: top; padding-right: 10px;">
                @if($payment->notes)
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #16a34a; margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px solid #bbf7d0;">Notes</div>
                <div style="background: #f8fafc; border-left: 3px solid #16a34a; padding: 6px 10px; font-size: 9px; color: #475569; line-height: 1.5; border: 1px solid #e2e8f0;">
                    {{ $payment->notes }}
                </div>
                @endif
            </td>

            {{-- Right: Summary --}}
            <td style="width: 52%; vertical-align: top;">
                <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #16a34a; margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px solid #bbf7d0;">Financial Summary</div>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 4px 0; font-size: 10px;">Total Bill Amount</td>
                        <td style="padding: 4px 0; font-size: 10px; text-align: right; font-weight: bold;">${{ number_format($payment->bill->bill_amount, 2) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 4px 0; font-size: 10px;">Previous Payments</td>
                        <td style="padding: 4px 0; font-size: 10px; text-align: right; font-weight: bold; color: #16a34a;">
                            -${{ number_format($payment->bill->paid_amount - $payment->amount_paid, 2) }}
                        </td>
                    </tr>

                    {{-- Highlighted payment row --}}
                    <tr style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0;">
                        <td style="padding: 6px 6px; font-size: 11px; font-weight: bold; color: #166534;">Amount Paid (This Payment)</td>
                        <td style="padding: 6px 6px; font-size: 13px; font-weight: bold; color: #166534; text-align: right;">
                            ${{ number_format($payment->amount_paid, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 6px 0 3px 0; font-size: 11px; font-weight: bold; color: #dc2626; border-top: 2px solid #dc2626;">Remaining Balance</td>
                        <td style="padding: 6px 0 3px 0; font-size: 11px; font-weight: bold; color: #dc2626; text-align: right; border-top: 2px solid #dc2626;">
                            ${{ number_format($payment->bill->outstanding_amount, 2) }}
                        </td>
                    </tr>
                    @if($payment->bill->outstanding_amount <= 0)
                    <tr>
                        <td colspan="2" style="padding: 4px 6px; background: #dcfce7; text-align: center; font-size: 9px; font-weight: bold; color: #166534; text-transform: uppercase; letter-spacing: 1px;">
                            ✓ Bill Fully Paid
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ── FOOTER ── --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px;">
        <tr>
            <td style="font-size: 8px; color: #94a3b8; vertical-align: middle;">
                <span style="font-weight: bold; color: #16a34a; font-size: 9px;">{{ $settings['clinic_name'] ?? 'MedBilling' }}</span><br>
                {{ $settings['invoice_footer'] ?? 'Thank you for your payment.' }}
            </td>
            <td style="font-size: 8px; color: #94a3b8; text-align: right; vertical-align: middle;">
                Receipt {{ $payment->payment_number }}<br>
                Generated {{ now()->format('M d, Y') }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>