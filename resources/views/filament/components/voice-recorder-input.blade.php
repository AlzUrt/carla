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

    function findFieldElement(fieldName) {
        return document.querySelector(
            `[name="${fieldName}"], [name$="[${fieldName}]"], textarea[id$="${fieldName}"], input[id$="${fieldName}"]`
        );
    }

    function setFieldValue(fieldName, value) {
        const field = findFieldElement(fieldName);
        if (!field) {
            return;
        }

        field.value = value ?? '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function showStatus(message, type = 'info') {
        if (!voiceStatus) {
            return;
        }

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
        if (!recordingTimer || !timerCount) {
            return;
        }

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

        if (recordingTimer) {
            recordingTimer.classList.add('hidden');
        }
    }

    startRecordBtn?.addEventListener('click', async () => {
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
            showStatus('Erreur lors du démarrage de l\'enregistrement: ' + error.message, 'error');
        }
    });

    stopRecordBtn?.addEventListener('click', () => {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            return;
        }

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

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch('/api/conversations/process-audio', {
                method: 'POST',
                body: formData,
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Erreur lors du traitement');
            }

            const data = await response.json();

            // Display results
            if (transcribedText) {
                transcribedText.value = data.text_question;
            }
            if (claudeResponse) {
                claudeResponse.value = data.text_answer;
            }

            setFieldValue('text_question', data.text_question);
            setFieldValue('text_answer', data.text_answer);
            setFieldValue('duration_stt_ms', data.durations?.stt_ms);
            setFieldValue('duration_llm_ms', data.durations?.llm_ms);
            setFieldValue('duration_tts_ms', data.durations?.tts_ms);
            setFieldValue('status', 'completed');

            transcriptionSection?.classList.remove('hidden');
            responseSection?.classList.remove('hidden');

            // Load audio response
            if (audioPlayer) {
                audioPlayer.src = data.audio_answer_url;
            }
            audioPlayerSection?.classList.remove('hidden');

            // Display metrics
            const sttTimeEl = document.getElementById('sttTime');
            const llmTimeEl = document.getElementById('llmTime');
            const ttsTimeEl = document.getElementById('ttsTime');
            const totalTimeEl = document.getElementById('totalTime');
            if (sttTimeEl) sttTimeEl.textContent = data.durations?.stt_ms ?? '-';
            if (llmTimeEl) llmTimeEl.textContent = data.durations?.llm_ms ?? '-';
            if (ttsTimeEl) ttsTimeEl.textContent = data.durations?.tts_ms ?? '-';
            if (totalTimeEl) totalTimeEl.textContent = data.durations?.total_ms ?? '-';
            metricsSection?.classList.remove('hidden');

            showStatus('Traitement réussi! ✨', 'success');

            // Play audio automatically
            if (audioPlayer) {
                audioPlayer.play().catch(() => {});
            } else if (data.audio_answer_url) {
                const fallbackAudio = new Audio(data.audio_answer_url);
                fallbackAudio.play().catch(() => {});
            }
        } catch (error) {
            showStatus('Erreur: ' + error.message, 'error');
            console.error('Error:', error);
        }
    }
</script>
