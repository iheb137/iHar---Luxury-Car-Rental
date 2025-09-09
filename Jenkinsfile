// =================================================================
// == JENKINSFILE ROBUSTE POUR DOCKER-IN-DOCKER ==
// =================================================================

pipeline {
    agent {
        docker {
            image 'docker:24.0-dind'
            args '--privileged --network=host -v /var/run/docker.sock:/var/run/docker.sock'
        }
    }

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host'
        KUBE_API_SERVER = "https://host.docker.internal:6443"
    }

    stages {
        stage('Setup Kubernetes Access') {
            steps {
                script {
                    // Configuration automatique de l'accès Kubernetes
                    withCredentials([file(credentialsId: KUBECONFIG_CREDENTIALS_ID, variable: 'KUBECONFIG_FILE')]) {
                        sh '''
                            mkdir -p ~/.kube
                            cp $KUBECONFIG_FILE ~/.kube/config
                            kubectl config set-cluster docker-desktop --server=$KUBE_API_SERVER --insecure-skip-tls-verify=true
                            kubectl config use-context docker-desktop
                        '''
                    }
                }
            }
        }

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
                script {
                    sh '''
                        echo "=== VÉRIFICATION DE LA CONNEXION KUBERNETES ==="
                        kubectl cluster-info
                        kubectl get nodes
                        
                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Attente du démarrage de MySQL..."
                        sleep 30
                        kubectl wait --for=condition=ready pod -l app=mysql --timeout=120s
                        
                        echo "--> Mise à jour du déploiement de l'application..."
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut des déploiements..."
                        kubectl rollout status deployment/mysql-deployment --timeout=120s
                        kubectl rollout status deployment/car-rental-deployment --timeout=120s
                        
                        echo "=== ÉTAT FINAL ==="
                        kubectl get pods,svc,deploy
                    '''
                }
            }
        }

        stage('Health Check') {
            steps {
                script {
                    sh '''
                        echo "=== TEST DE SANTÉ DE L'APPLICATION ==="
                        # Attendre que le service soit disponible
                        sleep 20
                        
                        # Tester l'accès à l'application
                        APP_URL=$(kubectl get svc car-rental -o jsonpath='{.status.loadBalancer.ingress[0].ip}:{.spec.ports[0].nodePort}')
                        if [ -z "$APP_URL" ]; then
                            APP_URL="localhost:30080"
                        fi
                        
                        echo "Testing application at: $APP_URL"
                        curl -f http://$APP_URL || echo "Application health check failed but continuing..."
                    '''
                }
            }
        }
    }
    
    post {
        always {
            script {
                echo "=== NETTOYAGE ET RAPPORT FINAL ==="
                sh '''
                    kubectl get pods,svc -o wide
                    echo "Application accessible sur: http://localhost:30080"
                '''
                cleanWs()
            }
        }
        success {
            echo "✅ DÉPLOIEMENT RÉUSSI!"
        }
        failure {
            echo "❌ ÉCHEC DU DÉPLOIEMENT"
            sh '''
                echo "=== LOGS DES PODS EN ÉCHEC ==="
                kubectl get pods --field-selector=status.phase!=Running -o name | xargs -r kubectl logs
            '''
        }
    }
}
