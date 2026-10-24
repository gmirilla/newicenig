<?php

namespace App\View\Components;

use App\Services\Branding\BrandingPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BrandingStyle extends Component
{
    public function render(): View
    {
        return view('components.branding-style', [
            'cssVariables' => app(BrandingPresenter::class)->cssVariables(),
        ]);
    }
}
