@use('Illuminate\Support\Facades\Storage')

<div class="inline-flex items-center gap-2">
    @php
        $audioPath = $type === 'question' ? $getRecord()->audio_question_path : $getRecord()->audio_answer_path;
        $audioUrl = $audioPath ? route('audio.serve', ['path' => $audioPath]) : null;
    @endphp
    
    @if($audioUrl)
        <audio 
            controls
            class="h-8"
            style="width: 180px;"
        >
            <source src="{{ $audioUrl }}" type="{{ $type === 'answer' ? 'audio/mpeg' : 'audio/webm' }}">
            Votre navigateur ne supporte pas la lecture audio.
        </audio>
    @else
        <span class="text-gray-400 text-sm">Aucun audio</span>
    @endif
</div>

