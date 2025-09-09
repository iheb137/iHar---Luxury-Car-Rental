// =================================================================
// == JENKINSFILE CORRIGÉ ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = "dockerhub-cred"
        K8S_TOKEN_CREDENTIALS_ID = "jenkins-k8s-token"
        K8S_SERVER_URL = "https://host.docker.internal:6443" // ← CORRIGÉ ICI
        K8S_CLUSTER_NAME = "docker-desktop"
        K8S_CONTEXT_NAME = "docker-desktop"
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    sh "docker build -t ${env.DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(credentialsId: env.DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo \$DOCKER_PASS | docker login -u \$DOCKER_USER --password-stdin"
                        sh "docker push ${env.DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                // Test de connexion d'abord
                sh """
                    echo "Test de résolution DNS..."
                    ping -c 1 host.docker.internal || true
                    echo "Test de connexion à l'API..."
                    curl -k https://host.docker.internal:6443/healthz || true
                """
                
                withKubeConfig([
                    credentialsId: env.K8S_TOKEN_CREDENTIALS_ID,
                    serverUrl: env.K8S_SERVER_URL,
                    clusterName: env.K8S_CLUSTER_NAME,
                    contextName: env.K8S_CONTEXT_NAME
                ]) {
                    sh """
                        echo "Vérification de la connexion au cluster..."
                        kubectl cluster-info
                        kubectl get nodes
                        
                        # Reste de vos commandes de déploiement...
                        echo "Déploiement en cours..."
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
