// =================================================================
// == JENKINSFILE FINAL POUR DOCKER DESKTOP KUBERNETES ==
// =================================================================

pipeline {
    // On utilise l'agent de base de Jenkins. Avec la bonne configuration du conteneur,
    // il a accès à Docker et les plugins lui fourniront kubectl.
    agent any

    // --- Variables Globales ---
    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        // L'ID du credential contenant votre fichier kubeconfig.
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host' 
    }

    // --- Séquence des Étapes ---
    stages {

        // --- ÉTAPE 1: Construire et Publier l'image Docker ---
        stage('Build & Push Docker Image') {
            steps {
                script {
                    echo "Construction de l'image Docker..."
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    
                    echo "Publication sur Docker Hub..."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        // --- ÉTAPE 2: Déployer sur Kubernetes ---
        stage('Deploy to Kubernetes') {
            steps {
                // withKubeConfig est la méthode la plus propre. Elle utilise le credential
                // pour configurer kubectl afin qu'il se connecte au bon cluster
                // (dans notre cas, le cluster 'docker-desktop').
                withKubeConfig([credentialsId: KUBECONFIG_CREDENTIALS_ID]) {
                    echo "Déploiement sur le cluster (authentifié via kubeconfig)..."
                    sh '''
                        echo "Contexte Kubernetes actuel :"
                        kubectl config current-context
                        
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
