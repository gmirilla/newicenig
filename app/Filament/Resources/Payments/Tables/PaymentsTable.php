<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Actions\Payments\CheckPendingPaystackPayment;
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
                Action::make('recheckPaystack')
                    ->label('Recheck with Paystack')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Asks Paystack for this transaction\'s current status right now, and restarts the automated 5-check retry if it\'s still not confirmed.')
                    ->visible(fn (Payment $record) => $record->status === PaymentStatus::Pending && $record->payment_method === PaymentMethod::Paystack)
                    ->action(function (Payment $record) {
                        $result = app(CheckPendingPaystackPayment::class)->recheckManually($record);

                        match ($result) {
                            'resolved' => Notification::make()->title('Resolved')->body('Payment status: '.$record->fresh()->status->label())->success()->send(),
                            'still_pending' => Notification::make()->title('Still pending')->body('Paystack has not confirmed this yet — the automated retries have restarted.')->warning()->send(),
                            default => Notification::make()->title('Nothing to recheck')->warning()->send(),
                        };
                    }),
                Action::make('resendNotification')
                    ->label('Resend notification')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Re-sends the payment notification email — with the member information PDF attached — to the approved mailing list configured in Site Settings.')
                    ->visible(fn (Payment $record) => $record->status === PaymentStatus::Successful)
                    ->action(function (Payment $record) {
                        try {
                            $sent = app(HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($record);
                        } catch (\DomainException $e) {
                            Notification::make()->title('Nothing to resend')->body($e->getMessage())->danger()->send();

                            return;
                        }

                        $sent
                            ? Notification::make()->title('Notification queued')->body('It will be delivered shortly — check the Email Log if it does not arrive.')->success()->send()
                            : Notification::make()->title('No approved recipients configured')->body('Add recipients under Site Settings first.')->warning()->send();
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
