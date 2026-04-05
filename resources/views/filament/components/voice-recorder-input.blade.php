<div class="space-y-4">
    <div class="rounded-lg border border-gray-300 bg-white p-4">
        <h3 class="mb-4 font-semibold text-gray-700">Enregistrement Vocal</h3>
        
        <div class="space-y-3">
            <!-- Status Display -->
            <div id="voiceStatus" class="hidden rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                En attente...
            </div>
            
            <!-- Recording Controls -->
            <div class="flex flex-wrap gap-2">
                <button 
                    type="button"
                    id="startRecordBtn"
                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    🎤 Démarrer l'enregistrement
                </button>
                
                <button 
                    type="button"
                    id="stopRecordBtn"
                    disabled
                    class="inline-flex items-center justify-center rounded-lg bg-gray-400 px-4 py-2 text-sm font-medium text-white hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    ⏹️ Arrêter
                </button>
            </div>
            
            <!-- Timer -->
            <div id="recordingTimer" class="hidden text-sm font-medium text-red-600">
                Durée: <span id="timerCount">00:00</span>
            </div>
            
            <!-- Transcription Display -->
            <div id="transcriptionSection" class="hidden space-y-2">
                <label class="text-sm font-medium text-gray-700">Texte transcrit:</label>
                <textarea 
                    id="transcribedText"
                    readonly
                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    rows="3"
                ></textarea>
            </div>
            
            <!-- Response Display -->
            <div id="responseSection" class="hidden space-y-2">
                <label class="text-sm font-medium text-gray-700">Réponse Claude:</label>
                <textarea 
                    id="claudeResponse"
                    readonly
                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                    rows="3"
                ></textarea>
            </div>
            
            <!-- Audio Player -->
            <div id="audioPlayerSection" class="hidden space-y-2">
                <label class="text-sm font-medium text-gray-700">Réponse Audio:</label>
                <div class="rounded-lg border border-gray-300 bg-gray-50 p-3">
                    <audio 
                        id="audioPlayer"
                        controls
                        class="w-full"
                    ></audio>
                </div>
            </div>
            
            <!-- Processing Metrics -->
            <div id="metricsSection" class="hidden rounded-lg bg-gray-50 p-3">
                <p class="mb-2 text-sm font-medium text-gray-700">Temps de traitement:</p>
                <ul class="space-y-1 text-sm text-gray-600">
                    <li>🎤 Transcription: <span id="sttTime">-</span> ms</li>
                    <li>🤖 Claude: <span id="llmTime">-</span> ms</li>
                    <li>🔊 TTS: <span id="ttsTime">-</span> ms</li>
                    <li class="font-medium">⏱️ Total: <span id="totalTime">-</span> ms</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    let mediaRecorder;
    let audioChunks = [];
    let recordingStartTime;
    let timerInterval;
    const preferredMimeTypes = [
        'audio/webm;codecs=opus',
        'audio/webm',
        'audio/mp4',
        'audio/ogg',
    ];
    let selectedMimeType = 'audio/webm';

    const startRecordBtn = document.getElementById('startRecordBtn');
    const stopRecordBtn = document.getElementById('stopRecordBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    const recordingTimer = document.getElementById('recordingTimer');
    const timerCount = document.getElementById('timerCount');
    const transcriptionSection = document.getElementById('transcriptionSection');
    const responseSection = document.getElementById('responseSection');
    const audioPlayerSection = document.getElementById('audioPlayerSection');
    const metricsSection = document.getElementById('metricsSection');
    const transcribedText = document.getElementById('transcribedText');
    const claudeResponse = document.getElementById('claudeResponse');
    const audioPlayer = document.getElementById('audioPlayer');

    function showStatus(message, type = 'info') {
        voiceStatus.textContent = message;
        voiceStatus.className = 'rounded-lg p-3 text-sm hidden';
        if (type === 'info') {
            voiceStatus.className = 'rounded-lg p-3 text-sm bg-blue-50 text-blue-700';
        } else if (type === 'success') {
            voiceStatus.className = 'rounded-lg p-3 text-sm bg-green-50 text-green-700';
        } else if (type === 'error') {
            voiceStatus.className = 'rounded-lg p-3 text-sm bg-red-50 text-red-700';
        }
        voiceStatus.classList.remove('hidden');
    }

    function startTimer() {
        recordingStartTime = Date.now();
        recordingTimer.classList.remove('hidden');
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            timerCount.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 100);
    }

    function stopTimer() {
        clearInterval(timerInterval);
        recordingTimer.classList.add('hidden');
    }

    startRecordBtn.addEventListener('click', async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            selectedMimeType = preferredMimeTypes.find((mimeType) => MediaRecorder.isTypeSupported(mimeType)) || '';
            mediaRecorder = selectedMimeType
                ? new MediaRecorder(stream, { mimeType: selectedMimeType })
                : new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = (event) => {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || selectedMimeType || 'audio/webm' });
                await sendAudio(audioBlob);
                stream.getTracks().forEach(track => track.stop());
            };

            mediaRecorder.start();
            startRecordBtn.disabled = true;
            stopRecordBtn.disabled = false;
            showStatus('Enregistrement en cours...', 'info');
            startTimer();
        } catch (error) {
            showStatus('Erreur d\'accès au microphone: ' + error.message, 'error');
        }
    });

    stopRecordBtn.addEventListener('click', () => {
        mediaRecorder.stop();
        stopRecordBtn.disabled = true;
        startRecordBtn.disabled = false;
        stopTimer();
        showStatus('Traitement de l\'audio en cours...', 'info');
    });

    async function sendAudio(audioBlob) {
        try {
            const formData = new FormData();
            const extension = audioBlob.type.includes('mp4') ? 'mp4' : audioBlob.type.includes('ogg') ? 'ogg' : 'webm';
            formData.append('audio', audioBlob, `recording.${extension}`);

            // Get conversation ID if available from the page
            const conversationIdEl = document.querySelector('[data-conversation-id]');
            if (conversationIdEl) {
                formData.append('conversation_id', conversationIdEl.dataset.conversationId);
            }

            const response = await fetch('/api/conversations/process-audio', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Erreur lors du traitement');
            }

            const data = await response.json();

            // Display results
            transcribedText.value = data.text_question;
            claudeResponse.value = data.text_answer;
            transcriptionSection.classList.remove('hidden');
            responseSection.classList.remove('hidden');

            // Load audio response
            audioPlayer.src = data.audio_answer_url;
            audioPlayerSection.classList.remove('hidden');

            // Display metrics
            document.getElementById('sttTime').textContent = data.durations.stt_ms;
            document.getElementById('llmTime').textContent = data.durations.llm_ms;
            document.getElementById('ttsTime').textContent = data.durations.tts_ms;
            document.getElementById('totalTime').textContent = data.durations.total_ms;
            metricsSection.classList.remove('hidden');

            showStatus('Traitement réussi! ✨', 'success');

            // Play audio automatically
            audioPlayer.play();
        } catch (error) {
            showStatus('Erreur: ' + error.message, 'error');
            console.error('Error:', error);
        }
    }
</script>
