<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Models\Plan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static ?string $modelLabel = 'forfait';
    protected static ?string $pluralModelLabel = 'forfaits';
    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->label('Site')->relationship('site', 'name')->required()->searchable()->preload(),
            TextInput::make('name')->label('Nom')->required()->maxLength(255),
            TextInput::make('price_minor')->label('Prix (XAF)')->numeric()->minValue(0)->required(),
            TextInput::make('validity_minutes')->label('Durée (minutes)')->numeric()->minValue(1)->required(),
            TextInput::make('download_limit_kbps')->label('Débit descendant (kbps)')->numeric()->minValue(1),
            TextInput::make('upload_limit_kbps')->label('Débit montant (kbps)')->numeric()->minValue(1),
            Toggle::make('is_active')->label('Disponible à la vente')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Forfait')->searchable()->sortable(),
                TextColumn::make('site.name')->label('Site')->sortable(),
                TextColumn::make('price_minor')->label('Prix')->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', ' ').' XAF')->sortable(),
                TextColumn::make('formatted_duration')->label('Durée'),
                IconColumn::make('is_active')->label('Actif')->boolean(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}
