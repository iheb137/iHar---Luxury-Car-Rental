// =================================================================
// == JENKINSFILE FINAL POUR LE PROJET IHAR - LUXURY CAR RENTAL ==
// =================================================================

pipeline {
    // On exécute toute la pipeline sur l'agent principal de Jenkins.
    agent any

    // --- Variables Globales ---
    // Ces variables sont disponibles dans toutes les étapes.
    environment {
        // Le nom de l'image Docker, AVEC VOTRE BON NOM D'UTILISATEUR DOCKER HUB.
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        // L'ID des identifiants Docker Hub que vous avez créés dans Jenkins.
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
    }

    // --- Séquence des Étapes ---
    stages {

        // --- ÉTAPE 1: Installation des Outils ---
        // Cette étape prépare l'environnement en installant Docker et kubectl.
        // Elle s'exécute à chaque fois pour garantir un environnement propre.
        stage('Install Tools') {
            steps {
                echo "Installation des outils nécessaires (Docker & kubectl)..."
                sh '''
                    # Mettre à jour la liste des paquets et installer les prérequis
                    apt-get update
                    apt-get install -y apt-transport-https ca-certificates curl gnupg lsb-release

                    # --- Installation du client Docker ---
                    echo "Installation du client Docker..."
                    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor --batch --yes -o /usr/share/keyrings/docker-archive-keyring.gpg
                    echo \
                      "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian \
                      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
                    apt-get update
                    apt-get install -y docker-ce-cli
                    docker --version

                    # --- Installation de kubectl ---
                    echo "Installation de kubectl..."
                    curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl"
                    install -o root -g root -m 0755 kubectl /usr/local/bin/kubectl
                    kubectl version --client
                '''
            }
        }

        // --- ÉTAPE 2: Build & Push sur Docker Hub ---
        // Construit l'image Docker et la publie sur votre compte Docker Hub.
        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo "Construction de l'image Docker: ${DOCKER_IMAGE}:latest"
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    
                    echo "Connexion et publication sur Docker Hub..."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        // --- ÉTAPE 3: Déploiement sur Kubernetes ---
        // Déploie l'application et sa base de données sur Minikube.
        stage('Deploy to Kubernetes') {
            steps {
                 echo "Déploiement sur le cluster Kubernetes..."
                 sh '''
                    # --- CORRECTION DES CHEMINS KUBECONFIG ---
                    # Remplace les chemins absolus Windows par des chemins Linux valides dans le conteneur.
                    # !! VÉRIFIEZ QUE 'saafi' EST BIEN VOTRE NOM D'UTILISATEUR WINDOWS !!
                    sed -i -e 's|C:\\\\Users\\\\saafi\\.minikube|/root/.minikube|g' /root/.kube/config

                    echo "--> Déploiement de la base de données MySQL..."
                    kubectl apply -f k8s/mysql.yaml
                    
                    echo "--> Mise à jour et déploiement de l'application Car Rental..."
                    # On met à jour dynamiquement le nom de l'image dans le fichier de déploiement.
                    sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                    kubectl apply -f k8s/deployment.yaml
                    
                    echo "--> Exposition du service de l'application..."
                    kubectl apply -f k8s/service.yaml
                    
                    echo "--> Vérification du statut du déploiement..."
                    kubectl rollout status deployment/car-rental-deployment
                '''
            }
        }
    }
    
    // --- Actions Post-Build ---
    // S'exécute toujours à la fin, que la pipeline réussisse ou échoue.
    post {
        always {
            echo "Pipeline terminée."
            cleanWs()
        }
    }
}
