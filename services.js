// services.js
document.addEventListener('DOMContentLoaded', function() {
    // Liste des services
    const services = [
        {
            titre: 'Location longue durée',
            description: 'Bénéficiez de tarifs préférentiels pour des locations de plus de 30 jours.',
            icone: '🚗'
        },
        {
            titre: 'Livraison à domicile',
            description: 'Nous livrons votre véhicule directement à votre adresse dans le Grand Tunis.',
            icone: '🏠'
        },
        {
            titre: 'Assurance tout risque',
            description: 'Chaque location inclut une assurance complète avec franchise réduite.',
            icone: '🛡️'
        },
        {
            titre: 'Assistance 24/7',
            description: 'Support technique et dépannage disponibles à tout moment.',
            icone: '📞'
        }
    ];

    // Affichage dynamique des services
    const servicesContainer = document.getElementById('services-container');
    if (servicesContainer) {
        services.forEach(service => {
            const serviceElement = document.createElement('div');
            serviceElement.classList.add('service-card');
            serviceElement.innerHTML = `
                <div class="service-icon">${service.icone}</div>
                <h3>${service.titre}</h3>
                <p>${service.description}</p>
            `;
            
            serviceElement.style.cssText = `
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                transition: transform 0.3s;
            `;

            serviceElement.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });

            serviceElement.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });

            servicesContainer.appendChild(serviceElement);
        });
    }

    // Simulation de calcul de disponibilité
    function verifierDisponibilite(vehicule, dateDebut, dateFin) {
        // Simulation de vérification de disponibilité
        const disponibilite = Math.random() > 0.2; // 80% de chance d'être disponible
        return disponibilite;
    }

    // Exemple d'utilisation de la vérification de disponibilité
    const checkDispoBouton = document.getElementById('checkDispo');
    if (checkDispoBouton) {
        checkDispoBouton.addEventListener('click', function() {
            const vehicule = document.getElementById('vehiculeSelect').value;
            const dateDebut = document.getElementById('dateDebutDispo').value;
            const dateFin = document.getElementById('dateFinDispo').value;

            const estDisponible = verifierDisponibilite(vehicule, dateDebut, dateFin);

            const messageDisponibilite = document.getElementById('messageDisponibilite');
            messageDisponibilite.style.color = estDisponible ? 'green' : 'red';
            messageDisponibilite.textContent = estDisponible 
                ? 'Le véhicule est disponible!' 
                : 'Désolé, le véhicule est indisponible pour ces dates.';
        });
    }
});