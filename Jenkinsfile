// Pipeline déclarative, version simplifiée et corrigée

pipeline {
    // 'agent none' au niveau global force chaque étape à définir son propre environnement.
    agent none

    environment {
        // Le nom de votre image sur Docker Hub.
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
        // L'ID de vos identifiants Docker Hub stockés dans Jenkins.
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // VÉRIFIEZ QUE CET ID EST CORRECT
    }

    stages {

        // --- ÉTAPE 1: Checkout ---
        stage('Checkout') {
            agent any
            steps {
                echo "Récupération du code..."
                checkout scm
            }
        }

        // --- ÉTAPE 2: Build & Push Docker Image ---
        // On utilise un agent Docker qui contient les outils Docker.
        stage('Build & Push Docker Image') {
            agent {
                docker { image 'docker:20.10.17' }
            }
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

        // --- ÉTAPE 3: Deploy to Kubernetes ---
        // CORRECTION MAJEURE : On utilise un simple agent Docker
        // qui contient déjà la commande kubectl. C'est beaucoup plus simple.
        stage('Deploy to Kubernetes') {
            agent {
                docker { image 'lachlanevenson/k8s-kubectl:v1.23.3' }
            }
            steps {
                echo "Déploiement sur le cluster Kubernetes..."
                // NOTE: Cette étape suppose que votre Jenkins (qui tourne dans Docker)
                // peut accéder au réseau de Minikube.
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
            // Le nettoyage se fait sur un agent de base, c'est suffisant.
            agent any
            steps {
                 echo "Pipeline terminée. Nettoyage de l'espace de travail."
                 cleanWs()
            }
        }
    }
}
