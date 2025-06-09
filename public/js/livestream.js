import AgoraRTC from "agora-rtc-sdk-ng";

// Variables globales (à passer depuis le template Twig)
let appId = "{{ appId }}"; // Remplacez par la valeur du template
let channel = "{{ channelName }}"; // Remplacez par la valeur du template
let token = "{{ token }}"; // Remplacez par la valeur du template
let uid = "{{ app.userId || Math.floor(Math.random() * 10000) }}"; // Utilise l'UID du serveur ou un fallback

// RTC client instance
let client = null;
let localAudioTrack = null;
let localVideoTrack = null;

// DOM elements
const joinBtn = document.getElementById("join-btn");
const joinAudienceBtn = document.getElementById("join-audience-btn");
const leaveBtn = document.getElementById("leave-btn");
const localPlayer = document.getElementById("local-player");
const remotePlayerList = document.getElementById("remote-playerlist");

// Initialize the AgoraRTC client
function initializeClient(role = "host") {
    client = AgoraRTC.createClient({ mode: "live", codec: "h264" });
    setupEventListeners();
    return client;
}

// Set up event listeners for remote streams
function setupEventListeners() {
    client.on("user-published", async (user, mediaType) => {
        try {
            await client.subscribe(user, mediaType);
            if (mediaType === "video") {
                const remotePlayerContainer = document.createElement("div");
                remotePlayerContainer.id = `remote-${user.uid}`;
                remotePlayerContainer.textContent = `Utilisateur distant ${user.uid}`;
                remotePlayerContainer.style.width = "640px";
                remotePlayerContainer.style.height = "480px";
                remotePlayerList.appendChild(remotePlayerContainer);
                await user.videoTrack.play(remotePlayerContainer);
            }
            if (mediaType === "audio") {
                await user.audioTrack.play();
            }
        } catch (error) {
            console.error("Erreur lors de la gestion du flux distant:", error);
        }
    });

    client.on("user-unpublished", (user) => {
        const remotePlayerContainer = document.getElementById(`remote-${user.uid}`);
        if (remotePlayerContainer) {
            remotePlayerContainer.remove();
        }
    });

    client.on("error", (err) => {
        console.error("Erreur client Agora:", err);
    });
}

// Create and publish local media tracks (for hosts)
async function createLocalMediaTracks() {
    [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
}

async function publishLocalTracks() {
    if (localAudioTrack && localVideoTrack) {
        await client.publish([localAudioTrack, localVideoTrack]);
    }
}

// Display local video (for hosts)
function displayLocalVideo() {
    if (localVideoTrack) {
        localPlayer.style.display = "block";
        localPlayer.textContent = `Utilisateur local ${uid}`;
        localVideoTrack.play(localPlayer);
    }
}

// Join as a host
async function joinAsHost() {
    try {
        client = initializeClient("host");
        await client.join(appId, channel, token, uid);
        await client.setClientRole("host");
        await createLocalMediaTracks();
        await publishLocalTracks();
        displayLocalVideo();
        toggleButtons(true);
    } catch (error) {
        console.error("Erreur lors du join en tant que hôte:", error);
        alert("Erreur de connexion. Détails : " + error.message);
    }
}

// Join as an audience member
async function joinAsAudience() {
    try {
        client = initializeClient("audience");
        await client.join(appId, channel, token, uid);
        await client.setClientRole("audience"); // Supprimez { level: 2 }, non standard
        toggleButtons(true);
    } catch (error) {
        console.error("Erreur lors du join en tant que spectateur:", error);
        alert("Erreur de connexion. Détails : " + error.message);
    }
}

// Leave the channel
async function leaveChannel() {
    try {
        if (localAudioTrack) {
            localAudioTrack.close();
            localAudioTrack = null;
        }
        if (localVideoTrack) {
            localVideoTrack.close();
            localVideoTrack = null;
        }
        await client.leave();
        localPlayer.style.display = "none";
        remotePlayerList.innerHTML = "";
        toggleButtons(false);
    } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
    }
}

// Toggle button states
function toggleButtons(isJoined) {
    joinBtn.disabled = isJoined;
    joinAudienceBtn.disabled = isJoined;
    leaveBtn.disabled = !isJoined;
}

// Add event listeners to buttons
joinBtn.addEventListener("click", joinAsHost);
joinAudienceBtn.addEventListener("click", joinAsAudience);
leaveBtn.addEventListener("click", leaveChannel);