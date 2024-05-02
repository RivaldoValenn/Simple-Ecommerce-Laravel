<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\CustomerRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;


enum Status: string implements HasLabel, HasColor, HasIcon
{
    case Processing = 'processing';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Processing => 'Processing',
            self::Shipping => 'Shipping',
            self::Completed => 'Completed',
            self::Canceled => 'Canceled',
        };
    }
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Processing => 'warning',
            self::Shipping => 'sky',
            self::Completed => 'success',
            self::Canceled => 'danger',
        };
    }
    public function getIcon(): string
    {
        return match ($this) {
            self::Processing => 'heroicon-o-arrow-path',
            self::Shipping => 'heroicon-o-truck',
            self::Completed => 'heroicon-o-check-circle',
            self::Canceled => 'heroicon-o-x-circle',
        };
    }
}


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Customer Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'cc' => 'Credit Card',
                                'cod' => 'Cash on Delivery',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),

                        Select::make('shipping_method')
                            ->options([
                                'standard' => 'Standard',
                                'express' => 'Express',
                                'overnight' => 'Overnight',
                                'pickup' => 'Pickup',
                            ])
                            ->required(),
                        ToggleButtons::make('status')
                            ->label('Order Status')
                            ->options(Status::class)
                            ->required()
                            ->inline(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Order Items')->schema([
                        Repeater::make('orderItems')
                            ->label('Items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(4)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('unit_amount', Product::find($state)?->price ?? 0);
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('total_amount', Product::find($state)?->price ?? 0);
                                    }),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                        $set('total_amount', $state * $get('unit_amount'));
                                    })

                                    ->required(),
                                TextInput::make('unit_amount')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $set('amount', $state);
                                    })
                                    ->columnSpan(3)
                                    ->required(),
                                TextInput::make('total_amount')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->label('Total')
                                    ->columnSpan(3)
                                    ->dehydrated()
                                    ->required(),

                            ])->columns(12),
                        Placeholder::make('grand_total_placeholder')
                            ->label('Grand Total')
                            ->content(function (Get $get, Set $set) {
                                $total = 0;
                                if (!$repeaters = $get('orderItems')) {
                                    return $total;
                                }
                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get('orderItems.' . $key . '.total_amount');
                                }
                                $set('grand_total', $total);
                                return "Rp. " . number_format($total, 0, ',', '.');
                            }),
                        Hidden::make('grand_total')
                            ->default(0)
                    ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->label('Total')
                    ->currency('IDR')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),
                TextColumn::make('shipping_method')
                    ->label('Shipping Method')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                    })
                    ->sortable(),
                SelectColumn::make('status')
                    ->label('Status')
                    ->options(Status::class)
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CustomerRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
