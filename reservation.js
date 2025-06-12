document.addEventListener('DOMContentLoaded', function() {
    const reservationForm = document.getElementById('reservationForm');
    
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Récupération des valeurs du formulaire
            const nom = document.getElementById('nom').value;
            const email = document.getElementById('email').value;
            const telephone = document.getElementById('telephone').value;
            const vehicule = document.getElementById('vehicule').value;
            const dateDebut = document.getElementById('date-debut').value;
            const dateFin = document.getElementById('date-fin').value;
            
            // Validation basic
            if (!nom || !email || !telephone || !vehicule || !dateDebut || !dateFin) {
                alert('Veuillez remplir tous les champs');
                return;
            }
            
            // Création des données à envoyer
            const formData = new FormData();
            formData.append('nom', nom);
            formData.append('email', email);
            formData.append('telephone', telephone);
            formData.append('vehicule', vehicule);
            formData.append('date_debut', dateDebut);
            formData.append('date_fin', dateFin);
            
            // Envoi des données au serveur
            fetch('traiter_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Correspondance entre les valeurs du select et les noms des véhicules
                    const nomVehicules = {
                        'mercedes-e': 'Mercedes Classe E',
                        'bmw-5': 'BMW Série 5',
                        'audi-q7': 'Audi Q7',
                        'range-rover': 'Range Rover Sport',
                        'tiguan': 'Volkswagen Tiguan',
                        'mercedes-c': 'Mercedes Classe C'
                    };
                    
                    // Création d'un modal de confirmation personnalisé
                    const confirmationModal = document.createElement('div');
                    confirmationModal.style.cssText = `
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        border-radius: 15px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                        padding: 30px;
                        max-width: 500px;
                        width: 90%;
                        text-align: center;
                        z-index: 1000;
                        animation: slideIn 0.5s ease-out;
                    `;

                    // Style pour l'overlay
                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0,0,0,0.5);
                        z-index: 999;
                        animation: fadeIn 0.5s ease-out;
                    `;

                    // Ajout de styles d'animation
                    const styleElement = document.createElement('style');
                    styleElement.textContent = `
                        @keyframes slideIn {
                            from { opacity: 0; transform: translate(-50%, -50%) scale(0.7); }
                            to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                        }
                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }
                    `;
                    document.head.appendChild(styleElement);

                    // Contenu du modal
                    confirmationModal.innerHTML = `
                        <div style="background-color: #4CAF50; color: white; border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 50px;">
                            ✓
                        </div>
                        <h2 style="color: #4CAF50; margin-bottom: 15px;">Réservation Confirmée!</h2>
                        <p style="margin-bottom: 20px; color: #333;">
                            <strong>Détails de la réservation:</strong><br>
                            Véhicule: ${nomVehicules[data.details.vehicule]}<br>
                            Du ${data.details.date_debut} au ${data.details.date_fin}<br>
                            Durée: ${data.details.nombre_jours} jours<br>
                            Prix total: ${data.details.prix_total.toFixed(2)} TND
                        </p>
                        <button id="fermerModal" style="
                            background-color: #4CAF50; 
                            color: white; 
                            border: none; 
                            padding: 10px 20px; 
                            border-radius: 5px; 
                            cursor: pointer;
                            transition: background-color 0.3s;
                        ">Fermer</button>
                    `;

                    // Fonction pour fermer le modal
                    function fermerModal() {
                        document.body.removeChild(confirmationModal);
                        document.body.removeChild(overlay);
                        reservationForm.reset();
                    }

                    // Ajout du modal et de l'overlay
                    document.body.appendChild(overlay);
                    document.body.appendChild(confirmationModal);

                    // Écouteur pour fermer le modal
                    const fermerBouton = confirmationModal.querySelector('#fermerModal');
                    fermerBouton.addEventListener('click', fermerModal);

                    // Fermeture au clic sur l'overlay
                    overlay.addEventListener('click', fermerModal);

                    // Option de fermeture avec la touche Échap
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            fermerModal();
                        }
                    });
                } else {
                    // Affichage d'une erreur
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la réservation. Veuillez réessayer.');
            });
        });
    }
});