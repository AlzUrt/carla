# 🎙️ Système Audio Conversationnel - Guide d'intégration

## ⚡ Quick Start (5 minutes)

### 1. Obtenez vos clés API

| Service | URL | Gratuit? |
|---------|-----|----------|
| OpenAI Whisper | https://platform.openai.com/api-keys | $5 de crédit gratuit |
| Claude | https://console.anthropic.com/ | Essai gratuit |
| ElevenLabs | https://elevenlabs.io/ | 10k chars/mois |

### 2. Configurez votre .env

```bash
# Dans le fichier .env (déjà à la fin du fichier)
OPENAI_API_KEY=sk-proj-xxxxx
CLAUDE_API_KEY=sk-ant-xxxxx
ELEVENLABS_API_KEY=xxxxx
ELEVENLABS_VOICE_ID=21m00Tcm4TlvDq8ikWAM
```

### 3. Créez les répertoires nécessaires

```bash
mkdir -p storage/app/conversations/{audio_questions,audio_answers}
chmod -R 755 storage/app/conversations
```

### 4. Utilisez dans Filament

1. Allez à http://localhost/admin
2. Cliquez sur "Conversations"
3. Créez une nouvelle conversation
4. Cliquez sur "🎤 Démarrer l'enregistrement"
5. Posez votre question
6. Cliquez "⏹️ Arrêter"
7. Écoutez la réponse! 🎧

---

## 🔍 Fichiers clés du système

### Pour comprendre le flux
1. [ConversationAudioController.php](app/Rest/Controllers/ConversationAudioController.php) - Point d'entrée API
2. [voice-recorder-input.blade.php](resources/views/filament/components/voice-recorder-input.blade.php) - Interface utilisateur

### Pour modifier le comportement
1. [WhisperService.php](app/Services/WhisperService.php) - Transcription audio
2. [ClaudeService.php](app/Services/ClaudeService.php) - Réponse IA
3. [ElevenLabsService.php](app/Services/ElevenLabsService.php) - Synthèse vocale

### Pour tester
```bash
php artisan voice:test /chemin/vers/audio.webm
php artisan test tests/Feature/VoiceConversationTest.php
```

---

## 🎯 API REST

### Endpoint

```http
POST /api/conversations/process-audio
Content-Type: multipart/form-data
X-CSRF-TOKEN: [token]

audio: [fichier audio WebM, WAV, MP3 ou OGG]
conversation_id: [optionnel - pour mettre à jour une conversation existante]
```

### Réponse de succès (200)

```json
{
  "success": true,
  "conversation_id": 1,
  "text_question": "Quel est la capitale de la France?",
  "text_answer": "La capitale de la France est Paris, située dans le nord-centre du pays...",
  "audio_answer_url": "/storage/conversations/audio_answers/answer_xxx.mp3",
  "durations": {
    "stt_ms": 1200,
    "llm_ms": 850,
    "tts_ms": 2100,
    "total_ms": 4150
  }
}
```

### Réponse d'erreur (400/500)

```json
{
  "success": false,
  "error": "Erreur lors de la transcription: ..."
}
```

---

## 🛠️ Troubleshooting

### ❌ "Erreur d'accès au microphone"
- Vérifiez que HTTPS est activé (obligatoire en production)
- Vérifiez les permissions du navigateur

### ❌ "Invalid API Key"
- Vérifiez que vos clés sont correctes dans `.env`
- Vérifiez que vous avez du crédit sur les comptes API

### ❌ Pas de réponse audio
- Vérifiez `storage/logs/laravel.log` pour les erreurs
- Testez avec: `php artisan voice:test /path/to/audio.webm`

### ❌ Les fichiers audio ne sont pas sauvegardés
```bash
# Vérifier les permissions
chmod -R 755 storage/app/conversations

# Vérifier l'espace disque
df -h
```

---

## 📊 Modèle de données

```php
// Chaque conversation contient:
Conversation {
    id: int,
    audio_question_path: string,      // Fichier audio question
    text_question: string,            // Transcription
    text_answer: string,              // Réponse Claude
    audio_answer_path: string,        // Fichier audio réponse
    duration_stt_ms: int,            // Temps transcription
    duration_llm_ms: int,            // Temps Claude
    duration_tts_ms: int,            // Temps synthèse vocale
    status: string,                   // pending|processing|completed|failed
    error: string|null,               // Message d'erreur si applicable
    created_at: timestamp,
    updated_at: timestamp
}
```

---

## 💰 Estimation des coûts

Pour **1000 conversations** avec:
- Audio question: 5 secondes (français)
- Réponse Claude: 50 mots
- Synthèse audio de la réponse

| Service | Coût | Calcul |
|---------|------|--------|
| **Whisper** | $0.10 | 1000 × 5s = 1.39h × $0.044/min |
| **Claude** | $0.20 | 1000 × 150 tokens × tarif |
| **ElevenLabs** | $2.00 | 1000 × 50 chars = 50k chars |
| **TOTAL** | **$2.30** | **$0.0023 par conversation** |

---

## 🎨 Personnalisations courantes

### Changer la langue
```php
// Dans WhisperService.php
'language' => 'en', // 'fr', 'es', 'de', etc.
```

### Changer le modèle Claude
```php
// Dans ClaudeService.php
'model' => 'claude-3-opus-20240229', // Plus puissant
```

### Changer la voix
```bash
# Dans .env
ELEVENLABS_VOICE_ID=EXAVITQu4vr4xnSDxMaL  # Callum (britannique)
```

---

## 🔒 Sécurité

- ✅ Validation des fichiers audio
- ✅ CSRF protection
- ✅ Variables d'environnement pour les clés
- ✅ Gestion des erreurs robuste
- ✅ Logging des erreurs

### Recommandations supplémentaires

```php
// Rate limiting
Route::post('/api/conversations/process-audio', ...)
    ->middleware('throttle:10,1'); // 10 par minute

// Chiffrement des données sensibles
$conversation->audio_question_path = encrypt($path);
```

---

## 📚 Ressources

- [OpenAI Whisper Docs](https://platform.openai.com/docs/api-reference/audio)
- [Anthropic Claude Docs](https://docs.anthropic.com/)
- [ElevenLabs Docs](https://docs.elevenlabs.io/)
- [Web Audio API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API)

---

## 🚀 Prochaines étapes

1. **Multi-utilisateur**: Associer les conversations aux utilisateurs
2. **Historique**: Tableau de bord avec l'historique des conversations
3. **Analytics**: Metrics de performance et d'utilisation
4. **Cache**: Cacher les réponses communes
5. **Queue asynchrone**: Traitement en arrière-plan avec Redis
6. **Webhooks**: Notifications externes

---

**Questions?** Consultez les autres fichiers de documentation:
- `VOICE_ASSISTANT_GUIDE.md` - Guide détaillé
- `IMPLEMENTATION_SUMMARY.md` - Architecture technique
- `DEPLOYMENT_CHECKLIST.md` - Déploiement en production
- `USE_CASES_AND_ALTERNATIVES.md` - Cas d'usage et alternatives
