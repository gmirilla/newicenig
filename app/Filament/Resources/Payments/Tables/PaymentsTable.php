<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Actions\Payments\HandleSuccessfulPayment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Exports\PaymentExporter;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable(),
                TextColumn::make('user.name')->label('User')->placeholder('—'),
                TextColumn::make('payment_method')
                    ->badge()
                    ->formatStateUsing(fn (PaymentMethod $state) => $state->label()),
                TextColumn::make('bankAccount.bank_name')->label('Bank')->placeholder('—'),
                TextColumn::make('amount')->money('NGN'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                    ->color(fn (PaymentStatus $state) => $state->color()),
                TextColumn::make('paid_at')->dateTime()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ExportAction::make()->exporter(PaymentExporter::class),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('payment_method')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                Action::make('viewProof')
                    ->label('View proof')
                    ->icon(Heroicon::OutlinedPaperClip)
                    ->url(fn (Payment $record) => route('payments.proof', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $record) => $record->hasProofOfPayment()),
                Action::make('verify')
                    ->label('Verify payment')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('This confirms the bank transfer was received and moves the linked application/renewal forward for review.')
                    ->visible(fn (Payment $record) => $record->status === PaymentStatus::PendingVerification)
                    ->action(function (Payment $record) {
                        app(HandleSuccessfulPayment::class)->handle($record, ['channel' => 'bank_transfer']);

                        Notification::make()->title('Payment verified')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(PaymentExporter::class),
                ]),
            ]);
    }
}
