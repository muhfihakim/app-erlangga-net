export class PTT {
    constructor() {
        this.localStream = null;
        this.peers = {};
        this.activeUsers = [];
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

            let peer = this.peers[data.from];
            if (!peer) peer = this.createPeer(data.from);

            if (data.sdp) {
                await peer.setRemoteDescription(new RTCSessionDescription(data.sdp));
                if (data.sdp.type === 'offer') {
                    const answer = await peer.createAnswer();
                    await peer.setLocalDescription(answer);
                    this.sendSignal(data.from, { sdp: peer.localDescription });
                }
            } else if (data.candidate) {
                await peer.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        });
    }

    createPeer(userId) {
        const peer = new RTCPeerConnection({
            iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
        });

        peer.onicecandidate = (e) => {
            if (e.candidate) this.sendSignal(userId, { candidate: e.candidate });
        };

        peer.ontrack = (e) => {
            console.log('Terima Suara/Track!');
            const audio = new Audio();
            audio.srcObject = e.streams[0];
            audio.play().catch(err => console.error("AutoPlay Error:", err));
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
        const targets = this.activeUsers.filter(id => id !== String(window.userId));
        
        targets.forEach(async (id) => {
            const peer = this.createPeer(id);
            const offer = await peer.createOffer();
            await peer.setLocalDescription(offer);
            this.sendSignal(id, { sdp: peer.localDescription });
            console.log('Kirim Offer ke:', id);
        });
        showToast('PTT: Berbicara...', 'info');
    }

    stopPTT() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(t => t.stop());
            this.localStream = null;
        }
        Object.values(this.peers).forEach(p => p.close());
        this.peers = {};
        showToast('PTT: Berhenti.', 'success');
    }
}

window.PTTManager = new PTT();
