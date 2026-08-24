<?php

namespace App\Filament\Pages;

use App\Models\PaystackSettingsAuditLog;
use App\Settings\PaystackSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;

class ManagePaystackSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-paystack-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|\UnitEnum|null $navigationGroup = 'Membership';

    protected static ?string $navigationLabel = 'Paystack Settings';

    protected static ?string $title = 'Paystack Settings';

    protected static ?int $navigationSort = 5;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(app(PaystackSettings::class)->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API credentials')
                    ->description('Found in your Paystack dashboard under Settings → API Keys & Webhooks.')
                    ->components([
                        TextInput::make('public_key')
                            ->label('Public key')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('secret_key')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Stored encrypted. Never shown in full again once saved — re-enter it to change it.'),
                        TextInput::make('payment_url')
                            ->label('API base URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText('Only change this if Paystack instructs you to use a different endpoint.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = app(PaystackSettings::class);

        $before = [
            'public_key' => $settings->public_key,
            'secret_key' => $settings->secret_key,
            'payment_url' => $settings->payment_url,
        ];

        $state = $this->form->getState();

        $settings->public_key = $state['public_key'];
        $settings->secret_key = $state['secret_key'];
        $settings->payment_url = $state['payment_url'];
        $settings->save();

        $this->recordAuditLog($before, $state);

        Notification::make()->title('Paystack settings saved')->success()->send();
    }

    /**
     * @param  array<string, string>  $before
     * @param  array<string, string>  $after
     */
    protected function recordAuditLog(array $before, array $after): void
    {
        $changes = [];

        if ($before['public_key'] !== $after['public_key']) {
            $changes['public_key'] = ['from' => $before['public_key'], 'to' => $after['public_key']];
        }

        if ($before['payment_url'] !== $after['payment_url']) {
            $changes['payment_url'] = ['from' => $before['payment_url'], 'to' => $after['payment_url']];
        }

        // The secret key's value is never written to the audit log, only the fact that it changed.
        if ($before['secret_key'] !== $after['secret_key']) {
            $changes['secret_key'] = ['changed' => true];
        }

        if (empty($changes)) {
            return;
        }

        PaystackSettingsAuditLog::create([
            'user_id' => auth()->id(),
            'changes' => $changes,
            'ip_address' => Request::ip(),
        ]);
    }

    public function getAuditLogsProperty(): Collection
    {
        return PaystackSettingsAuditLog::with('user')->latest()->limit(20)->get();
    }
}
