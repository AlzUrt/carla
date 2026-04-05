<div class="space-y-3">
    @php
        $audioPath = $type === 'question' ? $getRecord()->audio_question_path : $getRecord()->audio_answer_path;
        $audioUrl = $audioPath ? route('audio.serve', ['path' => $audioPath]) : null;
    @endphp

    @if($audioUrl)
        <div class="rounded-lg border border-gray-300 bg-gray-50 p-3">
            <audio 
                controls
                class="w-full"
                controlsList="nodownload"
            >
                <source src="{{ $audioUrl }}" type="{{ $type === 'answer' ? 'audio/mpeg' : 'audio/webm' }}">
                Votre navigateur ne supporte pas la balise audio.
            </audio>
        </div>
        <p class="text-xs text-gray-600">
            <strong>Chemin:</strong> {{ $audioPath }}
        </p>
    @else
        <p class="text-sm text-gray-500 italic">Aucun fichier audio disponible</p>
    @endif
</div>
