<?php

use App\Mail\ContactMessageSubmitted;
use App\Settings\GeneralSettings;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;

new class extends Component
{
    use WithRateLimiting;

    public string $name = '';

    public string $email = '';

    public string $message = '';

    public bool $sent = false;

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->rateLimit(3, decaySeconds: 300);
        } catch (TooManyRequestsException $exception) {
            $this->addError('message', 'Too many messages sent. Please try again in a few minutes.');

            return;
        }

        Mail::to(app(GeneralSettings::class)->org_email)
            ->send(new ContactMessageSubmitted($this->name, $this->email, $this->message));

        $this->reset(['name', 'email', 'message']);
        $this->sent = true;
    }
}; ?>

<div>
    @if ($sent)
        <x-alert variant="success">
            Thanks — your message has been sent. We'll get back to you soon.
        </x-alert>
    @else
        <form wire:submit="submit" class="space-y-5">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="message" value="Message" />
                <textarea
                    id="message"
                    wire:model="message"
                    rows="5"
                    required
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                ></textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <x-button type="submit">Send message</x-button>
        </form>
    @endif
</div>
