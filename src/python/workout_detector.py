from flask import Flask, Response, jsonify
from flask_cors import CORS
import cv2
import mediapipe as mp
import numpy as np
import time
from filterpy.kalman import KalmanFilter

app = Flask(__name__)
CORS(app)

# Initialisation MediaPipe avec des seuils de confiance plus élevés
mp_pose = mp.solutions.pose
pose = mp_pose.Pose(min_detection_confidence=0.7, min_tracking_confidence=0.7)
mp_drawing = mp.solutions.drawing_utils

# Variables globales
good_count = 0
bad_count = 0
state = "up"  # état initial : debout
squat_angle_threshold = 105  # Seuil pour un squat correct (légèrement assoupli)
transition_angle_threshold = 130  # Seuil pour passer de "up" à "down"
symmetry_tolerance = 10
min_knee_angle_left = 180
min_knee_angle_right = 180
min_depth_duration = 0.15  # Durée minimale réduite (légèrement assouplie)
max_transition_time = 1.5
min_transition_time = 0.1
last_down_time = 0
last_state_change_time = 0
last_error_message = ""
debug_info = {}

# Initialisation des filtres de Kalman pour les angles des genoux
kf_left = KalmanFilter(dim_x=2, dim_z=1)
kf_left.x = np.array([180.0, 0.0])
kf_left.F = np.array([[1, 1], [0, 1]])
kf_left.H = np.array([[1, 0]])
kf_left.P *= 1000.
kf_left.R = 0.5
kf_left.Q = np.array([[0.005, 0.0], [0.0, 0.005]])

kf_right = KalmanFilter(dim_x=2, dim_z=1)
kf_right.x = np.array([180.0, 0.0])
kf_right.F = np.array([[1, 1], [0, 1]])
kf_right.H = np.array([[1, 0]])
kf_right.P *= 1000.
kf_right.R = 0.5
kf_right.Q = np.array([[0.005, 0.0], [0.0, 0.005]])

def calculate_angle(a, b, c):
    vec1 = a - b
    vec2 = c - b
    dot_product = np.dot(vec1, vec2)
    norm_product = np.linalg.norm(vec1) * np.linalg.norm(vec2)
    if norm_product == 0:
        return 180
    return np.degrees(np.arccos(np.clip(dot_product / norm_product, -1.0, 1.0)))

def check_back_alignment(landmarks):
    left_shoulder = np.array([landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value].x, landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value].y])
    left_hip = np.array([landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].x, landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].y])
    left_knee = np.array([landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].x, landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].y])
    back_angle = calculate_angle(left_shoulder, left_hip, left_knee)
    debug_info['back_angle'] = back_angle
    return back_angle > 140

def check_feet_position(landmarks):
    left_ankle = np.array([landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].x, landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].y])
    right_ankle = np.array([landmarks[mp_pose.PoseLandmark.RIGHT_ANKLE.value].x, landmarks[mp_pose.PoseLandmark.RIGHT_ANKLE.value].y])
    feet_distance = np.linalg.norm(left_ankle - right_ankle)
    debug_info['feet_distance'] = feet_distance
    return 0.15 < feet_distance < 0.6

def check_hip_depth(landmarks):
    left_hip_y = landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].y
    right_hip_y = landmarks[mp_pose.PoseLandmark.RIGHT_HIP.value].y
    left_knee_y = landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].y
    right_knee_y = landmarks[mp_pose.PoseLandmark.RIGHT_KNEE.value].y
    avg_hip_y = (left_hip_y + right_hip_y) / 2
    avg_knee_y = (left_knee_y + right_knee_y) / 2
    debug_info['hip_depth'] = avg_hip_y - avg_knee_y
    return avg_hip_y > avg_knee_y - 0.15

def check_landmark_confidence(landmarks):
    required_landmarks = [
        mp_pose.PoseLandmark.LEFT_HIP,
        mp_pose.PoseLandmark.LEFT_KNEE,
        mp_pose.PoseLandmark.LEFT_ANKLE,
        mp_pose.PoseLandmark.RIGHT_HIP,
        mp_pose.PoseLandmark.RIGHT_KNEE,
        mp_pose.PoseLandmark.RIGHT_ANKLE,
        mp_pose.PoseLandmark.LEFT_SHOULDER,
        mp_pose.PoseLandmark.RIGHT_SHOULDER
    ]
    for landmark in required_landmarks:
        if landmarks[landmark.value].visibility < 0.7:
            return False
    return True

def detect_exercise(landmarks):
    global good_count, bad_count, state, min_knee_angle_left, min_knee_angle_right, last_down_time, last_state_change_time, last_error_message, debug_info

    if not check_landmark_confidence(landmarks):
        return "Low Confidence", (128, 128, 128)

    # Points de repère pour les genoux
    left_hip = np.array([landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].x, landmarks[mp_pose.PoseLandmark.LEFT_HIP.value].y])
    left_knee = np.array([landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].x, landmarks[mp_pose.PoseLandmark.LEFT_KNEE.value].y])
    left_ankle = np.array([landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].x, landmarks[mp_pose.PoseLandmark.LEFT_ANKLE.value].y])
    right_hip = np.array([landmarks[mp_pose.PoseLandmark.RIGHT_HIP.value].x, landmarks[mp_pose.PoseLandmark.RIGHT_HIP.value].y])
    right_knee = np.array([landmarks[mp_pose.PoseLandmark.RIGHT_KNEE.value].x, landmarks[mp_pose.PoseLandmark.RIGHT_KNEE.value].y])
    right_ankle = np.array([landmarks[mp_pose.PoseLandmark.RIGHT_ANKLE.value].x, landmarks[mp_pose.PoseLandmark.RIGHT_ANKLE.value].y])

    # Calcul des angles des genoux
    knee_angle_left = calculate_angle(left_hip, left_knee, left_ankle)
    knee_angle_right = calculate_angle(right_hip, right_knee, right_ankle)

    # Lissage avec les filtres de Kalman
    kf_left.predict()
    kf_left.update(knee_angle_left)
    smoothed_angle_left = kf_left.x[0]

    kf_right.predict()
    kf_right.update(knee_angle_right)
    smoothed_angle_right = kf_right.x[0]

    # Utiliser les angles bruts pour suivre les minimums
    if state == "down":
        min_knee_angle_left = min(min_knee_angle_left, knee_angle_left)
        min_knee_angle_right = min(min_knee_angle_right, knee_angle_right)

    # Logique de squat
    current_time = time.time()
    if (smoothed_angle_left < transition_angle_threshold and
        smoothed_angle_right < transition_angle_threshold) or check_hip_depth(landmarks):  # Descente
        if state == "up":
            state = "down"
            min_knee_angle_left = knee_angle_left
            min_knee_angle_right = knee_angle_right
            last_down_time = current_time
            last_state_change_time = current_time
        return "Down", (0, 165, 255)

    elif smoothed_angle_left > squat_angle_threshold + 20 and smoothed_angle_right > squat_angle_threshold + 20:  # Remontée
        if state == "down":
            duration = current_time - last_down_time
            symmetry_diff = abs(min_knee_angle_left - min_knee_angle_right)
            back_aligned = check_back_alignment(landmarks)
            feet_position_ok = check_feet_position(landmarks)
            transition_time = current_time - last_state_change_time

            # Stocker les infos de débogage
            debug_info['duration'] = duration
            debug_info['symmetry_diff'] = symmetry_diff
            debug_info['transition_time'] = transition_time

            # Vérifier la vitesse de transition
            if transition_time < min_transition_time:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Too Fast) ❌"
                return last_error_message, (0, 0, 255)
            if transition_time > max_transition_time:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Too Slow) ❌"
                return last_error_message, (0, 0, 255)

            # Critères pour un squat correct
            if min_knee_angle_left >= squat_angle_threshold or min_knee_angle_right >= squat_angle_threshold:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Not Deep Enough) ❌"
                return last_error_message, (0, 0, 255)
            if symmetry_diff >= symmetry_tolerance:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Asymmetric) ❌"
                return last_error_message, (0, 0, 255)
            if duration < min_depth_duration:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Too Short in Low Position) ❌"
                return last_error_message, (0, 0, 255)
            if not back_aligned:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Back Not Aligned) ❌"
                return last_error_message, (0, 0, 255)
            if not feet_position_ok:
                bad_count += 1
                state = "up"
                min_knee_angle_left = 180
                min_knee_angle_right = 180
                last_error_message = "Bad Squat (Feet Position Incorrect) ❌"
                return last_error_message, (0, 0, 255)

            # Si tous les critères sont satisfaits
            good_count += 1
            state = "up"
            min_knee_angle_left = 180
            min_knee_angle_right = 180
            last_state_change_time = current_time
            last_error_message = ""
            return "Squat Counted ✅", (0, 255, 0)

        return "Up", (255, 255, 0)

    return "In Motion", (200, 200, 200)

def generate_frames():
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("❌ Erreur : Impossible d'ouvrir la webcam")
        return

    last_good_time = 0

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

                # Afficher le statut principal
                cv2.putText(frame, status, (50, 50), cv2.FONT_HERSHEY_SIMPLEX, 1, color, 2, cv2.LINE_AA)

                # Afficher "Good Form" ou le message d'erreur
                if time.time() - last_good_time < 1.0:
                    cv2.putText(frame, "Good Form ✅", (200, 250), cv2.FONT_HERSHEY_SIMPLEX, 2, (0, 255, 0), 4, cv2.LINE_AA)
                elif last_error_message:
                    cv2.putText(frame, last_error_message, (50, 180), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2, cv2.LINE_AA)

                # Calcul des angles pour la barre de progression (pas d'affichage)
                left_hip = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_HIP.value]
                left_knee = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_KNEE.value]
                left_ankle = results.pose_landmarks.landmark[mp_pose.PoseLandmark.LEFT_ANKLE.value]

                # Barre de progression pour le genou gauche
                vec1 = np.array([left_hip.x - left_knee.x, left_hip.y - left_knee.y])
                vec2 = np.array([left_ankle.x - left_knee.x, left_ankle.y - left_knee.y])
                dot_product = np.dot(vec1, vec2)
                norm_product = np.linalg.norm(vec1) * np.linalg.norm(vec2)

                if norm_product != 0:
                    knee_angle = np.degrees(np.arccos(np.clip(dot_product / norm_product, -1.0, 1.0)))
                    min_angle = 60
                    max_angle = 170
                    percentage = np.clip((max_angle - knee_angle) / (max_angle - min_angle), 0, 1)
                    bar_height = int(300 * percentage)
                    cv2.rectangle(frame, (580, 100), (600, 400), (50, 50, 50), 2)
                    cv2.rectangle(frame, (580, 400 - bar_height), (600, 400), (0, 255, 0), -1)
                    cv2.putText(frame, f'{int(percentage*100)}%', (560, 90), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255,255,255), 2)

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