<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions\Action;

trait ConfirmsDeactivation
{
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->requiresConfirmation(fn (): bool => (bool) $this->record->getAttribute('is_active')
                && array_key_exists('is_active', $this->data)
                && ! (bool) $this->data['is_active'])
            ->modalHeading('Nonaktifkan data ini?')
            ->modalDescription('Data tetap tersimpan, tetapi tidak akan digunakan sampai diaktifkan kembali.')
            ->modalSubmitActionLabel('Ya, nonaktifkan');
    }
}
