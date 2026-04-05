<div class="flex gap-3">
    <button 
        type="button"
        id="startRecordBtn"
        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-6 py-3 text-sm font-semibold text-white hover:shadow-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
    >
        <span class="text-lg">🎤</span>
        <span>Enregistrer</span>
    </button>
    
    <button 
        type="button"
        id="stopRecordBtn"
        disabled
        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-slate-300 px-6 py-3 text-sm font-semibold text-white disabled:opacity-40 disabled:cursor-not-allowed hover:shadow-lg transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
    >
        <span class="text-lg">⏹️</span>
        <span>Arrêter</span>
    </button>
</div>

<!-- Status Message -->
<div id="voiceStatus" class="hidden mt-3 rounded-lg px-4 py-2 text-sm font-medium transition-all duration-300">
    En attente...
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
