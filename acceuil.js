// accueil.js
document.addEventListener('DOMContentLoaded', function() {
    // Animation du hero
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.style.opacity = '0';
        setTimeout(() => {
            hero.style.transition = 'opacity 1s ease-in-out';
            hero.style.opacity = '1';
        }, 100);
    }

    // Préchargement des images de voitures
    const carImages = document.querySelectorAll('.car-image');
    carImages.forEach(img => {
        const tempImg = new Image();
        tempImg.src = img.src;
    });

    // Gestion des animations des cartes de voitures
    const carCards = document.querySelectorAll('.car-card');
    carCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Vérification des réservations précédentes
    const reservationEnCours = localStorage.getItem('reservationEnCours');
    if (reservationEnCours) {
        const reservation = JSON.parse(reservationEnCours);
        const messageReservation = document.createElement('div');
        messageReservation.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
        `;
        messageReservation.innerHTML = `
            <strong>Dernière réservation</strong><br>
            Véhicule: ${reservation.vehicule}<br>
            Du ${reservation.dateDebut} au ${reservation.dateFin}
        `;
        document.body.appendChild(messageReservation);

        // Suppression après 10 secondes
        setTimeout(() => {
            document.body.removeChild(messageReservation);
        }, 10000);
    }
});