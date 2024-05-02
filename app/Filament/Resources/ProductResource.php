<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';


    protected static ?string $navigationGroup = 'Cataloque';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Str::slug($state));
                            })
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')
                            ->required()
                            ->disableToolbarButtons([
                                'attachFiles',
                                'table'
                            ])
                            ->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Product Images')->schema([
                        FileUpload::make('images')
                            ->required()
                            ->multiple()
                            ->maxFiles(5)
                            ->reorderable()
                            ->image()
                            ->maxSize(2048)
                            ->optimize('webp')
                            ->label('Upload Photo')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/gif'])
                            ->directory('product-images')
                            ->disk('public'),
                    ])
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Price & Stock')->schema([
                        TextInput::make('price')
                            ->required()
                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 2)
                            ->prefix('Rp')
                            ->numeric(),
                        TextInput::make('stock')
                            ->required()
                            ->maxLength(255),
                    ]),
                    Section::make('Associations')->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Category'),
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Brand'),
                    ]),
                    Section::make('Status')->schema([
                        Toggle::make('is_active')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Active')
                            ->inline(false)
                            ->required(),
                        Toggle::make('in_stock')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('In Stock')
                            ->inline(false)
                            ->required(),
                        Toggle::make('is_featured')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Featured')
                            ->inline(false)
                            ->required(),
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(10)
                    ->searchable(),
                TextColumn::make('price')
                    ->currency('IDR')
                    ->sortable(),
                ImageColumn::make('images')
                    ->limit(1)
                    ->searchable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                IconColumn::make('in_stock')
                    ->label('In Stock')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->color('secondary'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
