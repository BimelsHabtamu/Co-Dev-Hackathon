<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status')
                ->options([
                    'pending_approval' => 'Pending Approval',
                    'approved'         => 'Approved',
                    'rejected'         => 'Rejected',
                    'completed'        => 'Completed',
                ])->required(),
            Textarea::make('note')->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Sale #')->sortable(),
                TextColumn::make('user.name')->label('Sales Officer')->searchable(),
                TextColumn::make('subtotal')->money('ETB')->sortable(),
                TextColumn::make('discount')->suffix('%'),
                TextColumn::make('vat_amount')->money('ETB')->label('VAT (15%)'),
                TextColumn::make('total')->money('ETB')->sortable()->weight('bold'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending_approval',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'primary' => 'completed',
                    ]),
                TextColumn::make('approvedBy.name')->label('Approved By')->default('-'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending_approval' => 'Pending Approval',
                    'approved'         => 'Approved',
                    'rejected'         => 'Rejected',
                    'completed'        => 'Completed',
                ]),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Sale $record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Sale $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Sale approved successfully.')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Sale $record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Sale $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->items as $item) {
                                $item->product->increment('stock_quantity', $item->quantity);
                            }
                            $record->update([
                                'status'      => 'rejected',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                        });
                        Notification::make()->title('Sale rejected and stock restored.')->danger()->send();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
        ];
    }
}
