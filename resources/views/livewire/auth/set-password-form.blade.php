<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;

new class extends Component
{
    public User $user;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function submit(): void
    {
        $this->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $this->user->forceFill([
            'password' => Hash::make($this->password),
            'email_verified_at' => $this->user->email_verified_at ?? now(),
        ])->save();

        Auth::login($this->user);

        $this->redirect(route('member.dashboard'), navigate: true);
    }
}; ?>

<form wire:submit="submit" class="mt-6 space-y-6">
    <div>
        <x-input-label for="password" value="New password" />
        <x-text-input id="password" type="password" class="mt-1 block w-full" wire:model="password" required autofocus />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirm password" />
        <x-text-input id="password_confirmation" type="password" class="mt-1 block w-full" wire:model="password_confirmation" required />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <x-button type="submit" class="w-full justify-center">Set password &amp; continue</x-button>
</form>
