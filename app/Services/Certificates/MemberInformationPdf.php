<?php

namespace App\Services\Certificates;

use App\Models\UserMembership;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;

class MemberInformationPdf
{
    public function build(UserMembership $membership): PdfInstance
    {
        return Pdf::loadView('pdf.member-information', ['membership' => $membership])
            ->setPaper('a4', 'portrait');
    }
}
