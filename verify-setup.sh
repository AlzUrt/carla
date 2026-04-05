#!/bin/bash
# Script de vérification pré-déploiement pour le système audio

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔍 Vérification du système audio conversationnel..."
echo ""

# 1. Vérifier les fichiers
echo -e "${YELLOW}1. Vérification des fichiers créés...${NC}"

files_to_check=(
    "app/Services/WhisperService.php"
    "app/Services/ClaudeService.php"
    "app/Services/ElevenLabsService.php"
    "app/Rest/Controllers/ConversationAudioController.php"
    "app/Filament/Components/VoiceRecorderInput.php"
    "resources/views/filament/components/voice-recorder-input.blade.php"
    "app/Console/Commands/TestVoiceWorkflow.php"
    "app/Jobs/ProcessConversationAudio.php"
)

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file MANQUANT!"
    fi
done

echo ""

# 2. Vérifier les répertoires
echo -e "${YELLOW}2. Vérification des répertoires...${NC}"

dirs_to_check=(
    "storage/app/conversations/audio_questions"
    "storage/app/conversations/audio_answers"
    "app/Services"
    "app/Filament/Components"
)

for dir in "${dirs_to_check[@]}"; do
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $dir"
    else
        echo -e "${RED}✗${NC} $dir MANQUANT!"
    fi
done

echo ""

# 3. Vérifier les variables d'environnement
echo -e "${YELLOW}3. Vérification des variables d'environnement...${NC}"

if grep -q "OPENAI_API_KEY" .env; then
    if [ ! "$(grep '^OPENAI_API_KEY=' .env)" = "OPENAI_API_KEY=sk-proj-REMPLACEZ_PAR_VOTRE_CLÉ" ]; then
        echo -e "${GREEN}✓${NC} OPENAI_API_KEY configuré"
    else
        echo -e "${YELLOW}⚠${NC} OPENAI_API_KEY non configuré (placeholder détecté)"
    fi
else
    echo -e "${RED}✗${NC} OPENAI_API_KEY manquant de .env"
fi

if grep -q "CLAUDE_API_KEY" .env; then
    if [ ! "$(grep '^CLAUDE_API_KEY=' .env)" = "CLAUDE_API_KEY=sk-ant-REMPLACEZ_PAR_VOTRE_CLÉ" ]; then
        echo -e "${GREEN}✓${NC} CLAUDE_API_KEY configuré"
    else
        echo -e "${YELLOW}⚠${NC} CLAUDE_API_KEY non configuré (placeholder détecté)"
    fi
else
    echo -e "${RED}✗${NC} CLAUDE_API_KEY manquant de .env"
fi

if grep -q "ELEVENLABS_API_KEY" .env; then
    if [ ! "$(grep '^ELEVENLABS_API_KEY=' .env)" = "ELEVENLABS_API_KEY=REMPLACEZ_PAR_VOTRE_CLÉ" ]; then
        echo -e "${GREEN}✓${NC} ELEVENLABS_API_KEY configuré"
    else
        echo -e "${YELLOW}⚠${NC} ELEVENLABS_API_KEY non configuré (placeholder détecté)"
    fi
else
    echo -e "${RED}✗${NC} ELEVENLABS_API_KEY manquant de .env"
fi

echo ""

# 4. Vérifier les permissions
echo -e "${YELLOW}4. Vérification des permissions de storage...${NC}"

if [ -w "storage/app/conversations" ]; then
    echo -e "${GREEN}✓${NC} storage/app/conversations est writable"
else
    echo -e "${RED}✗${NC} storage/app/conversations n'est pas writable"
fi

echo ""

# 5. Vérifier la configuration Laravel
echo -e "${YELLOW}5. Vérification de la configuration Laravel...${NC}"

if grep -q "ConversationAudioController" routes/api.php; then
    echo -e "${GREEN}✓${NC} Route API configurée"
else
    echo -e "${RED}✗${NC} Route API manquante de routes/api.php"
fi

if grep -q "OPENAI_API_KEY" config/services.php; then
    echo -e "${GREEN}✓${NC} Services configurés dans config/services.php"
else
    echo -e "${RED}✗${NC} Services manquants de config/services.php"
fi

echo ""

# 6. Vérifier les documents
echo -e "${YELLOW}6. Vérification de la documentation...${NC}"

docs=(
    "VOICE_ASSISTANT_GUIDE.md"
    "IMPLEMENTATION_SUMMARY.md"
    "DEPLOYMENT_CHECKLIST.md"
    "USE_CASES_AND_ALTERNATIVES.md"
    "AUDIO_SYSTEM_README.md"
    "INTEGRATION_GUIDE.md"
    "COMPLETE_SUMMARY.md"
)

for doc in "${docs[@]}"; do
    if [ -f "$doc" ]; then
        echo -e "${GREEN}✓${NC} $doc"
    else
        echo -e "${YELLOW}⚠${NC} $doc manquant"
    fi
done

echo ""
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Vérification complète${NC}"
echo ""
echo "📋 Prochaines étapes:"
echo "1. Configurez vos clés API dans .env"
echo "2. Exécutez: mkdir -p storage/app/conversations/{audio_questions,audio_answers}"
echo "3. Exécutez: php artisan migrate"
echo "4. Testez avec: php artisan voice:test /chemin/audio.webm"
echo "5. Accédez à http://localhost/admin/conversations"
echo ""
