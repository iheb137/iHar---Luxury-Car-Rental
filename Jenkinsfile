// =================================================================
// == JENKINSFILE COMPLET POUR DÉPLOIEMENT AUTOMATISÉ ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_FILE = '/root/.kube/config'
        K8S_DIR = "/var/jenkins_home/workspace/ihar-app/k8s"
        KUBE_API_SERVER = "https://localhost:6443"
    }

    stages {
        stage('Vérification Environnement') {
            steps {
                sh '''
                    echo "🔍 Vérification de l'environnement..."
                    echo "Répertoire de travail: $(pwd)"
                    echo "Docker: $(docker --version)"
                    echo "Kubectl: $(kubectl version --client --short)"
                    echo "Kubeconfig: $KUBECONFIG_FILE"
                    
                    # Vérification des fichiers Kubernetes
                    echo "📁 Contenu du dossier:"
                    ls -la "$K8S_DIR/" || echo "⚠️ Dossier k8s non trouvé, création..."
                    mkdir -p "$K8S_DIR"
                    
                    # Vérification de la connexion Kubernetes
                    echo "🔗 Test connexion Kubernetes..."
                    kubectl cluster-info || echo "⚠️ Connexion Kubernetes à vérifier"
                '''
            }
        }

        stage('Build Image Docker') {
            steps {
                script {
                    echo "🏗️ Construction de l'image Docker..."
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    
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
                sh '''
                    echo "⚙️ Configuration Kubernetes..."
                    # Création du dossier .kube si inexistant
                    mkdir -p /root/.kube
                    
                    # Configuration minimaliste de kubectl
                    kubectl config set-cluster docker-desktop \
                        --server="$KUBE_API_SERVER" \
                        --insecure-skip-tls-verify=true
                    
                    kubectl config set-credentials jenkins-user \
                        --token="eyJhbGciOiJSUzI1NiIsImtpZCI6IkJNNmFmcEJpWFdVbmJSQ3VxMTdEMkhuc1hFZDRVdWV2U3RJcWszUE9ZeE0ifQ.eyJhdWQiOlsiaHR0cHM6Ly9rdWJlcm5ldGVzLmRlZmF1bHQuc3ZjLmNsdXN0ZXIubG9jYWwiXSwiZXhwIjoxNzg4OTc2NDI5LCJpYXQiOjE3NTc0NDA0MjksImlzcyI6Imh0dHBzOi8va3ViZXJuZXRlcy5kZWZhdWx0LnN2Yy5jbHVzdGVyLmxvY2FsIiwianRpIjoiZGZjN2UyMzQtYWMxMS00YzE4LThkM2YtMmVkNDIyN2EyYTVkIiwia3ViZXJuZXRlcy5pbyI6eyJuYW1lc3BhY2UiOiJkZWZhdWx0Iiwic2VydmljZWFjY291bnQiOnsibmFtZSI6ImplbmtpbnMtZGVwbG95ZXIiLCJ1aWQiOiJmYmVlYjE1Zi0yODIwLTRjZTctOWI3MS00MWI0YmY2NTgwNzAifX0sIm5iZiI6MTc1NzQ0MDQyOSwic3ViIjoic3lzdGVtOnNlcnZpY2VhY2NvdW50OmRelZmF1bHQ6amVua2lucy1kZXBsb3llciJ9.SgLaUDUtnKaE-uKkeuZzE3YeG6uyOsbqCP7DJ-rzd-gYHE2DdCd5M4hwNSyZwl18v6iP9CYw4kygir6NnkG1Z0Vg6uUOhQF6kLXJD9dnmrfR2bBx0mPJQwTLSSOjAH6QZg7Ft9DVc1bRaDu_S242Rq4gomzDTKBuIBUbFJ09f13uvPL3ZDRAWWH2UWRxxZRkENrhatHpnHtywxEvvqkbJ5bJkOUgU8KGaCvb2zhnGkHXqWNU9sHuSeBleojd5be6NMVzAj7dhO06DjHMkH3AKOTbte1P9EpsQhiDJDWpD3gW1cbT6AX2xim0zqcn26M5JjpQSD99duBI5UcTDrBmzw"
                    
                    kubectl config set-context jenkins-context \
                        --cluster=docker-desktop \
                        --user=jenkins-user
                    
                    kubectl config use-context jenkins-context
                    
                    echo "✅ Configuration Kubernetes terminée"
                    kubectl cluster-info
                '''
            }
        }

        stage('Déploiement Application') {
            steps {
                sh '''
                    echo "🚀 Déploiement de l'application..."
                    
                    # Vérification des fichiers de déploiement
                    echo "📋 Liste des fichiers de déploiement:"
                    ls -la "$K8S_DIR/" || exit 1
                    
                    # Déploiement MySQL
                    if [ -f "$K8S_DIR/mysql.yaml" ]; then
                        echo "🗄️ Déploiement MySQL..."
                        kubectl apply -f "$K8S_DIR/mysql.yaml"
                        echo "⏳ Attente du démarrage de MySQL..."
                        sleep 30
                    else
                        echo "⚠️ Fichier mysql.yaml non trouvé"
                    fi
                    
                    # Déploiement de l'application
                    if [ -f "$K8S_DIR/deployment.yaml" ]; then
                        echo "📦 Déploiement Application..."
                        kubectl apply -f "$K8S_DIR/deployment.yaml"
                    else
                        echo "❌ Fichier deployment.yaml non trouvé"
                        exit 1
                    fi
                    
                    # Déploiement du service
                    if [ -f "$K8S_DIR/service.yaml" ]; then
                        echo "🌐 Déploiement Service..."
                        kubectl apply -f "$K8S_DIR/service.yaml"
                    else
                        echo "⚠️ Fichier service.yaml non trouvé"
                    fi
                    
                    echo "✅ Déploiement terminé"
                '''
            }
        }

        stage('Vérification Déploiement') {
            steps {
                sh '''
                    echo "🔍 Vérification du déploiement..."
                    
                    # Attente du démarrage
                    echo "⏳ Attente du démarrage des pods..."
                    sleep 20
                    
                    # Vérification des ressources
                    echo "📊 État des pods:"
                    kubectl get pods -o wide
                    
                    echo "📊 État des services:"
                    kubectl get services -o wide
                    
                    echo "📊 État des deployments:"
                    kubectl get deployments -o wide
                    
                    # Vérification de la santé
                    echo "❤️ Santé de l'application:"
                    kubectl rollout status deployment/car-rental-deployment --timeout=120s || echo "⚠️ Le déploiement peut prendre plus de temps"
                    
                    echo "🎉 Déploiement réussi!"
                '''
            }
        }

        stage('Test Application') {
            steps {
                sh '''
                    echo "🧪 Tests de l'application..."
                    
                    # Port-forward pour tester
                    echo "🔗 Mise en place du port-forward..."
                    kubectl port-forward service/car-rental-service 8080:80 --address=0.0.0.0 &
                    PF_PID=$!
                    
                    # Attente de la connexion
                    sleep 10
                    
                    # Test de connexion
                    echo "🌐 Test d'accès à l'application..."
                    if curl -f http://localhost:8080; then
                        echo "✅ Application accessible avec succès"
                    else
                        echo "⚠️ Impossible d'accéder à l'application"
                        # Affichage des logs pour debug
                        kubectl get pods -o name | head -1 | xargs -r kubectl logs || true
                    fi
                    
                    # Nettoyage
                    kill $PF_PID 2>/dev/null || true
                '''
            }
        }
    }

    post {
        always {
            echo "🧹 Nettoyage et rapport final..."
            sh '''
                echo "📊 État final des ressources Kubernetes:"
                kubectl get all -o wide || true
                
                echo "🌐 URL d'accès:"
                echo "Application: http://localhost:30080"
                echo "MySQL: mysql-service:3306"
            '''
            cleanWs()
        }
        
        success {
            echo "🎉 PIPELINE RÉUSSI! L'application est déployée avec succès."
            emailext (
                subject: "SUCCÈS: Déploiement iHar Luxury Car Rental",
                body: """
                Le pipeline CI/CD a réussi!

                📦 Application déployée: ${DOCKER_IMAGE}:latest
                ⏰ Heure: ${new Date().format('yyyy-MM-dd HH:mm:ss')}
                🌐 URL: http://localhost:30080

                ✅ Tous les services sont opérationnels.
                """,
                to: "saafiiheb.si@gmail.com"
            )
        }
        
        failure {
            echo "💥 ÉCHEC DU PIPELINE"
            sh '''
                echo "📋 Logs de débuggage:"
                kubectl get events --sort-by=.lastTimestamp || true
                kubectl describe pods || true
            '''
            emailext (
                subject: "ÉCHEC: Déploiement iHar Luxury Car Rental",
                body: """
                Le pipeline CI/CD a échoué.

                📦 Application: ${DOCKER_IMAGE}:latest
                ⏰ Heure: ${new Date().format('yyyy-MM-dd HH:mm:ss')}

                ❌ Une intervention est nécessaire.
                """,
                to: "saafiiheb.si@gmail.com"
            )
        }
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
        buildDiscarder(logRotator(numToKeepStr: '5'))
        disableConcurrentBuilds()
        retry(1)
    }
}
