<?php

namespace App\Filament\Resources\Experiences\Tables;

use App\Enums\PublicationStatus;
use App\Filament\Support\PublishingActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExperiencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('role_title')->label('Role')->searchable()->sortable()->wrap(),
                TextColumn::make('organisation')->searchable()->wrap(),
                TextColumn::make('start_date')->date('M Y')->sortable(),
                TextColumn::make('end_date')->date('M Y')->placeholder('Present'),
                TextColumn::make('publication_status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('publication_status')->label('Status')->options(PublicationStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    PublishingActions::publish(),
                    PublishingActions::unpublish(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
