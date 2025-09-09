// =================================================================
// == JENKINSFILE SIMPLIFIÉ POUR JENKINS STANDARD ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host'
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    // Vérifier si Docker est disponible
                    sh '''
                        if ! command -v docker &> /dev/null; then
                            echo "Docker n'est pas installé. Installation en cours..."
                            curl -fsSL https://get.docker.com -o get-docker.sh
                            sh get-docker.sh
                            usermod -aG docker jenkins
                        fi
                        
                        docker build -t ${DOCKER_IMAGE}:latest .
                    '''
                    
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh '''
                            echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin
                            docker push ${DOCKER_IMAGE}:latest
                        '''
                    }
                }
            }
        }

       stage('Deploy to Kubernetes') {
    steps {
        script {
            withCredentials([file(credentialsId: KUBECONFIG_CREDENTIALS_ID, variable: 'KUBECONFIG_FILE')]) {
                sh '''
                    # Configuration de l'accès Kubernetes
                    mkdir -p ~/.kube
                    cp "$KUBECONFIG_FILE" ~/.kube/config
                    
                    echo "=== VÉRIFICATION DE LA CONNEXION KUBERNETES ==="
                    kubectl cluster-info
                    kubectl get nodes
                    
                    echo "--> Déploiement de MySQL..."
                    kubectl apply -f k8s/mysql.yaml
                    
                    echo "--> Attente du démarrage de MySQL..."
                    sleep 30
                    
                    echo "--> Déploiement de l'application..."
                    kubectl apply -f k8s/deployment.yaml
                    
                    echo "--> Exposition du service..."
                    kubectl apply -f k8s/service.yaml
                    
                    echo "--> Vérification du statut..."
                    kubectl rollout status deployment/mysql-deployment --timeout=120s
                    kubectl rollout status deployment/car-rental-deployment --timeout=120s
                    
                    echo "=== ÉTAT FINAL ==="
                    kubectl get pods,svc,deploy
                '''
            }
        }
    }
}
    
    post {
        always {
            echo "=== NETTOYAGE ==="
            cleanWs()
        }
        success {
            echo "✅ DÉPLOIEMENT RÉUSSI!"
            echo "Application accessible sur: http://localhost:30080"
        }
        failure {
            echo "❌ ÉCHEC DU DÉPLOIEMENT"
        }
    }
}
