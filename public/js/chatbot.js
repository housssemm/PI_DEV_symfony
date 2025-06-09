document.addEventListener('DOMContentLoaded', () => {
    const $chatbot = document.querySelector('.chatbot');
    const $chatbotHeader = document.querySelector('.chatbot__header');
    const $chatbotMessages = document.querySelector('.chatbot__messages');
    const $chatbotMessageWindow = document.querySelector('.chatbot__message-window');
    const $chatbotInput = document.querySelector('.chatbot__input');
    const $chatbotSubmit = document.querySelector('.chatbot__submit');

    // --- DEBUG : vérifier que les éléments sont bien capturés
    console.log('$chatbot =', $chatbot);
    console.log('$chatbotHeader =', $chatbotHeader);

    // Listener pour ouvrir/fermer le chat
    $chatbotHeader.addEventListener('click', () => {
        console.log('chat header clicked');
        toggle($chatbot, 'chatbot--closed');
    });

    // Listener pour envoyer le message au clic
    $chatbotSubmit.addEventListener('click', () => {
        validateMessage();
    });

    // Envoi au "Enter"
    document.addEventListener('keypress', event => {
        if (event.which === 13) validateMessage();
    });

    // Fonction toggle pour ajouter/retirer la classe
    const toggle = (element, klass) => {
        const classes = element.className.match(/\S+/g) || [];
        const idx = classes.indexOf(klass);
        idx >= 0 ? classes.splice(idx, 1) : classes.push(klass);
        element.className = classes.join(' ');
    };

    const loader = `<span class='loader'><span class='loader__dot'></span><span class='loader__dot'></span><span class='loader__dot'></span></span>`;
    const baseUrl = 'http://127.0.0.1:5000/chat_web';

    // Envoie le texte à Flask et affiche un loader
    const send = (text = '') => {
        const data = { text };
        fetch(baseUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
            .then(r => r.json())
            .then(res => {
                if (res.response) aiMessage(res.response);
                else aiMessage("Sorry, I couldn't get a response from the assistant.");
            })
            .catch(err => {
                aiMessage("There was an error with the chatbot.");
                console.error('Error:', err);
            });

        aiMessage(loader, true);
    };

    // Affiche un message de l'AI
    const aiMessage = (content, isLoading = false, delay = 0) => {
        setTimeout(() => {
            removeLoader();
            $chatbotMessages.innerHTML += `
        <li class='is-ai animation' id='${isLoading ? "is-loading" : ""}'>
          <div class="is-ai__profile-picture">
            <svg class="icon-avatar" viewBox="0 0 32 32">
              <use xlink:href="#avatar" />
            </svg>
          </div>
          <span class='chatbot__arrow chatbot__arrow--left'></span>
          <div class='chatbot__message'>${content}</div>
        </li>`;
            scrollDown();
        }, delay);
    };

    // Affiche un message de l’utilisateur
    const userMessage = content => {
        $chatbotMessages.innerHTML += `
      <li class='is-user animation'>
        <p class='chatbot__message'>${content}</p>
        <span class='chatbot__arrow chatbot__arrow--right'></span>
      </li>`;
    };

    // Validation et envoi du message
    const validateMessage = () => {
        const text = $chatbotInput.value;
        const safeText = text ? escapeScript(text) : '';
        if (safeText.length && safeText !== ' ') {
            resetInputField();
            userMessage(safeText);
            send(safeText);
        }
        scrollDown();
    };

    const resetInputField = () => { $chatbotInput.value = ''; };

    const scrollDown = () => {
        const distance = $chatbotMessageWindow.scrollHeight - ($chatbotMessages.lastChild.offsetHeight + 60);
        $chatbotMessageWindow.scrollTop = distance;
    };

    const removeLoader = () => {
        const loadingElem = document.getElementById('is-loading');
        if (loadingElem) $chatbotMessages.removeChild(loadingElem);
    };

    const escapeScript = unsafe => {
        return unsafe
            .replace(/</g, ' ')
            .replace(/>/g, ' ')
            .replace(/&/g, ' ')
            .replace(/"/g, ' ')
            .replace(/\\/, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    };
});
