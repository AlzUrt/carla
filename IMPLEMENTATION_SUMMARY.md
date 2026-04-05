# Système Audio Conversationnel Filament

## 🎯 Vue d'ensemble

Système complet intégrant:
- **Enregistrement audio** côté client avec Web Audio API
- **Transcription** via OpenAI Whisper
- **Réponse IA** via Anthropic Claude
- **Synthèse vocale** via ElevenLabs

## 📦 Fichiers créés/modifiés

### Services
- `app/Services/WhisperService.php` - Transcription audio
- `app/Services/ClaudeService.php` - Traitement IA
- `app/Services/ElevenLabsService.php` - Synthèse vocale

### Contrôleurs
- `app/Rest/Controllers/ConversationAudioController.php` - API endpoint

### Composants Filament
- `app/Filament/Components/VoiceRecorderInput.php` - Composant enregistreur
- `resources/views/filament/components/voice-recorder-input.blade.php` - Vue Blade

### Modèles & Base de données
- `app/Models/Conversation.php` - Modèle existant (pré-configuré)
- `database/migrations/2026_04_05_141158_create_conversations_table.php` - Table existante

### Configuration
- `app/Filament/Resources/Conversations/Schemas/ConversationForm.php` - Formulaire mis à jour
- `config/services.php` - Configuration des services
- `.env` - Variables d'environnement
- `routes/api.php` - Routes API

### Tests & Commandes
- `tests/Feature/VoiceConversationTest.php` - Tests
- `app/Console/Commands/TestVoiceWorkflow.php` - Commande de test

### Documentation
- `VOICE_ASSISTANT_GUIDE.md` - Guide complet
- `.env.example.voice` - Fichier exemple

## 🚀 Démarrage rapide

### 1. Configuration des clés API

```bash
# Modifier .env avec vos clés
OPENAI_API_KEY=sk-proj-xxxxx
CLAUDE_API_KEY=sk-ant-xxxxx
ELEVENLABS_API_KEY=xxxxx
```

### 2. Créer un répertoire de stockage

```bash
mkdir -p storage/app/conversations/{audio_questions,audio_answers}
```

### 3. Tester l'API directement

```bash
# Depuis votre navigateur ou postman:
POST /api/conversations/process-audio
Content-Type: multipart/form-data
audio: [fichier_audio.webm]
```

### 4. Utiliser dans Filament

1. Accédez à `http://yourapp.local/admin`
2. Allez dans "Conversations"
3. Créez une nouvelle conversation
4. Cliquez sur "🎤 Démarrer l'enregistrement"
5. Posez votre question
6. Écoutez la réponse!

## 🔧 Architecture

```
┌─────────────────┐
│  Filament Page  │
│  (Vue Blade)    │
└────────┬────────┘
         │ Audio WebM
         ▼
┌─────────────────────────────────────┐
│  POST /api/conversations/process-audio │
└────────┬────────────────────────────┘
         │
    ┌────┴────────────────────┐
    │                         │
    ▼ 1️⃣                     ▼ 2️⃣
┌──────────────┐       ┌──────────────┐
│ Whisper API  │       │ Claude API   │
│ (STT)        │────▶  │ (LLM)        │
└──────────────┘       └──────────────┘
                            │
                            ▼ 3️⃣
                      ┌──────────────────┐
                      │ ElevenLabs API   │
                      │ (TTS)            │
                      └────────┬─────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
                    ▼                     ▼
            ┌──────────────┐    ┌─────────────────┐
            │ MP3 Saved    │    │ Database Record │
            │ (Storage)    │    │ (Conversation)  │
            └──────────────┘    └─────────────────┘
```

## 📊 Données enregistrées

Chaque conversation stocke:
- `audio_question_path` - Chemin du fichier audio question
- `text_question` - Transcription de la question
- `text_answer` - Réponse de Claude
- `audio_answer_path` - Chemin du fichier audio réponse
- `duration_stt_ms` - Temps de transcription
- `duration_llm_ms` - Temps de traitement IA
- `duration_tts_ms` - Temps de synthèse vocale
- `status` - État (pending, processing, completed, failed)
- `error` - Message d'erreur si applicable

## 🛡️ Sécurité

- ✅ Validation des fichiers audio
- ✅ Gestion des erreurs API
- ✅ Stockage sécurisé des fichiers
- ✅ CSRF token sur le formulaire
- ✅ Variables d'environnement pour les clés

## ⚙️ Personalisations possibles

### Langue
Dans `app/Services/WhisperService.php`:
```php
'language' => 'en', // Changer pour 'es', 'de', etc.
```

### Modèle Claude
Dans `app/Services/ClaudeService.php`:
```php
'model' => 'claude-3-opus-20240229', // Plus puissant
```

### Voix ElevenLabs
Dans `.env`:
```env
ELEVENLABS_VOICE_ID=EXAVITQu4vr4xnSDxMaL  # Autre voix
```

## 📞 Support API

- **Whisper**: https://platform.openai.com/docs/api-reference/audio
- **Claude**: https://docs.anthropic.com/
- **ElevenLabs**: https://docs.elevenlabs.io/

## 🐛 Dépannage

Si vous rencontrez des problèmes:

1. **Vérifiez les logs**: `storage/logs/laravel.log`
2. **Testez avec artisan**: `php artisan voice:test /path/to/audio.webm`
3. **Vérifiez les permissions**: `storage/app/conversations/` doit être writable
4. **Vérifiez les clés API**: Assurez-vous qu'elles sont valides

## 📈 Prochaines étapes

- [ ] Ajouter plusieurs utilisateurs avec isolation des données
- [ ] Historique des conversations
- [ ] Paramètres de personnalisation dans l'admin
- [ ] Notifications Slack/Email
- [ ] Queue pour les traitements longs
- [ ] Analytics/Dashboard
- [ ] Support de plusieurs langues
