<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageGeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-general-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin']) ?? false;
    }

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(app(GeneralSettings::class)->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization details')
                    ->columns(2)
                    ->components([
                        TextInput::make('org_name')->label('Organization name')->required()->columnSpanFull(),
                        TextInput::make('org_phone')->label('Phone')->required(),
                        TextInput::make('org_email')->label('Email')->email()->required(),
                        TextInput::make('org_address')->label('Address')->required()->columnSpanFull(),
                    ]),
                Section::make('Social links')
                    ->columns(3)
                    ->components([
                        TextInput::make('facebook_url')->label('Facebook')->url(),
                        TextInput::make('twitter_url')->label('X / Twitter')->url(),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url(),
                    ]),
                Section::make('Payment notifications')
                    ->description('Whenever a membership payment or renewal succeeds, an email with a PDF of the member\'s information is sent to this approved list.')
                    ->components([
                        TagsInput::make('payment_notification_recipients')
                            ->label('Approved mailing list')
                            ->placeholder('Add an email and press Enter')
                            ->splitKeys([',', ' ', 'Tab'])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->fill($this->form->getState());
        $settings->save();

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
