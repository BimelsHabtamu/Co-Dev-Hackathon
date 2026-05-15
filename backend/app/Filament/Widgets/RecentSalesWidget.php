<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSalesWidget extends BaseWidget
{
    protected static ?string $heading   = 'Recent Sales';
    protected static ?int    $sort      = 5;

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::with(['user:id,name', 'items'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Sale #')
                    ->formatStateUsing(fn ($state) => '#' . $state)
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Sales Officer')
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->getStateUsing(fn (Sale $record) => $record->items->count()),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('ETB')
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Disc.')
                    ->suffix('%'),

                TextColumn::make('vat_amount')
                    ->label('VAT (15%)')
                    ->money('ETB'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('ETB')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_approval',
                        'success' => 'completed',
                        'primary' => 'approved',
                        'danger'  => 'rejected',
                    ]),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
