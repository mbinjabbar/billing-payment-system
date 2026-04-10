<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>NF2 Form - {{ $bill->bill_number }}</title>
<style>
    /* 1. Define the physical paper size */
    @page { 
        size: A4; 
        margin: 0.5in; 
    }
    
    body { 
        font-family: 'Helvetica', 'Arial', sans-serif; 
        font-size: 11px; /* Slightly larger for better readability */
        color: #000; 
        line-height: 1.3; 
        margin: 0; 
        padding: 0; 
        text-transform: uppercase; 
    }

    /* 2. Force the container to take up the full height of the page */
    .page-wrapper {
        width: 100%;
        height: 98%; /* Leaves a tiny bit of breathing room at the very bottom */
        border: 2px solid #000;
        position: relative;
    }

    .header-title { 
        text-align: center; 
        border-bottom: 2px solid #000; 
        padding: 15px; 
        font-weight: bold; 
        font-size: 14px; 
    }
    
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    td { border: 1px solid #000; padding: 6px; vertical-align: top; overflow: hidden; }
    
    .label { font-size: 8px; font-weight: bold; display: block; margin-bottom: 3px; color: #000; }
    .value { font-size: 11px; font-weight: normal; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .bg-gray { background-color: #f2f2f2; }
    
    /* 3. Give heights to sections to "stretch" the form down the page */
    .description-box { height: 100px; } /* Description and Injury boxes are usually large */
    .vehicle-section { background-color: #f9f9f9; }

    .checkbox-box { 
        display: inline-block; 
        width: 16px; 
        border: 1px solid #000; 
        height: 16px; 
        text-align: center; 
        margin-right: 5px; 
        font-size: 12px; 
        line-height: 16px; 
    }

    /* 4. Footer to sit at the absolute bottom of the container */
    .form-footer {
        position: absolute;
        bottom: 10px;
        width: 100%;
        text-align: center;
        font-size: 9px;
        font-weight: bold;
    }
</style>
</head>
<body>

@php 
    $patient = $bill->visit->appointment->patientCase->patient;
    $nf2 = $bill->visit->appointment->patientCase->nf2Detail;
@endphp

<div class="page-wrapper">
    <div class="header-title">
        NEW YORK MOTOR VEHICLE NO-FAULT INSURANCE LAW<br>
        APPLICATION FOR MOTOR VEHICLE NO-FAULT BENEFITS
    </div>

    <!-- Insurer Info -->
    <table>
        <tr>
            <td style="width: 50%;">
                <span class="label">NAME AND ADDRESS OF INSURER *</span>
                <div class="value bold">{{ $bill->insurance_firm->name }}</div>
                <div class="value">{{ $bill->insurance_firm->address ?? 'ADDRESS ON FILE' }}</div>
            </td>
            <td style="width: 50%;">
                <span class="label">NAME, ADDRESS, AND PHONE NUMBER OF INSURER'S CLAIMS REPRESENTATIVE *</span>
                <div class="value bold">{{ $bill->insurance_firm->contact_person ?? 'CLAIMS DEPARTMENT' }}</div>
                <div class="value">{{ $bill->insurance_firm->phone ?? 'N/A' }} / {{ $bill->insurance_firm->email ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Reference Row -->
    <table>
        <tr>
            <td><span class="label">DATE</span><div class="value">{{ now()->format('m/d/Y') }}</div></td>
            <td><span class="label">POLICYHOLDER</span><div class="value">{{ $nf2->policyholder_name ?? 'N/A' }}</div></td>
            <td><span class="label">POLICY NUMBER</span><div class="value">{{ $nf2->policy_number ?? 'N/A' }}</div></td>
            <td><span class="label">DATE OF ACCIDENT</span><div class="value">{{ $nf2->accident_date ? \Carbon\Carbon::parse($nf2->accident_date)->format('m/d/Y') : 'N/A' }}</div></td>
            <td><span class="label">CLAIM NUMBER</span><div class="value">{{ $nf2->claim_number ?? 'N/A' }}</div></td>
        </tr>
    </table>

    <div style="padding: 10px; font-size: 9px; border-bottom: 1px solid #000; background: #fafafa; text-align: center;">
        IMPORTANT: TO BE ELIGIBLE FOR BENEFITS YOU MUST COMPLETE AND SIGN THIS APPLICATION. RETURN PROMPTLY WITH COPIES OF ANY BILLS.
    </div>

    <!-- Applicant Section -->
    <table>
        <tr>
            <td>
                <span class="label">NAME AND ADDRESS OF APPLICANT *</span>
                <div class="value bold">{{ $patient->full_name }}</div>
                <div class="value">{{ $patient->address }}, {{ $patient->city }}, {{ $patient->state }} {{ $patient->postal_code }}</div>
            </td>
        </tr>
    </table>

    <!-- Numbered Details -->
    <table>
        <tr>
            <td style="width: 50%"><span class="label">1. YOUR NAME</span><div class="value">{{ $patient->full_name }}</div></td>
            <td style="width: 50%"><span class="label">2. PHONE NOS. (HOME / BUSINESS)</span><div class="value">{{ $patient->phone }} / {{ $patient->mobile ?? 'N/A' }}</div></td>
        </tr>
        <tr>
            <td><span class="label">3. YOUR ADDRESS</span><div class="value">{{ $patient->address }}, {{ $patient->city }}</div></td>
            <td>
                <table style="border: none; margin: 0; padding: 0;">
                    <tr>
                        <td style="border: none; border-right: 1px solid #000; padding: 0 5px 0 0;"><span class="label">4. DATE OF BIRTH</span><div class="value">{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('m/d/Y') }}</div></td>
                        <td style="border: none; padding: 0 0 0 5px;"><span class="label">5. SOCIAL SECURITY NO.</span><div class="value">{{ $nf2->patient_ssn ?? '###-##-####' }}</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Expanded Accident Info (Stretches the page) -->
    <table>
        <tr>
            <td style="width: 40%"><span class="label">6. DATE AND TIME OF ACCIDENT</span><div class="value">{{ $nf2->accident_date }} AT {{ $nf2->accident_time ?? 'N/A' }}</div></td>
            <td><span class="label">7. PLACE OF ACCIDENT</span><div class="value">{{ $nf2->accident_location ?? 'N/A' }}</div></td>
        </tr>
        <tr>
            <td colspan="2" class="description-box">
                <span class="label">8. BRIEF DESCRIPTION OF ACCIDENT</span>
                <div class="value">{{ $nf2->accident_description ?? 'N/A' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="description-box">
                <span class="label">9. DESCRIBE YOUR INJURY</span>
                <div class="value">{{ $nf2->injury_description ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Vehicle Details -->
    <table class="vehicle-section">
        <tr><td colspan="3" class="bg-gray bold center" style="font-size: 10px;">10. IDENTITY OF VEHICLE YOU OCCUPIED AT THE TIME OF ACCIDENT</td></tr>
        <tr>
            <td><span class="label">OWNER'S NAME</span><div class="value">{{ $nf2->vehicle_owner_name ?? 'N/A' }}</div></td>
            <td><span class="label">MAKE</span><div class="value">{{ $nf2->vehicle_make ?? 'N/A' }}</div></td>
            <td><span class="label">YEAR</span><div class="value">{{ $nf2->vehicle_year ?? 'N/A' }}</div></td>
        </tr>
        <tr>
            <td colspan="3" style="padding: 10px;">
                <span class="label" style="display: inline;">THIS VEHICLE WAS:</span>
                &nbsp;&nbsp; <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Bus' ? 'X' : '' }}</span> BUS
                &nbsp;&nbsp; <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Truck' ? 'X' : '' }}</span> TRUCK
                &nbsp;&nbsp; <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Automobile' ? 'X' : '' }}</span> AUTOMOBILE
                &nbsp;&nbsp; <span class="checkbox-box">{{ ($nf2->vehicle_type ?? '') == 'Motorcycle' ? 'X' : '' }}</span> MOTORCYCLE
            </td>
        </tr>
    </table>

    <!-- Questionnaire -->
    <table>
        <tr>
            <td style="width: 60%"><span class="label">11. WERE YOU THE DRIVER OF THE MOTOR VEHICLE?</span></td>
            <td class="center"><span class="label">YES</span><div class="bold">{{ ($nf2->is_driver ?? false) ? 'X' : '' }}</div></td>
            <td class="center"><span class="label">NO</span><div class="bold">{{ !($nf2->is_driver ?? false) ? 'X' : '' }}</div></td>
        </tr>
    </table>

</div>

</body>
</html>