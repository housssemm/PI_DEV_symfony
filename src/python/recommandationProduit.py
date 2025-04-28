from flask import Flask, jsonify
import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import os

# 1) Calcul du chemin vers public/img du projet Symfony
here         = os.path.abspath(__file__)
src_dir      = os.path.dirname(here)
project_root = os.path.dirname(os.path.dirname(src_dir))
img_dir      = os.path.join(project_root, 'public', 'img')

app = Flask(__name__)

def get_recommendations(product_id):
    # Connexion à la base de données
    conn = mysql.connector.connect(
        host="127.0.0.1", user="root", password="", database="pi_dev"
    )
    cursor = conn.cursor(dictionary=True)
    # On récupère maintenant la colonne image en plus
    cursor.execute("SELECT id, nom, prix, description, etat, image FROM produit")
    produits = cursor.fetchall()
    conn.close()

    ids          = [p['id'] for p in produits]
    descriptions = [p['description'] for p in produits]
    if product_id not in ids:
        return []

    # TF-IDF
    tfidf     = TfidfVectorizer()
    tfidf_mat = tfidf.fit_transform(descriptions)
    idx       = ids.index(product_id)
    cos_sim   = cosine_similarity(tfidf_mat[idx], tfidf_mat).flatten()
    sim_idxs  = cos_sim.argsort()[-6:-1][::-1]

    recs = []
    for i in sim_idxs:
        pid      = ids[i]
        row      = produits[i]
        # Récupère l'image stockée en base ou fallback
        filename = row['image'] or 'default.jpg'

        recs.append({
            "id"        : pid,
            "nom"       : row["nom"],
            "prix"      : row["prix"],
            "etat"      : row["etat"],
            "similarity": float(cos_sim[i]),
            "image"     : filename
        })
    return recs

@app.route('/recommender/<int:product_id>', methods=['GET'])
def recommend_products(product_id):
    recs = get_recommendations(product_id)
    if not recs:
        return jsonify({"message": "Aucune recommandation trouvée"}), 404
    return jsonify(recs)

if __name__ == '__main__':
    app.run(debug=True)
