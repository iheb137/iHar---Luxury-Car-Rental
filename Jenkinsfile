// =================================================================
// == JENKINSFILE FINAL POUR L'IMAGE PERSONNALISÉE ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        // MODIFIEZ CECI AVEC LE NOM EXACT DE VOTRE CREDENTIAL KUBERNETES
        K8S_TOKEN_CREDENTIALS_ID = 'jenkins-k8s-token' /
        K8S_SERVER_URL = 'https://kubernetes.docker.internal:6443'
        K8S_CLUSTER_NAME = 'docker-desktop'
        K8S_CONTEXT_NAME = 'docker-desktop'
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
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
                    sh '''
                        echo "Vérification de la connexion au cluster..."
                        kubectl cluster-info
                        kubectl get nodes
                        
                        echo "Création du namespace si nécessaire..."
                        kubectl create namespace ihar --dry-run=client -o yaml | kubectl apply -f -
                        
                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour de l'image dans le deployment..."
                        # Solution plus portable pour sed
                        if command -v sed &> /dev/null; then
                            sed -i.bak "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        else
                            # Fallback pour Windows ou autres systèmes
                            echo "image: ${DOCKER_IMAGE}:latest" > k8s/deployment.tmp
                            grep -v "image:" k8s/deployment.yaml >> k8s/deployment.tmp
                            mv k8s/deployment.tmp k8s/deployment.yaml
                        fi
                        
                        echo "--> Déploiement de l'application..."
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut des déploiements..."
                        timeout 120s bash -c 'while ! kubectl rollout status deployment/mysql-deployment 2>/dev/null; do sleep 5; done' || true
                        timeout 120s bash -c 'while ! kubectl rollout status deployment/car-rental-deployment 2>/dev/null; do sleep 5; done' || true
                        
                        echo "--> Affichage des ressources déployées..."
                        kubectl get deployments,services,pods --all-namespaces
                    '''
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
                withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID, serverUrl: env.K8S_SERVER_URL]) {
                    sh '''
                        echo "URLs d'accès :"
                        kubectl get services -o wide
                    '''
                }
            }
        }
        failure {
            echo "Échec du déploiement. Vérifiez les logs ci-dessus."
            script {
                withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID, serverUrl: env.K8S_SERVER_URL]) {
                    sh '''
                        echo "Derniers logs des pods :"
                        kubectl get pods --all-namespaces
                        kubectl describe pods --all-namespaces
                    '''
                }
            }
        }
    }
}
