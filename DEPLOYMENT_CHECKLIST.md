# ✅ Checklist de déploiement - Système Audio Conversationnel

## 📋 Avant le déploiement en production

### Configuration API

- [ ] **OpenAI Whisper**
  - [ ] Créé une clé API sur https://platform.openai.com/api-keys
  - [ ] Vérifier que le compte a suffisamment de crédits
  - [ ] Tester avec un audio court
  - [ ] Ajouter à `.env`: `OPENAI_API_KEY=sk-proj-xxxxx`

- [ ] **Anthropic Claude**
  - [ ] Créé une clé API sur https://console.anthropic.com/
  - [ ] Vérifier que le compte a suffisamment de crédits
  - [ ] Tester avec un message court
  - [ ] Ajouter à `.env`: `CLAUDE_API_KEY=sk-ant-xxxxx`

- [ ] **ElevenLabs**
  - [ ] Créé une clé API sur https://elevenlabs.io/
  - [ ] Choisi et tester une voix
  - [ ] Vérifier que le compte a du crédit
  - [ ] Ajouter à `.env`: `ELEVENLABS_API_KEY=xxxxx`

### Infrastructure Laravel

- [ ] Exécuter les migrations: `php artisan migrate`
- [ ] Créer les répertoires de stockage:
  ```bash
  mkdir -p storage/app/conversations/{audio_questions,audio_answers}
  chmod -R 755 storage/app/conversations
  ```
- [ ] Publier les assets Filament: `php artisan filament:install`
- [ ] Nettoyer le cache: `php artisan config:cache`

### Sécurité

- [ ] Vérifier que `APP_DEBUG=false` en production
- [ ] Vérifier que les clés API ne sont **pas** commitées
- [ ] Ajouter `.env` à `.gitignore` (devrait être par défaut)
- [ ] Configurer HTTPS (obligatoire pour Web Audio API)
- [ ] Ajouter rate limiting (voir `USE_CASES_AND_ALTERNATIVES.md`)

### Stockage

- [ ] Vérifier que `storage/` est writable par le serveur web
- [ ] Si utilisant S3: configurer AWS dans `.env`
  ```env
  FILESYSTEM_DISK=s3
  AWS_ACCESS_KEY_ID=xxxxx
  AWS_SECRET_ACCESS_KEY=xxxxx
  AWS_DEFAULT_REGION=eu-west-1
  AWS_BUCKET=your-bucket
  ```
- [ ] Ajouter une politique de suppression des anciens fichiers

### Base de données

- [ ] Vérifier que les migrations sont à jour: `php artisan migrate:status`
- [ ] Tester une conversation de bout en bout
- [ ] Configurer les backups réguliers

### Performance

- [ ] Configurer une queue (Redis recommandé):
  ```env
  QUEUE_CONNECTION=redis
  ```
- [ ] Tester le traitement avec une queue
- [ ] Ajouter des indexes pour les requêtes fréquentes

### Monitoring

- [ ] Configurer la surveillance des logs: `storage/logs/`
- [ ] Ajouter des alertes pour les erreurs API
- [ ] Monitorer les coûts API mensuels
- [ ] Ajouter une métrique pour les temps de traitement

### Tests

- [ ] Exécuter les tests: `php artisan test`
- [ ] Tester avec plusieurs navigateurs
- [ ] Tester sur mobile (si applicable)
- [ ] Vérifier la performance avec des fichiers audio longs

## 🚀 Déploiement

### Avant de déployer

```bash
# 1. Commit tous les changements
git add .
git commit -m "Feat: Add voice conversation system"

# 2. Vérifier que tout fonctionne localement
php artisan test
php artisan serve

# 3. Vérifier les dépendances
composer update --lock
```

### Déploiement sur le serveur

```bash
# SSH vers le serveur
ssh user@server.com

# 1. Pull les changements
cd /var/www/carla
git pull origin main

# 2. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 3. Exécuter les migrations
php artisan migrate --force

# 4. Nettoyer et optimiser
php artisan config:cache
php artisan view:cache
php artisan route:cache

# 5. Redémarrer la queue (si applicable)
php artisan queue:restart

# 6. Vérifier les logs
tail -f storage/logs/laravel.log
```

### Configuration du serveur web

#### Nginx
```nginx
location /api/conversations/process-audio {
    # Permettre les uploads volumineux (20MB)
    client_max_body_size 20M;
    
    # Timeout augmenté pour les APIs
    proxy_connect_timeout 10s;
    proxy_read_timeout 30s;
    
    proxy_pass http://127.0.0.1:9000;
}
```

#### Apache
```apache
<Location /api/conversations/process-audio>
    # Permettre les uploads
    LimitRequestBody 20971520
    
    # Timeouts
    ProxyTimeout 30
</Location>
```

## 🔍 Post-déploiement

### Tests de validation

- [ ] Ouvrir l'admin Filament: `/admin`
- [ ] Naviguer vers "Conversations"
- [ ] Tester le bouton d'enregistrement audio
- [ ] Vérifier que la réponse fonctionne
- [ ] Vérifier que les fichiers sont sauvegardés

### Monitoring du premier jour

- [ ] Surveiller les logs d'erreur
- [ ] Vérifier les coûts API (OpenAI, Claude, ElevenLabs)
- [ ] Tester depuis différents appareils
- [ ] Vérifier la latence globale

### Métriques à suivre

```sql
-- Conversations réussies
SELECT COUNT(*) FROM conversations WHERE status = 'completed';

-- Conversations échouées
SELECT COUNT(*) FROM conversations WHERE status = 'failed';

-- Temps moyen de traitement
SELECT AVG(duration_stt_ms + duration_llm_ms + duration_tts_ms) 
FROM conversations 
WHERE status = 'completed';

-- Erreurs les plus fréquentes
SELECT error, COUNT(*) 
FROM conversations 
WHERE status = 'failed' 
GROUP BY error;
```

## 🛠️ Troubleshooting post-déploiement

### Le bouton d'enregistrement ne fonctionne pas
```bash
# Vérifier les logs Filament
tail -f storage/logs/laravel.log

# Vérifier que HTTPS est activé
# (Web Audio API nécessite HTTPS)
```

### Les fichiers audio ne sont pas sauvegardés
```bash
# Vérifier les permissions du répertoire
chmod -R 755 storage/app/conversations

# Vérifier la taille du disque
df -h
```

### Les API retournent des erreurs d'authentification
```bash
# Vérifier les clés API
grep -E "OPENAI|CLAUDE|ELEVENLABS" .env

# Vérifier les quotas API
# Aller sur les dashboards: openai.com, anthropic.com, elevenlabs.io
```

### Performance lente
```bash
# Ajouter une queue pour les traitements
QUEUE_CONNECTION=redis

# Configurer Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Redémarrer la queue
php artisan queue:work
```

## 📊 Étapes d'optimisation

Après 1 semaine d'utilisation:

- [ ] Analyser les erreurs les plus communes
- [ ] Optimiser les appels API (caching, compression)
- [ ] Ajouter des limites de taux si nécessaire
- [ ] Considérer un plan API payant si utilisation élevée
- [ ] Documenter les patterns d'utilisation

Après 1 mois:

- [ ] Évaluer les coûts vs bénéfices
- [ ] Considérer des alternatives moins chères si nécessaire
- [ ] Mettre en place des analytics détaillées
- [ ] Planifier les améliorations futures

## 🎯 Succès = ✅

Le déploiement est réussi quand:

1. ✅ Vous pouvez enregistrer un audio depuis Filament
2. ✅ Le texte est transcrit correctement
3. ✅ Claude fournit une réponse pertinente
4. ✅ L'audio de la réponse est joué automatiquement
5. ✅ Les données sont sauvegardées dans la base de données
6. ✅ Les coûts API correspondent à votre budget

---

**Support**: Consultez `VOICE_ASSISTANT_GUIDE.md` et `USE_CASES_AND_ALTERNATIVES.md` pour plus d'aide.
