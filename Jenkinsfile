// Jenkinsfile final, autonome, qui installe ses propres dépendances.

pipeline {
    // On exécute toute la pipeline sur l'agent de base de Jenkins.
    agent any

    environment {
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
    }

    stages {

        // --- ÉTAPE 1: Installation des outils ---
        stage('Install Tools') {
            steps {
                echo "Installation des outils nécessaires (Docker & kubectl)..."
                sh '''
                    # On met à jour la liste des paquets et on installe les prérequis
                    apt-get update
                    apt-get install -y apt-transport-https ca-certificates curl gnupg lsb-release

                    # --- Installation du client Docker ---
                    echo "Installation du client Docker..."
                    # --- CORRECTION APPLIQUÉE ICI ---
                    # On ajoute les options --batch et --yes pour que la commande gpg ne soit pas interactive
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
        stage('Deploy to Kubernetes') {
            steps {
                 echo "Déploiement sur le cluster Kubernetes..."
                 sh '''
                    echo "--> Déploiement de MySQL..."
                    kubectl apply -f k8s/mysql.yaml
                    
                    echo "--> Déploiement de l'application Car Rental..."
                    kubectl apply -f k8s/deployment.yaml
                    
                    echo "--> Exposition du service Car Rental..."
                    kubectl apply -f k8s/service.yaml
                    
                    echo "--> Vérification du statut du déploiement..."
                    kubectl rollout status deployment/car-rental-deployment
                '''
            }
        }
    }
    
    post {
        always {
            echo "Pipeline terminée."
            cleanWs()
        }
    }
}
