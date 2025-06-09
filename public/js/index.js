import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.js';  // Remarquez l’extension
import '../css/messagerie.css'; // Si vous voulez CSS côté JS

const container = document.getElementById('app');
if (!container) {
    console.error("#app introuvable !");
} else {
    const root = createRoot(container);
    root.render(<App />);
}
