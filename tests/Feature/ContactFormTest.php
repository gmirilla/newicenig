<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('sends a contact message when the form is valid', function () {
    Mail::fake();

    Livewire::test('marketing.contact-form')
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('message', 'Hello, I have a question about membership.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Mail::assertSent(App\Mail\ContactMessageSubmitted::class);
});

it('requires all fields on the contact form', function () {
    Mail::fake();

    Livewire::test('marketing.contact-form')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'message']);

    Mail::assertNothingSent();
});
