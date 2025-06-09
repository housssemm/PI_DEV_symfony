import React, { useState, useEffect } from 'react';
import AgoraUIKit from 'agora-react-uikit';
import AgoraRTM from 'agora-rtm-sdk';
import { ReactMic } from 'react-mic';

const App = () => {
    const [rtcProps, setRtcProps] = useState(null);
    const [users, setUsers] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [messages, setMessages] = useState([]);
    const [isRecording, setIsRecording] = useState(false);

    useEffect(() => {
        // 1) Récupérer token Agora
        fetch('/messagerie/token', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                setRtcProps({
                    appId: data.appId,
                    channel: data.channelName,
                    token: data.token,
                    uid: data.uid
                });
            })
            .catch(console.error);

        // 2) Charger la liste des users
        fetch('/messagerie/users')
            .then(r => r.json())
            .then(setUsers)
            .catch(console.error);
    }, []);

    const sendMessage = () => {
        const input = document.getElementById('message-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;
        setMessages(prev => [...prev, { sender: 'Me', text, type: 'text' }]);
        input.value = '';
    };

    const handleVoiceMessage = (recordedBlob) => {
        setMessages(prev => [...prev, { sender: 'Me', text: recordedBlob.blobURL, type: 'voice' }]);
    };

    return (
        <div className="messaging-container">
            <aside className="user-list">
                <h2>Utilisateurs</h2>
                <ul>
                    {users.map(u => (
                        <li key={u.id} onClick={() => setSelectedUser(u)}>
                            <img src={`/img/${u.image}`} alt={`${u.nom} ${u.prenom}`} />
                            {u.nom} {u.prenom}
                        </li>
                    ))}
                </ul>
            </aside>

            <main className="message-panel">
                <header>
                    {selectedUser
                        ? (
                            <>
                                <img src={`/img/${selectedUser.image}`} className="user-avatar" alt={`${selectedUser.nom} ${selectedUser.prenom}`} />
                                <h2>{selectedUser.nom} {selectedUser.prenom}</h2>
                                <div className="call-buttons">
                                    <button onClick={() => alert('Appel vidéo')}>📹</button>
                                    <button onClick={() => alert('Appel vocal')}>📞</button>
                                </div>
                            </>
                        )
                        : <h2>Sélectionnez un utilisateur</h2>
                    }
                </header>

                <section className="messages">
                    {messages.map((m, i) => (
                        <div key={i} className="message">
                            <strong>{m.sender}:</strong>
                            {m.type === 'voice'
                                ? <audio controls src={m.text} />
                                : <span> {m.text}</span>
                            }
                        </div>
                    ))}
                </section>

                <footer>
                    <textarea id="message-input" placeholder="Écrivez un message..." />
                    <button onClick={sendMessage}>➡️</button>
                    <button onClick={() => setIsRecording(r => !r)}>
                        {isRecording ? '⏹️ Stop' : '🎙️ Enregistrer'}
                    </button>
                    <ReactMic
                        record={isRecording}
                        onStop={handleVoiceMessage}
                        mimeType="audio/webm"
                        style={{ display: 'none' }}
                    />
                </footer>

                {/* Widget vidéo/audio Agora */}
                {rtcProps && <div className="agora-widget">
                    <AgoraUIKit rtcProps={rtcProps} />
                </div>}
            </main>
        </div>
    );
};

export default App;
