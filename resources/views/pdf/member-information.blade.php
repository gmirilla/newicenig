<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 30px;
        }
        .header {
            border-bottom: 3px solid #16a34a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header .org {
            font-size: 18px;
            font-weight: bold;
            color: #14532d;
        }
        .header .subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        h2 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #15803d;
            border-bottom: 1px solid #dcfce7;
            padding-bottom: 4px;
            margin: 18px 0 8px;
        }
        table.fields {
            width: 100%;
            border-collapse: collapse;
        }
        table.fields td {
            padding: 4px 6px;
            vertical-align: top;
            width: 25%;
        }
        table.fields td.label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.fields td.value {
            font-weight: bold;
            padding-bottom: 8px;
        }
        .full-width {
            width: 100% !important;
        }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="org">Institute of Chartered Economists of Nigeria</div>
        <div class="subtitle">Member Information Sheet — {{ $membership->fullName() }}</div>
    </div>

    <h2>Membership</h2>
    <table class="fields">
        <tr>
            <td class="label">Tier</td>
            <td class="label">Status</td>
            <td class="label">Membership number</td>
            <td class="label">Applied</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->membershipTier->name }}</td>
            <td class="value">{{ $membership->status->label() }}</td>
            <td class="value">{{ $membership->membership_number ?? '—' }}</td>
            <td class="value">{{ $membership->created_at->format('M j, Y') }}</td>
        </tr>
    </table>

    <h2>Personal details</h2>
    <table class="fields">
        <tr>
            <td class="label">Full name</td>
            <td class="label">Gender</td>
            <td class="label">Date of birth</td>
            <td class="label">Nationality</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->fullName() }}</td>
            <td class="value">{{ ucfirst($membership->gender ?? '—') }}</td>
            <td class="value">{{ $membership->date_of_birth?->format('M j, Y') ?? '—' }}</td>
            <td class="value">{{ $membership->nationality ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Place of birth</td>
            <td class="label">Marital status</td>
            <td class="label" colspan="2">State of origin / LGA</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->place_of_birth ?? '—' }}</td>
            <td class="value">{{ ucfirst($membership->marital_status ?? '—') }}</td>
            <td class="value" colspan="2">{{ $membership->state_of_origin ?? '—' }} / {{ $membership->local_government_area ?? '—' }}</td>
        </tr>
    </table>

    <h2>Contact & address</h2>
    <table class="fields">
        <tr>
            <td class="label">Email</td>
            <td class="label">Phone</td>
            <td class="label" colspan="2">Next of kin</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->email }}</td>
            <td class="value">{{ $membership->phone }}</td>
            <td class="value" colspan="2">{{ $membership->next_of_kin_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label full-width" colspan="4">Residential address</td>
        </tr>
        <tr>
            <td class="value full-width" colspan="4">{{ $membership->residential_address ?? '—' }}</td>
        </tr>
        @if ($membership->postal_address)
            <tr>
                <td class="label full-width" colspan="4">Postal address</td>
            </tr>
            <tr>
                <td class="value full-width" colspan="4">{{ $membership->postal_address }}</td>
            </tr>
        @endif
    </table>

    <h2>Educational qualifications</h2>
    <table class="fields">
        <tr>
            <td class="label">Primary school</td>
            <td class="label">Year</td>
            <td class="label">Secondary school</td>
            <td class="label">Year</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->primary_school ?? '—' }}</td>
            <td class="value">{{ $membership->primary_school_year ?? '—' }}</td>
            <td class="value">{{ $membership->secondary_school ?? '—' }}</td>
            <td class="value">{{ $membership->secondary_school_year ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Higher institution</td>
            <td class="label">Course</td>
            <td class="label">Year</td>
            <td class="label">Grade</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->higher_institution ?? '—' }}</td>
            <td class="value">{{ $membership->higher_institution_course ?? '—' }}</td>
            <td class="value">{{ $membership->higher_institution_year ?? '—' }}</td>
            <td class="value">{{ $membership->higher_institution_grade ?? '—' }}</td>
        </tr>
    </table>

    <h2>Professional background</h2>
    <table class="fields">
        <tr>
            <td class="label">Employer</td>
            <td class="label">Job title</td>
            <td class="label">Years of experience</td>
            <td class="label">Year of qualification</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->employer_name ?? '—' }}</td>
            <td class="value">{{ $membership->job_title ?? '—' }}</td>
            <td class="value">{{ $membership->years_of_experience ?? '—' }}</td>
            <td class="value">{{ $membership->year_of_qualification ?? '—' }}</td>
        </tr>
        @if ($membership->belongs_to_other_institute)
            <tr>
                <td class="label">Other institute</td>
                <td class="label">Status</td>
                <td class="label" colspan="2">Membership number</td>
            </tr>
            <tr>
                <td class="value">{{ $membership->other_institute_name }}</td>
                <td class="value">{{ $membership->other_institute_status }}</td>
                <td class="value" colspan="2">{{ $membership->other_institute_membership_number ?? '—' }}</td>
            </tr>
        @endif
    </table>

    <h2>Declaration</h2>
    <table class="fields">
        <tr>
            <td class="label">Signed by</td>
            <td class="label" colspan="3">Accepted at</td>
        </tr>
        <tr>
            <td class="value">{{ $membership->declaration_name ?? '—' }}</td>
            <td class="value" colspan="3">{{ $membership->declaration_accepted_at?->format('M j, Y g:ia') ?? '—' }}</td>
        </tr>
    </table>

    <div class="footer">
        Generated automatically on {{ now()->format('M j, Y g:ia') }} — for internal use only.
    </div>
</body>
</html>
