<?php

namespace App\Filament\Support;

use App\Enums\PublicationStatus;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** Bulk publish and unpublish for any content table with a publication_status column. */
class PublishingActions
{
    public static function publish(): BulkAction
    {
        return BulkAction::make('publish')
            ->label('Publish selected')
            ->icon('heroicon-o-eye')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Selected items go live on the next site rebuild. Items with a publishing hold are skipped.')
            ->action(function (Collection $records): void {
                static::notify(static::setStatus($records, PublicationStatus::Published), 'published');
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function unpublish(): BulkAction
    {
        return BulkAction::make('unpublish')
            ->label('Move to draft')
            ->icon('heroicon-o-eye-slash')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (Collection $records): void {
                static::notify(static::setStatus($records, PublicationStatus::Draft), 'moved to draft');
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Change the status of each record. A record whose model refuses the change
     * (for example a project with a publishing hold) is skipped and reported.
     *
     * @param  iterable<Model>  $records
     * @return array{changed: int, blocked: list<string>}
     */
    public static function setStatus(iterable $records, PublicationStatus $status): array
    {
        $changed = 0;
        $blocked = [];

        foreach ($records as $record) {
            try {
                $record->update(['publication_status' => $status]);
                $changed++;
            } catch (ValidationException) {
                $blocked[] = static::label($record);
            }
        }

        return ['changed' => $changed, 'blocked' => $blocked];
    }

    /** @param array{changed: int, blocked: list<string>} $result */
    private static function notify(array $result, string $verb): void
    {
        $notification = Notification::make()->title($result['changed'].' '.$verb);

        if ($result['blocked'] !== []) {
            $notification->warning()->body('Skipped (publishing hold): '.implode(', ', $result['blocked']));
        } else {
            $notification->success();
        }

        $notification->send();
    }

    private static function label(Model $record): string
    {
        return (string) ($record->getAttribute('title')
            ?? $record->getAttribute('role_title')
            ?? $record->getAttribute('qualification')
            ?? $record->getKey());
    }
}
