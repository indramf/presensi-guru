<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Concerns\ConfirmsDeactivation;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\ScheduleConflictChecker;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSchedule extends EditRecord
{
    use ConfirmsDeactivation;

    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['type'] === 'activity') {
            $data['teacher_id'] = null;
            $data['subject_id'] = null;
        } else {
            $data['activity_name'] = null;
        }

        $conflicts = app(ScheduleConflictChecker::class)->check(
            teacherId: isset($data['teacher_id']) ? (int) $data['teacher_id'] : null,
            classId: (int) $data['class_id'],
            dayOfWeek: (int) $data['day_of_week'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            ignoreScheduleId: (int) $this->record->id,
        );

        if ($conflicts !== []) {
            throw ValidationException::withMessages([
                'data.start_time' => implode(' ', $conflicts),
            ]);
        }

        return $data;
    }
}
