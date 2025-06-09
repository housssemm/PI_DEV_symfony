// public/js/agora-ui.js
const AGORA_APP_ID = '49da528bddc74e3089d232ca596b856e';

let rtmClient = null, rtmChannel = null;
let rtcClient = null, localAudioTrack = null, localVideoTrack = null;

document.addEventListener('DOMContentLoaded', () => {
    // DOM references
    const usersList      = document.getElementById('users');
    const messagesDiv    = document.getElementById('messages');
    const messageInput   = document.getElementById('message-input');
    const sendBtn        = document.getElementById('send-message');
    const recordBtn      = document.getElementById('record-voice-message');
    const videoCallBtn   = document.getElementById('start-video-call');
    const voiceCallBtn   = document.getElementById('start-voice-call');
    const nameEl         = document.getElementById('user-name');
    const avatarEl       = document.getElementById('user-avatar');

    if (![usersList, messagesDiv, messageInput, sendBtn, recordBtn, videoCallBtn, voiceCallBtn, nameEl, avatarEl].every(el=>el)) {
        console.error('Éléments manquants dans le DOM');
        return;
    }

    let selectedUser = null;

    // 1) Charger utilisateurs
    fetch('/messagerie/users')
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(users => {
            usersList.innerHTML = '';
            users.forEach(u => {
                const li = document.createElement('li');
                li.className = 'user';
                li.innerHTML = `
          <img src="/img/${u.image || 'default-avatar.png'}" class="user-avatar" />
          <div class="user-details">
            <span class="user-name">${u.nom}</span>
            <span class="user-fullname">${u.prenom}</span>
          </div>`;
                li.onclick = () => selectUser(u, li);
                usersList.appendChild(li);
            });
        })
        .catch(err => {
            console.error('Erreur chargement users:', err);
            usersList.innerHTML = '<li style="padding:12px;color:#f88;">Impossible de charger la liste</li>';
        });

    // 2) Sélection d’un utilisateur
    async function selectUser(u, liEl) {
        // Highlight
        document.querySelectorAll('.user').forEach(el => el.classList.remove('selected'));
        liEl.classList.add('selected');

        selectedUser = u;
        nameEl.textContent = `${u.nom} ${u.prenom}`;
        avatarEl.src       = `/img/${u.image || 'default-avatar.png'}`;

        // 3) Charger historique
        messagesDiv.innerHTML = '<em>Chargement…</em>';
        try {
            const res = await fetch(`/api/messages/history/${u.id}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const hist = await res.json();
            messagesDiv.innerHTML = '';
            hist.forEach(m => {
                displayMessage(m.sender==='me' ? 'Me' : `${u.nom} ${u.prenom}`, m.message, m.sender==='me');
            });
        } catch (e) {
            console.error('Erreur historique:', e);
            messagesDiv.innerHTML = '<em>Impossible de charger l’historique</em>';
        }

        // 4) Initialiser Agora RTM
        await initRTM();
    }

    // 5) Initialisation RTM
    async function initRTM() {
        try {
            if (rtmChannel) { await rtmChannel.leave(); rtmChannel = null; }
            if (rtmClient)  { await rtmClient.logout(); rtmClient   = null; }

            const resp = await fetch('/messagerie/token', { method:'POST' });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const { token, channelName, uid } = await resp.json();

            rtmClient  = AgoraRTM.createInstance(AGORA_APP_ID);
            await rtmClient.login({ uid: uid.toString(), token });

            rtmChannel = rtmClient.createChannel(channelName);
            await rtmChannel.join();
            rtmChannel.on('ChannelMessage', ({ text }, senderId) => {
                displayMessage(senderId, text, false);
            });
        } catch (e) {
            console.error('Erreur init RTM:', e);
        }
    }

    // 6) Affichage message
    function displayMessage(sender, text, isMe=false) {
        const div = document.createElement('div');
        div.className = 'message' + (isMe ? ' me' : '');
        if (text.startsWith('data:audio/')) {
            const audio = document.createElement('audio');
            audio.controls = true; audio.src = text;
            div.append(`${sender}: `, audio);
        } else {
            div.textContent = `${sender}: ${text}`;
        }
        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // 7) Envoi texte
    sendBtn.onclick = async () => {
        if (!selectedUser) { alert('Sélectionnez un utilisateur'); return; }
        const txt = messageInput.value.trim();
        if (!txt || !rtmChannel) return;
        try {
            await rtmChannel.sendMessage({ text: txt });
            displayMessage('Me', txt, true);
            await fetch('/api/messages', {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body: JSON.stringify({
                    toUserId: selectedUser.id,
                    message: txt,
                    timestamp: new Date().toISOString()
                })
            });
        } catch (e) {
            console.error('Erreur envoi:', e);
            alert('Erreur lors de l’envoi');
        }
        messageInput.value = '';
    };

    // 8) Enregistrement vocal
    let recorder = null, chunks = [];
    recordBtn.onclick = async () => {
        if (!recorder) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
                recorder = new MediaRecorder(stream);
                recorder.ondataavailable = e => chunks.push(e.data);
                recorder.onstop = async () => {
                    const blob = new Blob(chunks, { type:'audio/webm' });
                    chunks=[]; recorder=null; recordBtn.textContent='🎙️';
                    const dataUrl = await new Promise(r => {
                        const fr = new FileReader();
                        fr.onloadend = () => r(fr.result);
                        fr.readAsDataURL(blob);
                    });
                    if (rtmChannel) {
                        await rtmChannel.sendMessage({ text:dataUrl });
                        displayMessage('Me', dataUrl, true);
                    }
                };
                recorder.start(); recordBtn.textContent='⏹️';
            } catch (e) {
                alert('Micro inaccessible: '+e.message);
            }
        } else {
            recorder.stop();
        }
    };

    // 9) Appels audio/vidéo
    async function startCall(video) {
        if (!selectedUser) { alert('Sélectionnez un utilisateur'); return; }
        if (!rtcClient) {
            rtcClient = AgoraRTC.createClient({ mode:'rtc', codec:'vp8' });
            try {
                const resp = await fetch('/messagerie/token',{ method:'POST' });
                const { token, channelName, uid } = await resp.json();
                await rtcClient.join(AGORA_APP_ID, channelName, token, uid);

                localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                await rtcClient.publish(localAudioTrack);

                if (video) {
                    localVideoTrack = await AgoraRTC.createCameraVideoTrack();
                    localVideoTrack.play('app');
                    await rtcClient.publish(localVideoTrack);
                }

                rtcClient.on('user-published', async (user, mediaType) => {
                    await rtcClient.subscribe(user, mediaType);
                    if (mediaType==='audio') user.audioTrack.play();
                    if (mediaType==='video') {
                        const player = document.createElement('div');
                        player.id = `player-${user.uid}`;
                        document.getElementById('app').appendChild(player);
                        user.videoTrack.play(player.id);
                    }
                });
            } catch(e) {
                console.error('Erreur démarrage appel:', e);
            }
        }
    }
    videoCallBtn.onclick = () => startCall(true);
    voiceCallBtn.onclick = () => startCall(false);

});
