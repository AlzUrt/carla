# Guide d'utilisation - Système Audio Conversationnel

## Configuration

### 1. Variables d'environnement

Mettez à jour votre fichier `.env` avec vos clés API:

```env
# OpenAI Whisper API
OPENAI_API_KEY=sk-xxxxxxxxxxxxx

# Anthropic Claude API
CLAUDE_API_KEY=sk-ant-xxxxxxxxxxxxx

# ElevenLabs API
ELEVENLABS_API_KEY=xxxxxxxxxxxx
ELEVENLABS_VOICE_ID=21m00Tcm4TlvDq8ikWAM  # ID de la voix (optional)
```

### 2. Récupérer les clés API

#### OpenAI (Whisper)
1. Allez sur https://platform.openai.com/api-keys
2. Créez une nouvelle clé API
3. Copiez-la dans `OPENAI_API_KEY`

#### Anthropic Claude
1. Allez sur https://console.anthropic.com/
2. Créez une nouvelle clé API
3. Copiez-la dans `CLAUDE_API_KEY`

#### ElevenLabs
1. Allez sur https://elevenlabs.io/
2. Créez un compte gratuit
3. Récupérez votre clé API
4. (Optionnel) Choisissez un ID de voix sur https://elevenlabs.io/voice-lab

## Utilisation

### Dans Filament

1. Allez sur la section "Conversations" dans Filament
2. Créez une nouvelle conversation
3. Dans la section "Enregistrement Vocal":
   - Cliquez sur "🎤 Démarrer l'enregistrement"
   - Posez votre question en français
   - Cliquez sur "⏹️ Arrêter"

### Workflow automatique

Le système effectuera automatiquement:

1. **STT (Speech-to-Text)** - Whisper
   - Transcrit votre audio en texte français

2. **LLM (Large Language Model)** - Claude
   - Traite votre question
   - Génère une réponse contextuelle

3. **TTS (Text-to-Speech)** - ElevenLabs
   - Synthétise la réponse en audio
   - Joue l'audio automatiquement

### Résultats

Après traitement, vous verrez:
- ✅ Texte transcrit de votre question
- ✅ Réponse textuelle de Claude
- ✅ Lecteur audio pour la réponse
- ✅ Temps de traitement pour chaque étape

## Architecture Technique

### Structure des fichiers

```
app/
├── Services/
│   ├── WhisperService.php      # Transcription audio
│   ├── ClaudeService.php       # Réponse IA
│   └── ElevenLabsService.php   # Synthèse vocale
├── Rest/Controllers/
│   └── ConversationAudioController.php  # Endpoint API
├── Filament/
│   ├── Components/
│   │   └── VoiceRecorderInput.php       # Composant enregistreur
│   └── Resources/Conversations/
│       └── Schemas/ConversationForm.php # Formulaire
└── Models/
    └── Conversation.php         # Modèle de données

routes/
└── api.php                      # Routes API

resources/views/filament/components/
└── voice-recorder-input.blade.php       # Vue du composant
```

### Endpoint API

**POST** `/api/conversations/process-audio`

Paramètres:
- `audio` (file) - Fichier audio WebM
- `conversation_id` (optional) - ID de conversation existante

Réponse:
```json
{
  "success": true,
  "conversation_id": 1,
  "text_question": "Quel est la capitale de la France?",
  "text_answer": "La capitale de la France est Paris...",
  "audio_answer_url": "/storage/...",
  "durations": {
    "stt_ms": 1200,
    "llm_ms": 800,
    "tts_ms": 2100,
    "total_ms": 4100
  }
}
```

## Dépannage

### "Erreur d'accès au microphone"
- Vérifiez les permissions du navigateur
- Utilisez HTTPS (les API WebRTC nécessitent HTTPS en production)

### "Erreur lors du traitement"
- Vérifiez que toutes les clés API sont configurées
- Vérifiez la console du serveur (logs)
- Vérifiez les quotas API

### Pas de réponse audio
- Vérifiez que ElevenLabs API fonctionne
- Vérifiez le crédit/quota ElevenLabs

## Coûts

- **Whisper**: ~$0.02 par heure audio
- **Claude**: ~$3-15 par million de tokens
- **ElevenLabs**: Gratuit jusqu'à 10k caractères/mois

## Notes de sécurité

- Ne commit jamais vos clés API
- Utilisez des variables d'environnement
- En production, limitez l'accès à l'endpoint API
- Versionnez les fichiers audio avec prudence (espace disque)

## Support

Pour toute question ou issue, consultez:
- [OpenAI Documentation](https://platform.openai.com/docs)
- [Anthropic Documentation](https://docs.anthropic.com)
- [ElevenLabs Documentation](https://docs.elevenlabs.io)
