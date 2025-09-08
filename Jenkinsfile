// =================================================================
// == JENKINSFILE FINAL (AVEC CREDENTIALS KUBERNETES) ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        // L'ID du nouveau credential Kubernetes que nous venons de créer.
        KUBECONFIG_CREDENTIALS_ID = 'minikube-config'
    }

    stages {

        stage('Install Tools') {
            steps {
                echo "Installation des outils nécessaires..."
                sh '''
                    apt-get update && apt-get install -y curl
                    # --- Installation du client Docker ---
                    curl -fsSL https://get.docker.com -o get-docker.sh
                    sh get-docker.sh
                    docker --version
                    # --- Installation de kubectl ---
                    curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl"
                    install -o root -g root -m 0755 kubectl /usr/local/bin/kubectl
                    kubectl version --client
                '''
            }
        }

        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo "Construction et publication de l'image Docker..."
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        // --- ÉTAPE 3: Déploiement sur Kubernetes (Version Propre) ---
        stage('Deploy to Kubernetes') {
            steps {
                // Cette commande 'withKubeConfig' est la clé.
                // Elle utilise notre credential 'minikube-config' et configure kubectl
                // automatiquement et de manière sécurisée pour cette étape.
                withKubeConfig([credentialsId: KUBECONFIG_CREDENTIALS_ID]) {
                    echo "Déploiement sur le cluster Kubernetes (authentifié)..."
                    sh '''
                        # Plus besoin de spécifier --server ou les certificats !
                        # kubectl est maintenant automatiquement configuré.
                        
                        echo "--> Déploiement de la base de données MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut du déploiement..."
                        kubectl rollout status deployment/car-rental-deployment
                    '''
                }
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
