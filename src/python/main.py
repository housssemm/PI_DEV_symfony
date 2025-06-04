from flask import Flask, Response, jsonify
from flask_cors import CORS
import cv2
import mediapipe as mp
import numpy as np
import time
import math

app = Flask(__name__)
CORS(app)

# Initialisation MediaPipe
mp_pose = mp.solutions.pose
mp_drawing = mp.solutions.drawing_utils

# Variables globales
counter = 0
stage = None
last_angle = 0

def calculate_angle(a, b, c):
    """
    Calcule l'angle entre trois points.
    """
    a = np.array(a)
    b = np.array(b)
    c = np.array(c)
    
    radians = math.atan2(c[1] - b[1], c[0] - b[0]) - math.atan2(a[1] - b[1], a[0] - b[0])
    angle = np.abs(radians * 180.0 / np.pi)
    
    if angle > 180.0:
        angle = 360 - angle
        
    return angle

def generate_frames():
    """
    Génère les images pour le flux vidéo.
    """
    global counter, stage, last_angle
    
    # Ouvrir la webcam
    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("❌ Erreur : Impossible d'ouvrir la webcam")
        return
    
    # Configurer MediaPipe Pose
    with mp_pose.Pose(min_detection_confidence=0.5, min_tracking_confidence=0.5) as pose:
        while cap.isOpened():
            success, frame = cap.read()
            if not success:
                print("❌ Erreur : Impossible de lire la webcam")
                break
            
            # Redimensionner l'image pour de meilleures performances
            frame = cv2.resize(frame, (640, 480))
            
            # Convertir l'image en RGB pour MediaPipe
            image_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            image_rgb.flags.writeable = False
            
            # Traiter l'image avec MediaPipe
            results = pose.process(image_rgb)
            
            # Reconvertir l'image en BGR pour OpenCV
            image_rgb.flags.writeable = True
            image = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR)
            
            # Dessiner les points de repère
            if results.pose_landmarks:
                mp_drawing.draw_landmarks(
                    image, 
                    results.pose_landmarks, 
                    mp_pose.POSE_CONNECTIONS,
                    mp_drawing.DrawingSpec(color=(245, 117, 66), thickness=2, circle_radius=2),
                    mp_drawing.DrawingSpec(color=(245, 66, 230), thickness=2, circle_radius=2)
                )
                
                # Extraire les points de repère
                try:
                    landmarks = results.pose_landmarks.landmark
                    
                    # Points pour l'exercice de flexion du bras
                    shoulder = [landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value].x,
                               landmarks[mp_pose.PoseLandmark.LEFT_SHOULDER.value].y]
                    elbow = [landmarks[mp_pose.PoseLandmark.LEFT_ELBOW.value].x,
                            landmarks[mp_pose.PoseLandmark.LEFT_ELBOW.value].y]
                    wrist = [landmarks[mp_pose.PoseLandmark.LEFT_WRIST.value].x,
                            landmarks[mp_pose.PoseLandmark.LEFT_WRIST.value].y]
                    
                    # Calculer l'angle
                    angle = calculate_angle(shoulder, elbow, wrist)
                    last_angle = angle
                    
                    # Logique de comptage
                    if angle > 160:
                        stage = "up"
                    elif angle < 50 and stage == 'up':
                        stage = "down"
                        counter += 1
                    
                    # Afficher l'angle
                    cv2.putText(image, f"Angle: {int(angle)}", 
                               (10, 30), 
                               cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2, cv2.LINE_AA)
                    
                    # Afficher le compteur
                    cv2.putText(image, f"Reps: {counter}", 
                               (10, 70), 
                               cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2, cv2.LINE_AA)
                    
                    # Afficher l'état
                    cv2.putText(image, f"Stage: {stage}", 
                               (10, 110), 
                               cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2, cv2.LINE_AA)
                    
                except Exception as e:
                    print(f"Erreur lors de l'extraction des points de repère: {e}")
            
            # Encoder l'image pour le streaming
            ret, buffer = cv2.imencode('.jpg', image)
            frame = buffer.tobytes()
            
            yield (b'--frame\r\nContent-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')
    
    cap.release()

@app.route('/video_feed')
def video_feed():
    """
    Route pour le flux vidéo.
    """
    return Response(generate_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/counter')
def get_counter():
    """
    Route pour obtenir le compteur d'exercices.
    """
    global counter, stage, last_angle
    return jsonify({
        'count': counter,
        'stage': stage,
        'angle': int(last_angle)
    })

@app.route('/reset')
def reset_counter():
    """
    Route pour réinitialiser le compteur.
    """
    global counter, stage
    counter = 0
    stage = None
    return jsonify({'success': True})

if __name__ == "__main__":
    print("Démarrage du serveur Flask sur http://localhost:5000")
    print("Flux vidéo disponible sur http://localhost:5000/video_feed")
    print("Compteur disponible sur http://localhost:5000/counter")
    print("Réinitialisation disponible sur http://localhost:5000/reset")
    print("Appuyez sur Ctrl+C pour quitter")
    app.run(host="0.0.0.0", port=5000, debug=False, threaded=True)

