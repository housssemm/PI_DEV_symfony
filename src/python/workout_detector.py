from flask import Flask, Response
from flask_cors import CORS
import cv2
import mediapipe as mp
import numpy as np

app = Flask(__name__)
CORS(app)

# Ensuite, ton code pour MediaPipe et la vidéo
mp_pose = mp.solutions.pose
pose = mp_pose.Pose(min_detection_confidence=0.5, min_tracking_confidence=0.5)
mp_drawing = mp.solutions.drawing_utils

good_count = 0
bad_count = 0

def detect_exercise(landmarks):
    global good_count, bad_count

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
    vec2 = left_knee - left_ankle

    dot_product = np.dot(vec1, vec2)
    norm_product = np.linalg.norm(vec1) * np.linalg.norm(vec2)

    if norm_product == 0:
        return "Unknown Form", (255, 255, 255)

    knee_angle = np.degrees(np.arccos(np.clip(dot_product / norm_product, -1.0, 1.0)))

    if 80 <= knee_angle <= 100:
        good_count += 1
        return "Good Form", (0, 255, 0)
    else:
        bad_count += 1
        return "Bad Form", (0, 0, 255)

def generate_frames():
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("❌ Erreur : Impossible d'ouvrir la webcam")
        return

    try:
        while True:
            success, frame = cap.read()
            if not success:
                print("❌ Erreur : Impossible de lire la webcam")
                break

            # Conversion pour MediaPipe
            frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            results = pose.process(frame_rgb)

            if results.pose_landmarks:
                # Dessin des landmarks et connexions
                mp_drawing.draw_landmarks(frame, results.pose_landmarks, mp_pose.POSE_CONNECTIONS)
                form, color = detect_exercise(results.pose_landmarks.landmark)
                cv2.putText(frame, form, (50, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2, cv2.LINE_AA)

            # Encodage en JPEG pour le streaming
            _, buffer = cv2.imencode('.jpg', frame)
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')
    finally:
        cap.release()

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == "__main__":
   app.run(host="0.0.0.0", port=5000, debug=True)

