document.addEventListener('DOMContentLoaded', function() {
    const reclamationForm = document.getElementById('reclamationForm');
    
    if (reclamationForm) {
        reclamationForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Récupération des données du formulaire
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            const telephone = document.getElementById('telephone').value.trim();
            const typeReclamation = document.getElementById('type-reclamation').value;
            const details = document.getElementById('details').value.trim();
            
            // Validation côté client
            if (!nom || !email || !telephone || !typeReclamation || !details) {
                alert('Veuillez remplir tous les champs');
                return;
            }
            
            // Validation email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Veuillez entrer une adresse email valide');
                return;
            }
            
            // Validation du téléphone (numéro tunisien)
            const telephoneRegex = /^(2|5|9)\d{7}$/;
            if (!telephoneRegex.test(telephone)) {
                alert('Veuillez entrer un numéro de téléphone tunisien valide');
                return;
            }
            
            // Création des données à envoyer
            const formData = new FormData();
            formData.append('nom', nom);
            formData.append('email', email);
            formData.append('telephone', telephone);
            formData.append('type_reclamation', typeReclamation);
            formData.append('details', details);
            
            // Envoi des données au serveur
            fetch('traiter_reclamation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Correspondance entre les valeurs du select et les noms des types de réclamation
                    const typeReclamationNoms = {
                        'etat-vehicule': 'État du véhicule',
                        'service': 'Qualité du service',
                        'facturation': 'Problème de facturation',
                        'autre': 'Autre'
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
                        <h2 style="color: #4CAF50; margin-bottom: 15px;">Réclamation Envoyée!</h2>
                        <p style="margin-bottom: 20px; color: #333;">
                            <strong>Détails de la réclamation:</strong><br>
                            Type: ${typeReclamationNoms[data.details.type]}<br>
                            Nom: ${data.details.nom}<br>
                            Email: ${data.details.email}<br>
                            Téléphone: ${data.details.telephone}
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
                        reclamationForm.reset();
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
                alert('Une erreur est survenue lors de l\'envoi de la réclamation. Veuillez réessayer.');
            });
        });
    }
});