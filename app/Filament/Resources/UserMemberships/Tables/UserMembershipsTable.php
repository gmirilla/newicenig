<?php

namespace App\Filament\Resources\UserMemberships\Tables;

use App\Enums\MembershipStatus;
use App\Filament\Exports\UserMembershipExporter;
use App\Models\UserMembership;
use App\Notifications\MembershipApproved;
use App\Notifications\MembershipReinstated;
use App\Notifications\MembershipRevoked;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserMembershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('membership_number')->placeholder('—')->searchable(),
                TextColumn::make('first_name')->formatStateUsing(fn ($record) => $record->fullName())->label('Applicant')->searchable(['first_name', 'last_name']),
                TextColumn::make('email')->searchable(),
                TextColumn::make('membershipTier.name')->label('Tier'),
                TextColumn::make('previousMembership.membershipTier.name')->label('Upgrading from')->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MembershipStatus $state) => $state->label())
                    ->color(fn (MembershipStatus $state) => $state->color()),
                TextColumn::make('expires_at')->dateTime()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ExportAction::make()->exporter(UserMembershipExporter::class),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(MembershipStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (UserMembership $record) => $record->status === MembershipStatus::PendingReview)
                    ->requiresConfirmation()
                    ->action(function (UserMembership $record) {
                        $record->update([
                            'status' => MembershipStatus::Active,
                            'membership_number' => $record->membership_number ?: UserMembership::generateMembershipNumber(),
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                            'expires_at' => now()->addYear(),
                        ]);

                        if ($record->previousMembership) {
                            $record->previousMembership->update(['status' => MembershipStatus::Superseded]);
                        }

                        if ($record->user) {
                            $record->user->notify(new MembershipApproved($record));
                        }

                        Notification::make()->title('Membership approved')->success()->send();
                    }),
                Action::make('revoke')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->visible(fn (UserMembership $record) => $record->status !== MembershipStatus::Revoked)
                    ->requiresConfirmation()
                    ->modalDescription('This immediately blocks the member from the notice board and document library, and marks their membership Revoked.')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for revocation')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (UserMembership $record, array $data) {
                        $record->update([
                            'status' => MembershipStatus::Revoked,
                            'revoked_at' => now(),
                            'revoked_by' => auth()->id(),
                            'revoke_reason' => $data['reason'],
                        ]);

                        if ($record->user) {
                            $record->user->notify(new MembershipRevoked($record));
                        }

                        Notification::make()->title('Membership revoked')->success()->send();
                    }),
                Action::make('reinstate')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('success')
                    ->visible(fn (UserMembership $record) => $record->status === MembershipStatus::Revoked)
                    ->requiresConfirmation()
                    ->modalDescription('Restores this membership to Active (or Expired, if its term has already lapsed) and gives the member back notice board/document access.')
                    ->action(function (UserMembership $record) {
                        $record->update([
                            'status' => $record->expires_at && $record->expires_at->isPast()
                                ? MembershipStatus::Expired
                                : MembershipStatus::Active,
                        ]);

                        if ($record->user) {
                            $record->user->notify(new MembershipReinstated($record));
                        }

                        Notification::make()->title('Membership reinstated')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(UserMembershipExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
