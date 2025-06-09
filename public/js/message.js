let currentUserId = null;
let selectedUserId = null;
let mercureToken = null;
let eventSource = null;

// Récupérer le token Mercure
async function getMercureToken() {
    const response = await fetch('/api/mercure-token', {
        method: 'GET',
        credentials: 'include'
    });
    const data = await response.json();
    if (response.ok) {
        mercureToken = data.token;
        return mercureToken;
    } else {
        throw new Error('Échec de la récupération du token Mercure: ' + data.error);
    }
}

// Charger la liste des utilisateurs après connexion
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Récupérer l'ID de l'utilisateur connecté
        const meResponse = await fetch('/api/me', { credentials: 'include' });
        if (!meResponse.ok) throw new Error(`Erreur ${meResponse.status}: ${meResponse.statusText}`);
        const meData = await meResponse.json();
        currentUserId = meData.id;

        // Charger la liste des utilisateurs
        const usersResponse = await fetch('/api/users', {
            headers: { 'Accept': 'application/ld+json' },
            credentials: 'include'
        });
        if (!usersResponse.ok) throw new Error(`Erreur ${usersResponse.status}: ${usersResponse.statusText}`);
        const usersData = await usersResponse.json();
        const list = document.getElementById('user-list');
        if (usersData['hydra:member'] && Array.isArray(usersData['hydra:member'])) {
            usersData['hydra:member'].forEach(user => {
                if (user.id !== currentUserId) {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.innerHTML = `
                        <img src="${user.profileImage}" alt="${user.nom} ${user.prenom}" class="profile-image">
                        ${user.nom} ${user.prenom}
                    `;
                    a.onclick = (e) => {
                        e.preventDefault();
                        startConversation(user.id, `${user.nom} ${user.prenom}`, a);
                    };
                    li.appendChild(a);
                    list.appendChild(li);
                }
            });
        } else {
            list.innerHTML = '<li>Aucun utilisateur disponible</li>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue. Veuillez vous reconnecter. Détails : ' + error.message);
    }
});

// Démarrer une conversation
async function startConversation(userId, userName, linkElement) {
    // Mettre à jour l'état de l'utilisateur sélectionné
    selectedUserId = userId;

    // Mettre en surbrillance l'utilisateur sélectionné
    document.querySelectorAll('.user-list li a').forEach(a => a.classList.remove('active'));
    linkElement.classList.add('active');

    // Afficher la section de conversation
    document.getElementById('conversation').style.display = 'flex';
    document.getElementById('conversation-with').textContent = userName;

    // Charger les messages
    await loadMessages(userId);

    // Fermer l'ancien EventSource si existant
    if (eventSource) {
        eventSource.close();
    }

    // Initialiser Mercure pour les mises à jour en temps réel
    try {
        await getMercureToken();
        eventSource = new EventSource(`/mercure?topic=messages/${userId}`, {
            withCredentials: true,
            headers: { 'Authorization': 'Bearer ' + mercureToken }
        });
        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            const list = document.getElementById('message-list');
            list.innerHTML += `
                <li class="received">
                    ${data.content}
                    <div class="message-meta">${data.sender} - ${data.date}</div>
                </li>
            `;
            list.scrollTop = list.scrollHeight;
        };
        eventSource.onerror = () => console.error('Erreur de connexion à Mercure');
    } catch (error) {
        console.error('Erreur Mercure:', error);
    }
}

// Charger l'historique des messages
async function loadMessages(userId) {
    try {
        const response = await fetch(`/api/messages?utilisateurExpediteur=${currentUserId}&utilisateurDestinataire=${userId}`, {
            headers: { 'Accept': 'application/ld+json' },
            credentials: 'include'
        });
        const text = await response.text(); // Get raw response
        console.log('Messages API response:', text); // Log to inspect
        if (!response.ok) throw new Error(`Erreur ${response.status}: ${response.statusText}`);
        const data = JSON.parse(text);
        const list = document.getElementById('message-list');
        list.innerHTML = '';
        if (data['hydra:member'] && Array.isArray(data['hydra:member'])) {
            data['hydra:member'].forEach(message => {
                const isSent = message.utilisateurExpediteur.id === currentUserId;
                const sender = message.utilisateurExpediteur.nom;
                const content = message.contenu;
                const date = new Date(message.dateEnvoi).toLocaleTimeString();
                list.innerHTML += `
                    <li class="${isSent ? 'sent' : 'received'}">
                        ${content}
                        <div class="message-meta">${sender} - ${date}</div>
                    </li>
                `;
            });
            list.scrollTop = list.scrollHeight;
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Envoyer un message
document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const contenu = e.target.contenu.value;

    if (!currentUserId || !selectedUserId) {
        alert('Veuillez sélectionner un destinataire ou vous reconnecter.');
        return;
    }

    try {
        const response = await fetch('/api/messages/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                contenu: contenu,
                utilisateurExpediteur: `/api/users/${currentUserId}`,
                utilisateurDestinataire: `/api/users/${selectedUserId}`
            })
        });
        if (!response.ok) throw new Error(`Erreur ${response.status}: ${response.statusText}`);
        e.target.contenu.value = '';
        await loadMessages(selectedUserId);
        const list = document.getElementById('message-list');
        list.scrollTop = list.scrollHeight;
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'envoi du message : ' + error.message);
    }
});