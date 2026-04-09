<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NF2 Form - {{ $bill->bill_number }}</title>
<style>
    @page { margin: 20px; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #000; line-height: 1.2; margin: 0; padding: 0; text-transform: uppercase; }
    .container { width: 100%; border: 2px solid #000; padding: 1px; }
    .header-title { text-align: center; border-bottom: 2px solid #000; padding: 10px; font-weight: bold; font-size: 13px; }
    
    table { width: 100%; border-collapse: collapse; }
    td { border: 1px solid #000; padding: 4px; vertical-align: top; }
    
    .label { font-size: 8px; font-weight: bold; display: block; margin-bottom: 2px; color: #333; }
    .value { font-size: 10px; font-weight: normal; min-height: 12px; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .bg-gray { background-color: #f2f2f2; }
    
    .half { width: 50%; }
    .fifth { width: 20%; }
    .checkbox-box { display: inline-block; width: 15px; border: 1px solid #000; height: 12px; text-align: center; margin-right: 2px; font-size: 9px; line-height: 12px; vertical-align: middle; }
</style>
</head>
<body>

@php 
    $patient = $bill->visit->appointment->patientCase->patient;
    $nf2 = $bill->visit->appointment->patientCase->nf2Detail;
@endphp

<div class="container">
    <div class="header-title">
        NEW YORK MOTOR VEHICLE NO-FAULT INSURANCE LAW<br>
        APPLICATION FOR MOTOR VEHICLE NO-FAULT BENEFITS
    </div>

    <!-- Top Row: Insurer Info -->
    <table>
        <tr>
            <td class="half">
                <span class="label">NAME AND ADDRESS OF INSURER *</span>
                <div class="value bold">{{ $bill->insurance_firm->name }}</div>
                <div class="value">{{ $bill->insurance_firm->address ?? 'ADDRESS ON FILE' }}</div>
            </td>
            <td class="half">
                <span class="label">NAME, ADDRESS, AND PHONE NUMBER OF INSURER'S CLAIMS REPRESENTATIVE *</span>
                <div class="value bold">{{ $bill->insurance_firm->contact_person ?? 'CLAIMS DEPT' }}</div>
                <div class="value">{{ $bill->insurance_firm->phone ?? 'N/A' }} / {{ $bill->insurance_firm->email ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Reference Row -->
    <table>
        <tr>
            <td class="fifth">
                <span class="label">DATE</span>
                <div class="value">{{ now()->format('m/d/Y') }}</div>
            </td>
            <td class="fifth">
                <span class="label">POLICYHOLDER</span>
                <div class="value">{{ $nf2->policyholder_name ?? 'N/A' }}</div>
            </td>
            <td class="fifth">
                <span class="label">POLICY NUMBER</span>
                <div class="value">{{ $nf2->policy_number ?? 'N/A' }}</div>
            </td>
            <td class="fifth">
                <span class="label">DATE OF ACCIDENT</span>
                <div class="value">{{ $nf2->accident_date ? \Carbon\Carbon::parse($nf2->accident_date)->format('m/d/Y') : 'N/A' }}</div>
            </td>
            <td class="fifth">
                <span class="label">CLAIM NUMBER</span>
                <div class="value">{{ $nf2->claim_number ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <div style="padding: 5px; font-size: 8px; border-bottom: 1px solid #000; background: #fafafa;">
        TO ENABLE US TO DETERMINE IF YOU ARE ENTITLED TO BENEFITS UNDER THE NEW YORK NO-FAULT LAW, PLEASE COMPLETE THIS FORM AND RETURN IT PROMPTLY.
    </div>

    <!-- Section: Applicant -->
    <table>
        <tr>
            <td>
                <span class="label">NAME AND ADDRESS OF APPLICANT *</span>
                <div class="value bold">{{ $patient->full_name }}</div>
                <div class="value">{{ $patient->address }}, {{ $patient->city }}, {{ $patient->state }} {{ $patient->postal_code }}</div>
            </td>
        </tr>
    </table>

    <!-- Numbered Rows 1-5 -->
    <table>
        <tr>
            <td style="width: 40%">
                <span class="label">1. YOUR NAME</span>
                <div class="value">{{ $patient->full_name }}</div>
            </td>
            <td colspan="2">
                <span class="label">2. PHONE NOS. (HOME / BUSINESS)</span>
                <div class="value">{{ $patient->phone }} / {{ $patient->mobile ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">3. YOUR ADDRESS (NO., STREET, CITY OR TOWN AND ZIP CODE)</span>
                <div class="value">{{ $patient->address }}, {{ $patient->city }}, {{ $patient->state }}</div>
            </td>
            <td style="width: 30%">
                <span class="label">4. DATE OF BIRTH</span>
                <div class="value">{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('m/d/Y') }}</div>
            </td>
            <td style="width: 30%">
                <span class="label">5. SOCIAL SECURITY NO.</span>
                <div class="value">{{ $nf2->patient_ssn ?? '###-##-####' }}</div>
            </td>
        </tr>
    </table>

    <!-- Accident details 6-9 -->
    <table>
        <tr>
            <td style="width: 40%">
                <span class="label">6. DATE AND TIME OF ACCIDENT</span>
                <div class="value">{{ $nf2->accident_date }} AT {{ $nf2->accident_time ?? 'N/A' }}</div>
            </td>
            <td>
                <span class="label">7. PLACE OF ACCIDENT (STREET, CITY OR TOWN AND STATE)</span>
                <div class="value">{{ $nf2->accident_location ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">8. BRIEF DESCRIPTION OF ACCIDENT</span>
                <div class="value" style="height: 25px;">{{ $nf2->accident_description ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">9. DESCRIBE YOUR INJURY</span>
                <div class="value" style="height: 25px;">{{ $nf2->injury_description ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Vehicle details 10 -->
    <table>
        <tr>
            <td colspan="3" class="bg-gray bold center" style="font-size: 9px; padding: 2px;">10. IDENTITY OF VEHICLE YOU OCCUPIED OR OPERATED AT THE TIME OF ACCIDENT</td>
        </tr>
        <tr>
            <td><span class="label">OWNER'S NAME</span> <div class="value">{{ $nf2->vehicle_owner_name ?? 'SAME AS APPLICANT' }}</div></td>
            <td><span class="label">MAKE</span> <div class="value">{{ $nf2->vehicle_make ?? 'N/A' }}</div></td>
            <td><span class="label">YEAR</span> <div class="value">{{ $nf2->vehicle_year ?? 'N/A' }}</div></td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="label">THIS VEHICLE WAS:</span>
                <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Bus' ? 'X' : '' }}</span> BUS
                <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Truck' ? 'X' : '' }}</span> TRUCK
                <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Automobile' ? 'X' : '' }}</span> AUTOMOBILE
            </td>
        </tr>
    </table>

    <!-- Questionnaire 11 -->
    <table style="border-bottom: 2px solid #000;">
        <tr>
            <td style="width: 70%"><span class="label">11. WERE YOU THE DRIVER OF THE MOTOR VEHICLE?</span></td>
            <td class="center"><span class="label">YES</span> <span class="bold">{{ ($nf2->is_driver ?? false) ? 'X' : '' }}</span></td>
            <td class="center"><span class="label">NO</span> <span class="bold">{{ !($nf2->is_driver ?? false) ? 'X' : '' }}</span></td>
        </tr>
    </table>
</div>

<!-- BILLING ATTACHMENT -->
<div style="margin-top: 15px; border: 1px dashed #000; padding: 10px;">
    <h3 class="center" style="font-size: 11px;">ATTACHMENT: MEDICAL BILLING SUMMARY</h3>
    <table style="margin-top: 8px;">
        <tr class="bg-gray">
            <th class="center">PROCEDURE CODE</th>
            <th class="center">BASE CHARGES</th>
            <th class="center">NET BILL AMOUNT</th>
        </tr>
        @if(is_array($bill->procedure_codes))
            @foreach($bill->procedure_codes as $proc)
            <tr>
                <!-- 💡 SAFETY CHECK: Handle if $proc is a string OR an array -->
                <td class="center">{{ is_array($proc) ? ($proc['code'] ?? 'N/A') : $proc }}</td>
                <td class="center">${{ number_format($bill->charges, 2) }}</td>
                <td class="center">${{ number_format($bill->bill_amount, 2) }}</td>
            </tr>
            @endforeach
        @endif
    </table>

    <div style="margin-top: 10px; text-align: right;">
        <p class="bold" style="font-size: 11px;">OUTSTANDING BALANCE: ${{ number_format($bill->outstanding_amount, 2) }}</p>
    </div>
</div>

</body>
</html>