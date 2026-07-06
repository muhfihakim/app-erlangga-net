export class PTT {
    constructor() {
        this.localStream = null;
        this.peers = {};
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
            console.log('PTT Users:', users);
        }).joining((user) => {
            console.log('PTT Joined:', user);
        }).leaving((user) => {
            console.log('PTT Left:', user);
            if (this.peers[user.id]) {
                this.peers[user.id].close();
                delete this.peers[user.id];
            }
        }).listen('.WebRTCSignaling', async (e) => {
            const data = e.payload;
            if (data.to !== window.userId) return; // window.userId from blade

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
            if (e.candidate) {
                this.sendSignal(userId, { candidate: e.candidate });
            }
        };

        peer.ontrack = (e) => {
            const audio = new Audio();
            audio.srcObject = e.streams[0];
            audio.play();
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
        });
    }

    async startPTT() {
        await this.initAudio();
        // create offer to all users
        const users = Object.keys(this.channel.users || {}).filter(id => id != window.userId);
        users.forEach(async (id) => {
            const peer = this.createPeer(id);
            const offer = await peer.createOffer();
            await peer.setLocalDescription(offer);
            this.sendSignal(id, { sdp: peer.localDescription });
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
