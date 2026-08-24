<?php

namespace App\Services\Certificates;

use App\Models\UserMembership;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;

class MembershipCertificatePdf
{
    public function build(UserMembership $membership): PdfInstance
    {
        return Pdf::loadView('pdf.certificate', ['membership' => $membership])
            ->setPaper('a4', 'landscape');
    }
}
