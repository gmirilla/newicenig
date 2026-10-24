<?php

namespace App\Filament\Pages;

use App\Settings\BrandingSettings;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Lets a deployment re-skin for a different client without touching code —
 * logo, favicon, the three brand colors (each one generates a full 50–950
 * scale, see App\Services\Branding\ColorRamp), and the handful of homepage
 * text fields most worth swapping per org.
 */
class ManageBrandingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-general-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Branding';

    protected static ?string $title = 'Branding';

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
        $this->form->fill(app(BrandingSettings::class)->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->components([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->helperText('Shown in the navigation bar and footer. Leave empty to use the shipped default.'),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->helperText('Shown in the browser tab. Leave empty to reuse the logo.'),
                        TextInput::make('brand_short_name')
                            ->label('Short name')
                            ->required()
                            ->helperText('The short wordmark next to the logo, e.g. "ICEN".')
                            ->columnSpanFull(),
                    ]),
                Section::make('Colors')
                    ->description('Each color generates a full light-to-dark scale used throughout the site — pick the base tone, not a specific shade.')
                    ->columns(3)
                    ->components([
                        ColorPicker::make('color_brand')->label('Brand')->required(),
                        ColorPicker::make('color_accent')->label('Accent')->required(),
                        ColorPicker::make('color_surface')->label('Dark surface (footer)')->required(),
                    ]),
                Section::make('Homepage')
                    ->description('The hero section at the top of the homepage.')
                    ->components([
                        TextInput::make('hero_eyebrow')->label('Eyebrow (small label above the headline)')->columnSpanFull(),
                        TextInput::make('hero_headline')->label('Headline')->required()->columnSpanFull(),
                        Textarea::make('hero_subheadline')->label('Subheading')->required()->rows(3)->columnSpanFull(),
                        TextInput::make('hero_cta_label')->label('Call-to-action button text')->required(),
                    ]),
                Section::make('Membership page')
                    ->components([
                        Textarea::make('membership_intro')
                            ->label('Intro paragraph')
                            ->helperText('Shown under "Join the Institute" on the Membership page.')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Footer & SEO')
                    ->components([
                        Textarea::make('footer_tagline')->label('Footer tagline')->required()->rows(2)->columnSpanFull(),
                        TextInput::make('copyright_text')
                            ->label('Copyright line')
                            ->helperText('Shown in the footer after "© '.date('Y').' " — the year is always current, so don\'t include it here.')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('site_tagline')
                            ->label('Site meta description')
                            ->helperText('Used for search engines and link previews. Leave empty to omit.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = app(BrandingSettings::class);
        $settings->fill($this->form->getState());
        $settings->save();

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
