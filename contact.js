document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Récupération des données du formulaire
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            const telephone = document.getElementById('telephone').value.trim();
            const message = document.getElementById('message').value.trim();
            
            // Validation côté client
            if (!nom || !email || !telephone || !message) {
                displayMessage('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            // Validation email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                displayMessage('Veuillez entrer une adresse email valide', 'error');
                return;
            }
            
            // Validation du téléphone (numéro tunisien)
            const telephoneRegex = /^(2|5|9)\d{7}$/;
            if (!telephoneRegex.test(telephone)) {
                displayMessage('Veuillez entrer un numéro de téléphone tunisien valide', 'error');
                return;
            }
            
            // Création des données à envoyer
            const formData = new FormData();
            formData.append('nom', nom);
            formData.append('email', email);
            formData.append('telephone', telephone);
            formData.append('message', message);
            
            // Envoi des données au serveur
            fetch('traiter_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessage(data.message, 'success');
                    contactForm.reset();
                } else {
                    displayMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                displayMessage('Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.', 'error');
            });
        });
    }

    // Fonction pour afficher des messages expressifs
    function displayMessage(message, type) {
        // Créer un conteneur de message s'il n'existe pas
        let messageContainer = document.getElementById('message-container');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'message-container';
            messageContainer.style.position = 'fixed';
            messageContainer.style.top = '20px';
            messageContainer.style.left = '50%';
            messageContainer.style.transform = 'translateX(-50%)';
            messageContainer.style.zIndex = '1000';
            document.body.appendChild(messageContainer);
        }

        // Créer l'élément de message
        const messageElement = document.createElement('div');
        messageElement.textContent = message;
        messageElement.style.padding = '15px';
        messageElement.style.borderRadius = '8px';
        messageElement.style.marginBottom = '10px';
        messageElement.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        messageElement.style.opacity = '0';
        messageElement.style.transition = 'opacity 0.5s';

        // Style conditionnel selon le type de message
        if (type === 'success') {
            messageElement.style.backgroundColor = '#4CAF50';
            messageElement.style.color = 'white';
        } else if (type === 'error') {
            messageElement.style.backgroundColor = '#F44336';
            messageElement.style.color = 'white';
        }

        // Ajouter et animer le message
        messageContainer.appendChild(messageElement);
        
        // Animation d'apparition
        requestAnimationFrame(() => {
            messageElement.style.opacity = '1';
        });

        // Suppression automatique après quelques secondes
        setTimeout(() => {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageContainer.removeChild(messageElement);
            }, 500);
        }, 3000);
    }

    // Intégration de la carte
    function initCarte() {
        const carteContainer = document.getElementById('carte');
        if (carteContainer) {
            carteContainer.innerHTML = `
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3192.5567533273!2d10.189473315719628!3d36.84499577994399!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12fd354598664f33%3A0x3a9e989a69adc305!2sManar%2C%20Tunis!5e0!3m2!1sfr!2stn!4v1648134289934!5m2!1sfr!2stn" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            `;
        }
    }

    initCarte();
});