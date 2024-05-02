<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\CustomerRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Customer Management';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->label('Customer Name')
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(255),
                    Select::make('state')
                        ->label('State')
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->preload()
                        ->afterStateUpdated(fn (callable $set) => $set('city', null))
                        ->options(function () {
                            $client = new Client();
                            $response = $client->get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
                            $provinces = json_decode($response->getBody(), true);

                            $options = [];
                            foreach ($provinces as $province) {
                                $options[$province['id']] = $province['name'];
                            }

                            return $options;
                        })
                        ->reactive(),
                    Select::make('city')
                        ->label('City')
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->options(function (callable $get) {
                            $state = $get('state');

                            if (!$state) {
                                return [];
                            }

                            $client = new Client();
                            $response = $client->get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$state}.json");
                            $cities = json_decode($response->getBody(), true);

                            $options = [];
                            foreach ($cities as $city) {
                                $options[$city['id']] = $city['name'];
                            }

                            return $options;
                        }),
                    TextInput::make('street_address')
                        ->label('Street Address')
                        ->maxLength(255),
                    TextInput::make('zip_code')
                        ->label('Postal Code')
                        ->maxLength(255),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('street_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            OrdersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
