# Guide d'intégration du système audio dans d'autres ressources Filament

## 🎯 Intégrer dans n'importe quelle ressource Filament

### Option 1: Dans un formulaire existant

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Components\VoiceRecorderInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class MyCustomResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Enregistrement Vocal')
                    ->description('Enregistrez votre message vocal')
                    ->schema([
                        VoiceRecorderInput::make(),
                    ])
                    ->collapsible(),
                
                // Autres champs...
            ]);
    }
}
```

### Option 2: Dans une action personnalisée

```php
<?php

use Filament\Actions\Action;
use App\Filament\Components\VoiceRecorderInput;

public function getActions(): array
{
    return [
        Action::make('voice_input')
            ->label('Ajouter via voix')
            ->form([
                VoiceRecorderInput::make(),
            ])
            ->action(function (array $data) {
                // Traiter les données vocales
            }),
    ];
}
```

### Option 3: Sur une page personnalisée

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Components\VoiceRecorderInput;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class VoiceCommandCenter extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-microphone';
    protected static string $view = 'filament.pages.voice-command-center';
    
    public function getFormSchema(): array
    {
        return [
            Section::make('Centre de Commandes Vocales')
                ->schema([
                    VoiceRecorderInput::make(),
                ]),
        ];
    }
}
```

## 🎤 Utiliser l'API directement (sans Filament)

```javascript
// Sur n'importe quelle page web

async function recordAndProcess() {
    // 1. Obtenir l'accès au microphone
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const mediaRecorder = new MediaRecorder(stream);
    
    // 2. Enregistrer
    const chunks = [];
    mediaRecorder.ondataavailable = e => chunks.push(e.data);
    mediaRecorder.start();
    
    // Arrêter après 10 secondes
    setTimeout(() => {
        mediaRecorder.stop();
        mediaRecorder.onstop = async () => {
            const blob = new Blob(chunks, { type: 'audio/webm' });
            
            // 3. Envoyer à l'API
            const formData = new FormData();
            formData.append('audio', blob);
            
            const response = await fetch('/api/conversations/process-audio', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            
            const data = await response.json();
            
            // 4. Utiliser la réponse
            console.log('Question:', data.text_question);
            console.log('Réponse:', data.text_answer);
            
            // 5. Jouer l'audio
            const audio = new Audio(data.audio_answer_url);
            audio.play();
        };
    }, 10000);
}
```

## 🧩 Composant personnalisé (exemple avancé)

```php
<?php

namespace App\Filament\Components;

use Filament\Forms\Components\Component;

class AdvancedVoiceRecorder extends Component
{
    protected string $view = 'filament.components.advanced-voice-recorder';
    
    protected array $languages = ['fr', 'en', 'es'];
    protected string $language = 'fr';
    protected bool $autoPlay = true;
    protected int $maxDuration = 60; // secondes
    
    public function language(string $language): static
    {
        $this->language = $language;
        return $this;
    }
    
    public function autoPlay(bool $value): static
    {
        $this->autoPlay = $value;
        return $this;
    }
    
    public function maxDuration(int $seconds): static
    {
        $this->maxDuration = $seconds;
        return $this;
    }
    
    public function getLanguage(): string
    {
        return $this->language;
    }
    
    public function shouldAutoPlay(): bool
    {
        return $this->autoPlay;
    }
    
    public function getMaxDuration(): int
    {
        return $this->maxDuration;
    }
}
```

Et la vue correspondante:

```blade
{{-- resources/views/filament/components/advanced-voice-recorder.blade.php --}}
<div class="voice-recorder" data-language="{{ $getLanguage() }}" data-max-duration="{{ $getMaxDuration() }}">
    <button id="startBtn">Enregistrer</button>
    <button id="stopBtn" disabled>Arrêter</button>
    <div id="transcript"></div>
    <audio id="player" controls @if($shouldAutoPlay()) autoplay @endif></audio>
</div>
```

## 🔗 Intégrer avec les relations Eloquent

```php
<?php

namespace App\Models;

class Support extends Model
{
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}

// Dans la ressource Filament
class SupportResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email'),
                
                // Ajouter les conversations associées
                Section::make('Conversations Vocales')
                    ->relationship('conversations')
                    ->schema([
                        VoiceRecorderInput::make(),
                    ]),
            ]);
    }
}
```

## 📊 Afficher l'historique des conversations

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('text_question')
                ->label('Question')
                ->limit(50)
                ->searchable(),
                
            TextColumn::make('text_answer')
                ->label('Réponse')
                ->limit(50),
                
            BadgeColumn::make('status')
                ->colors([
                    'warning' => 'pending',
                    'info' => 'processing',
                    'success' => 'completed',
                    'danger' => 'failed',
                ]),
                
            TextColumn::make('getTotalDurationMs')
                ->label('Temps total')
                ->formatStateUsing(fn ($state) => $state ? $state . ' ms' : '-'),
        ]);
}
```

## 🎯 Webhook après enregistrement

```php
<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationAudioController extends Controller
{
    public function processAudio(Request $request)
    {
        // ... traitement normal ...
        
        $conversation = Conversation::create([/*...*/]);
        
        // Déclencher webhook
        dispatch(new SendWebhook('conversation.created', [
            'conversation_id' => $conversation->id,
            'question' => $conversation->text_question,
            'answer' => $conversation->text_answer,
        ]));
        
        return response()->json([/*...*/]);
    }
}
```

## 🏗️ Architecture multi-tenants

```php
<?php

class ConversationAudioController extends Controller
{
    public function processAudio(Request $request)
    {
        $conversationId = $request->input('conversation_id');
        
        $conversation = Conversation::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($conversationId);
            
        // Traitement...
    }
}
```

## 📱 Version mobile-friendly

```blade
<div class="flex flex-col gap-4 p-4">
    {{-- Version mobile du composant --}}
    @if(request()->is('*/mobile/*'))
        <div class="fixed bottom-4 right-4">
            <button class="rounded-full bg-blue-600 p-4 text-white shadow-lg">
                🎤
            </button>
        </div>
    @else
        {{-- Version desktop --}}
        <x-voice-recorder />
    @endif
</div>
```

## 🔌 Intégration avec les notifications

```php
<?php

use Illuminate\Notifications\Notification;

class ConversationRecorded extends Notification
{
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }
    
    public function toArray($notifiable)
    {
        return [
            'message' => 'Nouvelle conversation enregistrée',
            'conversation_id' => $this->conversation->id,
            'question' => $this->conversation->text_question,
        ];
    }
}

// Lors du traitement
$user->notify(new ConversationRecorded($conversation));
```

---

## 📚 Ressources

- [Filament Documentation](https://filamentphp.com/docs)
- [Filament Form Components](https://filamentphp.com/docs/3.x/forms/fields/getting-started)
- [Web Audio API](https://developer.mozilla.org/docs/Web/API/Web_Audio_API)

---

Ces exemples montraient comment adapter le système audio à différents contextes. N'hésitez pas à les combiner selon vos besoins!
