// =================================================================
// == JENKINSFILE FINAL POUR L'IMAGE PERSONNALISÉE ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = "dockerhub-cred"
        K8S_TOKEN_CREDENTIALS_ID = "jenkins-k8s-token" // Assurez-vous que ce nom correspond exactement à votre credential
        K8S_SERVER_URL = "https://kubernetes.docker.internal:6443"
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
                        
                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour de l'image dans le deployment..."
                        # Utilisation de perl pour une substitution portable
                        perl -i -pe "s|image: .*|image: ${env.DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        
                        echo "--> Déploiement de l'application..."
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut des déploiements..."
                        kubectl rollout status deployment/mysql-deployment --timeout=120s || echo "MySQL deployment status check skipped"
                        kubectl rollout status deployment/car-rental-deployment --timeout=120s || echo "App deployment status check skipped"
                        
                        echo "--> Affichage des ressources déployées..."
                        kubectl get deployments,services,pods
                    """
                }
            }
        }
    }
    
    post {
        always {
            cleanWs()
        }
        success {
            echo "Déploiement réussi !"
            script {
                withKubeConfig([
                    credentialsId: env.K8S_TOKEN_CREDENTIALS_ID,
                    serverUrl: env.K8S_SERVER_URL,
                    clusterName: env.K8S_CLUSTER_NAME
                ]) {
                    sh """
                        echo "URLs d'accès :"
                        kubectl get services -o wide
                    """
                }
            }
        }
        failure {
            echo "Échec du déploiement. Vérifiez les logs ci-dessus."
            script {
                withKubeConfig([
                    credentialsId: env.K8S_TOKEN_CREDENTIALS_ID,
                    serverUrl: env.K8S_SERVER_URL,
                    clusterName: env.K8S_CLUSTER_NAME
                ]) {
                    sh """
                        echo "Derniers logs des pods :"
                        kubectl get pods
                        kubectl describe pods
                    """
                }
            }
        }
    }
}
