<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        activity('document')
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'attributes' => $document->getAttributes(),
                'event' => 'created'
            ])
            ->log('Создан документ: ' . $document->title);
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        $changes = $document->getChanges();
        $original = $document->getOriginal();
        
        // Исключаем системные поля из логирования
        $excludeFields = ['updated_at', 'created_at'];
        $changes = array_diff_key($changes, array_flip($excludeFields));
        
        if (empty($changes)) {
            return;
        }

        activity('document')
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'attributes' => $changes,
                'old' => array_intersect_key($original, $changes),
                'event' => 'updated'
            ])
            ->log('Обновлён документ: ' . $document->title);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        activity('document')
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'attributes' => $document->getAttributes(),
                'event' => 'deleted'
            ])
            ->log('Удалён документ: ' . $document->title);
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        activity('document')
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'attributes' => $document->getAttributes(),
                'event' => 'restored'
            ])
            ->log('Восстановлен документ: ' . $document->title);
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        activity('document')
            ->causedBy(Auth::user())
            ->performedOn($document)
            ->withProperties([
                'attributes' => $document->getAttributes(),
                'event' => 'force_deleted'
            ])
            ->log('Окончательно удалён документ: ' . $document->title);
    }
}
