<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\ScheduleConflictChecker;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
        );

        if ($conflicts !== []) {
            throw ValidationException::withMessages([
                'data.start_time' => implode(' ', $conflicts),
            ]);
        }

        return $data;
    }
}
