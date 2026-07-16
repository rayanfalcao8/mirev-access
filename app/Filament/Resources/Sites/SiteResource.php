<?php

namespace App\Filament\Resources\Sites;

use App\Filament\Resources\Sites\Pages\CreateSite;
use App\Filament\Resources\Sites\Pages\EditSite;
use App\Filament\Resources\Sites\Pages\ListSites;
use App\Models\Site;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;
    protected static ?string $modelLabel = 'site';
    protected static ?string $pluralModelLabel = 'sites';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nom')->required()->maxLength(255),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('currency')->label('Devise')->default('XAF')->required()->length(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Site')->searchable()->sortable(),
                TextColumn::make('slug')->label('Portail')->searchable(),
                TextColumn::make('plans_count')->label('Forfaits')->counts('plans'),
                TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSites::route('/'),
            'create' => CreateSite::route('/create'),
            'edit' => EditSite::route('/{record}/edit'),
        ];
    }
}
