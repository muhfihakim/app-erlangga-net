export class PTT {
    constructor() {
        this.localStream = null;
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.activeUsers = [];
        this.activeUsersInfo = {};
        this.channel = Echo.join('ptt');
        this.setupListeners();
    }

    async initAudio() {
        if (!this.localStream) {
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (e) {
                console.error('PTT: Mic error', e);
                showToast('Gagal akses mikrofon', 'error');
            }
        }
    }

    setupListeners() {
        this.channel.here((users) => {
            this.activeUsers = users.map(u => String(u.id));
            users.forEach(u => this.activeUsersInfo[String(u.id)] = u.name);
        }).joining((user) => {
            if (!this.activeUsers.includes(String(user.id))) {
                this.activeUsers.push(String(user.id));
            }
            this.activeUsersInfo[String(user.id)] = user.name;
        }).leaving((user) => {
            this.activeUsers = this.activeUsers.filter(id => id !== String(user.id));
            delete this.activeUsersInfo[String(user.id)];
        }).listen('.PttAudioSent', (e) => {
            console.log('Terima Pesan Suara dari:', e.senderName);
            
            // Tampilkan UI speaker
            this.showSpeaker(e.senderName);

            // Putar audio
            let audio = document.getElementById('ptt-playback');
            if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'ptt-playback';
                audio.autoplay = true;
                audio.playsInline = true;
                
                // Hide UI saat selesai
                audio.onended = () => {
                    this.hideSpeaker();
                };
                
                document.body.appendChild(audio);
            }
            
            audio.src = e.audioUrl;
            audio.play().catch(err => {
                console.error("AutoPlay Error:", err);
                showToast('Klik layar untuk mainkan suara dari ' + e.senderName, 'warning');
            });
        });
    }

    async startPTT() {
        await this.initAudio();
        if (!this.localStream) {
            console.error("Mic stream is null. Aborting PTT.");
            return;
        }

        this.audioChunks = [];
        try {
            // Gunakan mimeType standar
            let options = { mimeType: 'audio/webm;codecs=opus' };
            if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                options = { mimeType: 'audio/webm' };
            }
            
            this.mediaRecorder = new MediaRecorder(this.localStream, options);
            
            this.mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    this.audioChunks.push(e.data);
                }
            };
            
            this.mediaRecorder.start();
            showToast('PTT: Merekam...', 'info');
        } catch (err) {
            console.error("MediaRecorder start error:", err);
            showToast('Gagal mulai rekam', 'error');
        }
    }

    stopPTT() {
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            this.mediaRecorder.onstop = () => {
                this.uploadAudio();
            };
            this.mediaRecorder.stop();
        }
        showToast('PTT: Mengirim...', 'info');
    }

    uploadAudio() {
        if (this.audioChunks.length === 0) return;
        
        const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
        const formData = new FormData();
        formData.append('audio', audioBlob, 'ptt_voice.webm');
        
        axios.post('/api/ptt/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then(response => {
            console.log("Audio terkirim:", response.data);
            showToast('PTT terkirim.', 'success');
        }).catch(err => {
            console.error("PTT Upload Error:", err);
            showToast('Gagal kirim PTT', 'error');
        });
        
        this.audioChunks = [];
    }

    showSpeaker(name) {
        const info = document.getElementById('ptt-speaker-info');
        const nameEl = document.getElementById('ptt-speaker-name');
        if (info && nameEl) {
            nameEl.textContent = name || 'Seseorang';
            info.classList.remove('hidden');
        }
    }

    hideSpeaker() {
        const info = document.getElementById('ptt-speaker-info');
        if (info) {
            info.classList.add('hidden');
        }
    }
}

window.PTTManager = new PTT();
