<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SectionResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SectionResource\RelationManagers;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    Section::make('First Section')->schema([
                        TextInput::make('first_title')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('first_image')
                            ->required()
                            ->image()
                            ->maxSize(2048)
                            ->optimize('webp')
                            ->label('Upload Photo')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif'])
                            ->directory('section-image')
                            ->disk('public')
                            ->image(),
                    ]),
                    Section::make('Second Section')->schema([
                        TextInput::make('second_title')
                            ->maxLength(255),
                        FileUpload::make('second_image')
                            ->image()
                            ->maxSize(2048)
                            ->optimize('webp')
                            ->label('Upload Photo')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif'])
                            ->directory('section-image')
                            ->disk('public')
                            ->image(),
                    ]),
                    Section::make('Jumbo Section')->schema([
                        TextInput::make('jumbo_title')
                            ->maxLength(255),
                        FileUpload::make('jumbo_image')
                            ->image()
                            ->maxSize(2048)
                            ->optimize('webp')
                            ->label('Upload Photo')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif'])
                            ->directory('section-image')
                            ->disk('public'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('second_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumbo_title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('first_image'),
                Tables\Columns\ImageColumn::make('second_image'),
                Tables\Columns\ImageColumn::make('jumbo_image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
