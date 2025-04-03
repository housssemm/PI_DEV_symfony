document.addEventListener("DOMContentLoaded", function() {
    let videoFrame = document.getElementById("video-frame");
    let streaming = false;

    function startCamera() {
        if (!videoFrame) {
            console.error("❌ Erreur : L'élément #video-frame n'existe pas !");
            return;
        }

        if (!streaming) {
            videoFrame.src = "http://127.0.0.1:5000/video_feed";
            streaming = true;
        }
    }

    function stopCamera() {
        if (streaming) {
            videoFrame.src = "";
            streaming = false;
        }
    }

    // Rendre les fonctions accessibles globalement
    window.startCamera = startCamera;
    window.stopCamera = stopCamera;
});
