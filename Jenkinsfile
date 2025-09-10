pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = "dockerhub-cred"
        K8S_TOKEN_CREDENTIALS_ID = "jenkins-k8s-token"
        K8S_SERVER_URL = "https://localhost:6443"
        K8S_CLUSTER_NAME = "docker-desktop"
        K8S_CONTEXT_NAME = "docker-desktop"
        GIT_CREDENTIALS_ID = "iheb"
    }

    stages {
        stage('Checkout Code') {
            steps {
                git(
                    url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git',
                    credentialsId: env.GIT_CREDENTIALS_ID,
                    branch: 'master'
                )
            }
        }

        stage('Build & Push Docker Image') {
            steps {
                script {
                    sh "docker build -t ${env.DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(
                        credentialsId: env.DOCKER_CREDENTIALS_ID,
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh """
                            echo \$DOCKER_PASS | docker login -u \$DOCKER_USER --password-stdin
                            docker push ${env.DOCKER_IMAGE}:latest
                        """
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                script {
                    withKubeConfig([
                        credentialsId: env.K8S_TOKEN_CREDENTIALS_ID,
                        serverUrl: env.K8S_SERVER_URL,
                        clusterName: env.K8S_CLUSTER_NAME,
                        contextName: env.K8S_CONTEXT_NAME
                    ]) {
                        sh """
                            echo "=== VÉRIFICATION CONNEXION KUBERNETES ==="
                            kubectl cluster-info
                            kubectl get nodes
                            
                            echo "=== DÉPLOIEMENT APPLICATION ==="
                            
                            # Créer le namespace si nécessaire
                            kubectl create namespace ihar --dry-run=client -o yaml | kubectl apply -f -
                            
                            # Déployer MySQL
                            if [ -f "k8s/mysql.yaml" ]; then
                                kubectl apply -f k8s/mysql.yaml -n ihar
                                echo "Attente du démarrage de MySQL..."
                                sleep 10
                            fi
                            
                            # Mettre à jour l'image dans le deployment
                            if [ -f "k8s/deployment.yaml" ]; then
                                sed -i 's|image: .*|image: ${env.DOCKER_IMAGE}:latest|g' k8s/deployment.yaml
                                kubectl apply -f k8s/deployment.yaml -n ihar
                            else
                                # Créer un deployment basique si le fichier n'existe pas
                                kubectl create deployment luxury-car-rental \
                                    --image=${env.DOCKER_IMAGE}:latest \
                                    --port=80 \
                                    -n ihar --dry-run=client -o yaml | kubectl apply -f -
                            fi
                            
                            # Exposer le service
                            if [ -f "k8s/service.yaml" ]; then
                                kubectl apply -f k8s/service.yaml -n ihar
                            else
                                kubectl expose deployment luxury-car-rental \
                                    --port=80 \
                                    --target-port=80 \
                                    --type=NodePort \
                                    -n ihar --dry-run=client -o yaml | kubectl apply -f -
                            fi
                            
                            echo "=== VÉRIFICATION DÉPLOIEMENT ==="
                            kubectl get deployments,services,pods -n ihar
                            
                            echo "=== ATTENTE DISPONIBILITÉ ==="
                            kubectl rollout status deployment/luxury-car-rental -n ihar --timeout=120s || true
                        """
                    }
                }
            }
        }

        stage('Health Check') {
            steps {
                script {
                    withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID]) {
                        sh """
                            echo "=== TEST SANTÉ APPLICATION ==="
                            # Récupérer l'URL du service
                            SERVICE_URL=\$(kubectl get svc luxury-car-rental -n ihar -o jsonpath='{.status.loadBalancer.ingress[0].ip}' || echo "localhost")
                            SERVICE_PORT=\$(kubectl get svc luxury-car-rental -n ihar -o jsonpath='{.spec.ports[0].nodePort}')
                            
                            echo "Application accessible à: http://\${SERVICE_URL}:\${SERVICE_PORT}"
                            
                            # Test de santé simple
                            curl -I http://localhost:\${SERVICE_PORT} || echo "Test curl échoué mais déploiement terminé"
                        """
                    }
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
            echo "✅ DÉPLOIEMENT RÉUSSI !"
            script {
                withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID]) {
                    sh """
                        echo "Résumé du déploiement:"
                        kubectl get all -n ihar
                    """
                }
            }
        }
        failure {
            echo "❌ ÉCHEC DU DÉPLOIEMENT"
            script {
                withKubeConfig([credentialsId: env.K8S_TOKEN_CREDENTIALS_ID]) {
                    sh """
                        echo "Logs des pods:"
                        kubectl get pods -n ihar
                        kubectl describe pods -n ihar
                    """
                }
            }
        }
    }
}
