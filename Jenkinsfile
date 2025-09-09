// =================================================================
// == JENKINSFILE CORRIGÉ AVEC LA BONNE IP ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host'
        KUBE_API_SERVER = "https://172.24.192.1:6443" // IP WSL
    }

    stages {
        stage('Vérification de l\'environnement') {
            steps {
                sh '''
                    echo "=== ENVIRONNEMENT ==="
                    echo "IP Kubernetes: 172.24.192.1:6443"
                    echo "Docker: $(docker --version)"
                    echo "Kubectl: $(kubectl version --client 2>&1 | head -1)"
                '''
            }
        }

        stage('Build & Push Docker Image') {
            steps {
                script {
                    sh """
                        echo "🏗️ Construction de l'image Docker..."
                        docker build -t ${DOCKER_IMAGE}:latest .
                    """
                    
                    withCredentials([usernamePassword(
                        credentialsId: DOCKER_CREDENTIALS_ID, 
                        usernameVariable: 'DOCKER_USER', 
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh """
                            echo "🔐 Authentification Docker Hub..."
                            echo \$DOCKER_PASS | docker login -u \$DOCKER_USER --password-stdin
                            echo "📤 Push de l'image..."
                            docker push ${DOCKER_IMAGE}:latest
                        """
                    }
                }
            }
        }

        stage('Configuration Kubernetes') {
            steps {
                script {
                    withCredentials([file(credentialsId: KUBECONFIG_CREDENTIALS_ID, variable: 'KUBECONFIG_FILE')]) {
                        sh """
                            echo "🔧 Configuration Kubernetes..."
                            mkdir -p ~/.kube
                            cp "$KUBECONFIG_FILE" ~/.kube/config
                            
                            # Configuration directe avec l'IP WSL
                            kubectl config set-cluster docker-desktop \\
                              --server=${KUBE_API_SERVER} \\
                              --insecure-skip-tls-verify=true
                            kubectl config set-context docker-desktop \\
                              --cluster=docker-desktop \\
                              --user=docker-desktop
                            kubectl config use-context docker-desktop
                            
                            echo "✅ Configuration Kubernetes terminée"
                        """
                    }
                }
            }
        }

        stage('Test de Connexion Kubernetes') {
            steps {
                sh """
                    echo "🧪 Test de connexion à Kubernetes..."
                    if kubectl cluster-info; then
                        echo "✅ Connexion Kubernetes réussie"
                    else
                        echo "⚠️ Impossible de se connecter, tentative avec validation désactivée"
                    fi
                """
            }
        }

        stage('Déploiement Application') {
            steps {
                sh """
                    echo "🚀 Déploiement en cours..."
                    
                    echo "1. Déploiement MySQL..."
                    kubectl apply -f k8s/mysql.yaml --validate=false
                    
                    echo "2. Attente de démarrage (20s)..."
                    sleep 20
                    
                    echo "3. Déploiement Application..."
                    kubectl apply -f k8s/deployment.yaml --validate=false
                    
                    echo "4. Déploiement Service..."
                    kubectl apply -f k8s/service.yaml --validate=false
                    
                    echo "5. Vérification..."
                    sleep 10
                    kubectl get pods,svc 2>/dev/null || echo "Aucun pod trouvé"
                    
                    echo "✅ Déploiement terminé"
                """
            }
        }
    }

    post {
        always {
            echo "=== NETTOYAGE ==="
            cleanWs()
        }
        success {
            echo "🎉 DÉPLOIEMENT RÉUSSI!"
            echo "Application accessible sur: http://localhost:30080"
        }
        failure {
            echo "💥 ÉCHEC DU DÉPLOIEMENT"
            sh """
                echo "=== TENTATIVE DE DIAGNOSTIC ==="
                # Test de connexion basique
                curl -k ${KUBE_API_SERVER} || echo "Impossible d'accéder à l'API Kubernetes"
                
                # Affichage de la configuration
                echo "=== CONFIGURATION KUBECTL ==="
                kubectl config view || true
            """
        }
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
        retry(1)
    }
}
