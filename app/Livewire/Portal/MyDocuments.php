<?php

namespace App\Livewire\Portal;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyDocuments extends Component
{
    public function getDocumentsProperty()
    {
        return Auth::user()->documents()->latest()->get();
    }

    public function render()
    {
        return view('livewire.portal.my-documents');
    }
}
