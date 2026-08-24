<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Certificates\MembershipCertificatePdf;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function show(Request $request, MembershipCertificatePdf $certificate)
    {
        $membership = $request->user()->currentMembership();

        abort_unless($membership?->isActive(), 403, 'An active membership is required to download a certificate.');

        $filename = 'icen-certificate-'.str_replace('/', '-', $membership->membership_number).'.pdf';

        return $certificate->build($membership)->stream($filename);
    }
}
