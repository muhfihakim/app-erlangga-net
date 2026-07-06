import { ForegroundService } from '@capawesome-team/capacitor-android-foreground-service';

export class PTT {
    constructor() {
        this.localStream = null;
        this.peers = {};
        this.iceQueue = {};
        this.activeUsers = [];
        this.channel = Echo.join('ptt');
        this.setupListeners();
        
        if (window.Capacitor && window.Capacitor.getPlatform() === 'android') {
            ForegroundService.start({
                id: 1,
                title: 'PTT Aktif',
                body: 'Siap menerima suara PTT di latar belakang',
            }).catch(e => console.error("ForegroundService error:", e));
        }
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
        }).joining((user) => {
            this.activeUsers.push(String(user.id));
        }).leaving((user) => {
            this.activeUsers = this.activeUsers.filter(id => id !== String(user.id));
            if (this.peers[user.id]) {
                this.peers[user.id].close();
                delete this.peers[user.id];
            }
        }).listen('.WebRTCSignaling', async (e) => {
            console.log('Terima Signal:', e);
            const data = e.payload;
            if (String(data.to) !== String(window.userId)) return; 

            if (data.action === 'stop') {
                if (this.peers[data.from]) {
                    this.peers[data.from].close();
                    delete this.peers[data.from];
                }
                showToast('PTT selesai.', 'success');
                return;
            }

            let peer = this.peers[data.from];
            if (!peer) {
                peer = this.createPeer(data.from);
                this.iceQueue[data.from] = [];
            }

            if (data.sdp) {
                if (data.sdp.type === 'offer') {
                    showToast('Mendengarkan PTT...', 'info');
                }
                try {
                    const decodedSdp = atob(data.sdp.sdp);
                    await peer.setRemoteDescription(new RTCSessionDescription({ type: data.sdp.type, sdp: decodedSdp }));
                    
                    // Process queued candidates
                    if (this.iceQueue[data.from]) {
                        for (let c of this.iceQueue[data.from]) {
                            const decodedCandidate = { ...c, candidate: atob(c.candidate) };
                            await peer.addIceCandidate(new RTCIceCandidate(decodedCandidate)).catch(e => console.error("ICE Queue Error:", e));
                        }
                        this.iceQueue[data.from] = [];
                    }

                    if (data.sdp.type === 'offer') {
                        const answer = await peer.createAnswer();
                        await peer.setLocalDescription(answer);
                        this.sendSignal(data.from, { sdp: { type: peer.localDescription.type, sdp: btoa(peer.localDescription.sdp) } });
                    }
                } catch (e) {
                    console.error("SDP Error:", e);
                }
            } else if (data.candidate) {
                if (peer.remoteDescription) {
                    const decodedCandidate = { ...data.candidate, candidate: atob(data.candidate.candidate) };
                    await peer.addIceCandidate(new RTCIceCandidate(decodedCandidate)).catch(e => console.error("ICE Error:", e));
                } else {
                    this.iceQueue[data.from].push(data.candidate);
                }
            }
        });
    }

    createPeer(userId) {
        const peer = new RTCPeerConnection({
            iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
        });

        peer.onicecandidate = (e) => {
            if (e.candidate) {
                const encodedCandidate = { ...e.candidate.toJSON(), candidate: btoa(e.candidate.candidate) };
                this.sendSignal(userId, { candidate: encodedCandidate });
            }
        };

        peer.ontrack = (e) => {
            console.log('Terima Suara/Track!');
            let audio = document.getElementById('ptt-audio-' + userId);
            if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'ptt-audio-' + userId;
                audio.autoplay = true;
                audio.playsInline = true;
                document.body.appendChild(audio);
            }
            audio.srcObject = e.streams[0];
            audio.play().catch(err => {
                console.error("AutoPlay Error:", err);
                showToast('HP memblokir suara otomatis', 'error');
            });
        };

        if (this.localStream) {
            this.localStream.getTracks().forEach(track => peer.addTrack(track, this.localStream));
        }

        this.peers[userId] = peer;
        return peer;
    }

    sendSignal(to, payload) {
        axios.post('/api/ptt/signal', {
            to,
            from: window.userId,
            ...payload
        }).catch(err => {
            console.error("Signal API Error:", err);
            showToast('Gagal kirim sinyal', 'error');
        });
    }

    async startPTT() {
        await this.initAudio();
        if (!this.localStream) {
            console.error("Mic stream is null. Aborting PTT.");
            return;
        }
        
        const targets = this.activeUsers.filter(id => id !== String(window.userId));
        
        targets.forEach(async (id) => {
            const peer = this.createPeer(id);
            const offer = await peer.createOffer({ offerToReceiveAudio: true });
            await peer.setLocalDescription(offer);
            this.sendSignal(id, { sdp: { type: peer.localDescription.type, sdp: btoa(peer.localDescription.sdp) } });
            console.log('Kirim Offer ke:', id);
        });
        showToast('PTT: Berbicara...', 'info');
    }

    stopPTT() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(t => t.stop());
            this.localStream = null;
        }
        
        // Notify others to stop
        const targets = this.activeUsers.filter(id => id !== String(window.userId));
        targets.forEach(id => this.sendSignal(id, { action: 'stop' }));

        Object.values(this.peers).forEach(p => p.close());
        this.peers = {};
        
        // Remove all audio elements
        document.querySelectorAll('audio[id^="ptt-audio-"]').forEach(el => el.remove());
        
        showToast('PTT: Berhenti.', 'success');
    }
}

window.PTTManager = new PTT();
