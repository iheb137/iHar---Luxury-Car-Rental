// =================================================================
// == JENKINSFILE FINAL ET OPTIMISÉ POUR UN ENVIRONNEMENT STABLE ==
// =================================================================

pipeline {
    // On utilise l'agent de base de Jenkins. Il est suffisant car il peut
    // utiliser le Docker de l'hôte grâce à notre configuration.
    agent any

    // --- Variables Globales ---
    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'minikube-config'
    }

    // --- Séquence des Étapes ---
    stages {

        // ÉTAPE 1: Construire et Publier l'image Docker
        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo "Construction de l'image Docker..."
                    // La commande 'docker' fonctionne car notre conteneur Jenkins
                    // est bien configuré (lancé en root avec le socket Docker partagé).
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    
                    echo "Publication sur Docker Hub..."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        // ÉTAPE 2: Déployer sur Kubernetes
        stage('Deploy to Kubernetes') {
            steps {
                // La commande 'withKubeConfig' est la méthode la plus propre.
                // Elle prépare l'environnement pour que 'kubectl' fonctionne
                // sans problème de chemin ou d'authentification.
                withKubeConfig([credentialsId: KUBECONFIG_CREDENTIALS_ID]) {
                    echo "Déploiement sur le cluster (authentifié via kubeconfig)..."
                    sh '''
                        # On vérifie la connexion pour être sûr
                        kubectl cluster-info
                        
                        echo "--> Déploiement de la base de données MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut des déploiements..."
                        kubectl rollout status deployment/mysql-deployment
                        kubectl rollout status deployment/car-rental-deployment
                    '''
                }
            }
        }
    }
    
    // --- Actions Post-Build ---
    post {
        always {
            echo "Pipeline terminée."
            cleanWs()
        }
    }
}
