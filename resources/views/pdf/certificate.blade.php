<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #14532d;
        }
        .border {
            border: 14px solid #16a34a;
            padding: 50px;
            height: 100%;
            box-sizing: border-box;
            text-align: center;
        }
        .eyebrow {
            letter-spacing: 4px;
            text-transform: uppercase;
            font-size: 12px;
            color: #6b7280;
        }
        .org {
            font-size: 22px;
            font-weight: bold;
            margin-top: 6px;
        }
        h1 {
            font-size: 34px;
            margin: 40px 0 10px;
            color: #111827;
        }
        .name {
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0 6px;
            border-bottom: 2px solid #16a34a;
            display: inline-block;
            padding-bottom: 6px;
        }
        .tier {
            font-size: 16px;
            color: #374151;
            margin-top: 10px;
        }
        .meta {
            margin-top: 50px;
            width: 100%;
            font-size: 12px;
            color: #6b7280;
        }
        .meta td {
            width: 33%;
            text-align: center;
        }
        .meta strong {
            display: block;
            color: #111827;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="border">
        <p class="eyebrow">Certificate of Membership</p>
        <p class="org">Institute of Chartered Economists of Nigeria</p>

        <h1>This certifies that</h1>
        <p class="name">{{ $membership->fullName() }}</p>
        <p class="tier">is a {{ $membership->membershipTier->name }} ({{ $membership->membershipTier->abbreviation }}) of the Institute,
            in good standing, having satisfied all requirements for this designation.</p>

        <table class="meta">
            <tr>
                <td>
                    <strong>{{ $membership->membership_number }}</strong>
                    Membership Number
                </td>
                <td>
                    <strong>{{ $membership->verified_at?->format('F j, Y') }}</strong>
                    Date Issued
                </td>
                <td>
                    <strong>{{ $membership->expires_at?->format('F j, Y') }}</strong>
                    Valid Until
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
