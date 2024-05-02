<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use GuzzleHttp\Client;

class CustomerRelationManager extends RelationManager
{
    protected static string $relationship = 'customer';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('state'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('street_address'),
                Tables\Columns\TextColumn::make('zip_code'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime()
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}