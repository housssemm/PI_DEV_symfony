from flask import Flask, Response, jsonify
from flask_cors import CORS
import cv2
import mediapipe as mp
import numpy as np
import time

app = Flask(__name__)
CORS(app)

# Initialisation MediaPipe
mp_pose = mp.solutions.pose
pose = mp_pose.Pose(min_detection_confidence=0.5, min_tracking_confidence=0.5)
mp_drawing = mp.solutions.drawing_utils

# Variables globales
good_count = 0
bad_count = 0
state = "up"  # état initial : debout
squat_angle_threshold = 100  # seuil pour déterminer la position basse

def detect_exercise(landmarks):
    global good_count, bad_count, state

    left_hip = np.array([
        landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].x,
        landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].y
    ])
    left_knee = np.array([
        landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].x,
        landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].y
    ])
    left_ankle = np.array([
        landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].x,
        landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].y
    ])

    vec1 = left_hip - left_knee
    vec2 = left_ankle - left_knee

    dot_product = np.dot(vec1, vec2)
    norm_product = np.linalg.norm(vec1) * np.linalg.norm(vec2)

    if norm_product == 0:
        return "Unknown", (255, 255, 255)

    knee_angle = np.degrees(np.arccos(np.clip(dot_product / norm_product, -1.0, 1.0)))

    # Logique de squat
    if knee_angle < squat_angle_threshold:  # Descente
        if state == "up":
            state = "down"
        return "Down", (0, 165, 255)

    elif knee_angle > squat_angle_threshold + 20:  # Remontée
        if state == "down":
            if knee_angle < 120:
                good_count += 1
                status = "Squat Counted ✅"
                color = (0, 255, 0)
            else:
                bad_count += 1
                status = "Bad Squat ❌"
                color = (0, 0, 255)
            state = "up"
            return status, color
        return "Up", (255, 255, 0)

    return "In Motion", (200, 200, 200)

def generate_frames():
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("❌ Erreur : Impossible d'ouvrir la webcam")
        return

    last_good_time = 0  # Pour afficher "Good Form" pendant un court moment

    try:
        while True:
            success, frame = cap.read()
            if not success:
                print("❌ Erreur : Impossible de lire la webcam")
                break

            frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            results = pose.process(frame_rgb)

            if results.pose_landmarks:
                mp_drawing.draw_landmarks(frame, results.pose_landmarks, mp_pose.POSE_CONNECTIONS)
                status, color = detect_exercise(results.pose_landmarks.landmark)

                if status == "Squat Counted ✅":
                    last_good_time = time.time()

                # Affichage principal en haut
                cv2.putText(frame, status, (50, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2, cv2.LINE_AA)

                # Affichage central temporaire de "Good Form"
                if time.time() - last_good_time < 1.0:
                    cv2.putText(frame, "Good Form ✅", (200, 250), cv2.FONT_HERSHEY_SIMPLEX, 2, (0, 255, 0), 4, cv2.LINE_AA)

                # Calcul et affichage de la barre de progression
                left_hip = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_HIP.value]
                left_knee = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_KNEE.value]
                left_ankle = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_ANKLE.value]

                # Vecteurs
                vec1 = np.array([left_hip.x - left_knee.x, left_hip.y - left_knee.y])
                vec2 = np.array([left_ankle.x - left_knee.x, left_ankle.y - left_knee.y])
                dot_product = np.dot(vec1, vec2)
                norm_product = np.linalg.norm(vec1) * np.linalg.norm(vec2)

                if norm_product != 0:
                    knee_angle = np.degrees(np.arccos(np.clip(dot_product / norm_product, -1.0, 1.0)))

                    # Convertir l’angle en pourcentage (170° = 0%, 60° = 100%)
                    min_angle = 60
                    max_angle = 170
                    percentage = np.clip((max_angle - knee_angle) / (max_angle - min_angle), 0, 1)

                    bar_height = int(300 * percentage)
                    cv2.rectangle(frame, (580, 100), (600, 400), (50, 50, 50), 2)  # Cadre
                    cv2.rectangle(frame, (580, 400 - bar_height), (600, 400), (0, 255, 0), -1)  # Remplissage
                    cv2.putText(frame, f'{int(percentage*100)}%', (560, 90), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255,255,255), 2)

            # Affichage des compteurs
            cv2.putText(frame, f'Good Squats: {good_count}', (50, 100), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)
            cv2.putText(frame, f'Bad Attempts: {bad_count}', (50, 140), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 0, 255), 2)

            _, buffer = cv2.imencode('.jpg', frame)
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')
    finally:
        cap.release()

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/counter')
def get_counter():
    return jsonify({
        'good': good_count,
        'bad': bad_count
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
