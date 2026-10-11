<?php

namespace App\Filament\Shared\Actions;

use Closure;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Delete through a Service. A ValidationException from the service (e.g. "role still in use")
 * becomes a danger notification instead of an error page, and the record stays.
 */
class ServiceDeleteAction
{
    /**
     * @param  Closure(Model): void  $delete  e.g. fn (User $record) => app(UserService::class)->delete($record)
     */
    public static function make(Closure $delete): DeleteAction
    {
        return DeleteAction::make()
            ->using(function (Model $record) use ($delete): bool {
                try {
                    $delete($record);
                } catch (ValidationException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();

                    return false;
                }

                return true;
            });
    }
}
