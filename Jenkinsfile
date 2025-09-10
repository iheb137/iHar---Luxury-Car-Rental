// =================================================================
// == JENKINSFILE CORRIGÉ ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = "dockerhub-cred"
        K8S_TOKEN_CREDENTIALS_ID = "jenkins-k8s-token"
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    // Build de l’image
                    sh "docker build -t ${env.DOCKER_IMAGE}:latest ."

                    // Login + Push sur DockerHub
                    withCredentials([usernamePassword(credentialsId: env.DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo \$DOCKER_PASS | docker login -u \$DOCKER_USER --password-stdin"
                        sh "docker push ${env.DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                // Utilisation du kubeconfig fourni via Jenkins Credentials
                withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID]) {
                    sh """
                        echo "Vérification de la connexion au cluster..."
                        kubectl cluster-info
                        kubectl get nodes
                        
                        # Exemple de déploiement (tu peux adapter à ton manifeste)
                        echo "Déploiement en cours..."
                        kubectl apply -f k8s/deployment.yaml
                        kubectl apply -f k8s/service.yaml
                    """
                }
            }
        }
    }

    post {
        always {
            cleanWs()
        }
    }
}
