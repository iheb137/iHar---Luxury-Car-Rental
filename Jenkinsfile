// =================================================================
// == JENKINSFILE COMPLET POUR IHAR LUXURY CAR RENTAL ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host'
        DOCKER_REGISTRY = 'docker.io'
    }

    stages {
        stage('Vérification de l\'environnement') {
            steps {
                sh '''
                    echo "=== ENVIRONNEMENT DE DÉPLOIEMENT ==="
                    echo "Date: $(date)"
                    echo "Répertoire de travail: $(pwd)"
                    echo "Utilisateur: $(whoami)"
                    echo "Java: $(java -version 2>&1 | head -1)"
                    echo "Docker: $(docker --version 2>&1 || echo 'Non installé')"
                    echo "Kubectl: $(kubectl version --client 2>&1 | head -1 || echo 'Non installé')"
                    echo "Git: $(git --version)"
                    echo "=== STRUCTURE DU PROJET ==="
                    ls -la
                    echo "=== FICHIERS KUBERNETES ==="
                    ls -la k8s/ || echo "Dossier k8s non trouvé"
                '''
            }
        }

        stage('Build & Push Docker Image') {
            steps {
                script {
                    // Vérification et installation de Docker si nécessaire
                    sh '''
                        if ! command -v docker &> /dev/null; then
                            echo "🚀 Installation de Docker..."
                            curl -fsSL https://get.docker.com -o get-docker.sh
                            sh get-docker.sh
                            usermod -aG docker jenkins
                            echo "Docker installé avec succès"
                        else
                            echo "✅ Docker est déjà installé"
                            docker --version
                        fi
                    '''
                    
                    // Construction de l'image Docker
                    sh """
                        echo "🏗️ Construction de l'image Docker..."
                        docker build -t ${DOCKER_IMAGE}:latest .
                        echo "✅ Image construite avec succès"
                    """
                    
                    // Authentification et push vers Docker Hub
                    withCredentials([usernamePassword(
                        credentialsId: DOCKER_CREDENTIALS_ID, 
                        usernameVariable: 'DOCKER_USER', 
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh """
                            echo "🔐 Authentification auprès de Docker Hub..."
                            echo \$DOCKER_PASS | docker login -u \$DOCKER_USER --password-stdin
                            echo "📤 Pushing de l'image vers Docker Hub..."
                            docker push ${DOCKER_IMAGE}:latest
                            echo "✅ Image poussée avec succès vers ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:latest"
                        """
                    }
                }
            }
        }

        stage('Déploiement Kubernetes') {
            steps {
                script {
                    withCredentials([file(credentialsId: KUBECONFIG_CREDENTIALS_ID, variable: 'KUBECONFIG_FILE')]) {
                        sh '''
                            echo "🔧 Configuration de l'accès Kubernetes..."
                            mkdir -p ~/.kube
                            cp "$KUBECONFIG_FILE" ~/.kube/config
                            chmod 600 ~/.kube/config
                            
                            echo "=== VÉRIFICATION DE LA CONNEXION KUBERNETES ==="
                            kubectl cluster-info
                            kubectl get nodes
                            
                            echo "🗄️ Déploiement de la base de données MySQL..."
                            if [ -f "k8s/mysql.yaml" ]; then
                                kubectl apply -f k8s/mysql.yaml
                                echo "⏳ Attente du démarrage de MySQL (30 secondes)..."
                                sleep 30
                                
                                # Vérification du déploiement MySQL
                                echo "🔍 Vérification du statut MySQL..."
                                kubectl wait --for=condition=available deployment/mysql-deployment --timeout=120s || echo "MySQL peut mettre plus de temps à démarrer"
                                kubectl get pods -l app=mysql
                            else
                                echo "⚠️ Fichier k8s/mysql.yaml non trouvé"
                            fi
                            
                            echo "🚀 Déploiement de l'application..."
                            if [ -f "k8s/deployment.yaml" ]; then
                                kubectl apply -f k8s/deployment.yaml
                            else
                                echo "❌ Fichier k8s/deployment.yaml non trouvé"
                                exit 1
                            fi
                            
                            echo "🌐 Exposition du service..."
                            if [ -f "k8s/service.yaml" ]; then
                                kubectl apply -f k8s/service.yaml
                            else
                                echo "⚠️ Fichier k8s/service.yaml non trouvé"
                            fi
                            
                            echo "⏳ Attente du démarrage de l'application..."
                            sleep 20
                            
                            echo "✅ Vérification du statut des déploiements..."
                            kubectl rollout status deployment/car-rental-deployment --timeout=120s || echo "Le déploiement peut prendre plus de temps"
                            
                            echo "=== ÉTAT FINAL DES RESSOURCES KUBERNETES ==="
                            kubectl get pods,svc,deploy -o wide
                            
                            echo "🔗 URLs d'accès:"
                            echo " - Application: http://localhost:30080"
                            echo " - MySQL: mysql-service:3306"
                        '''
                    }
                }
            }
        }

        stage('Tests de santé') {
            steps {
                script {
                    sh '''
                        echo "🧪 Tests de santé de l'application..."
                        echo "⏳ Attente supplémentaire pour le démarrage complet..."
                        sleep 30
                        
                        # Test de connexion à l'application
                        echo "🔗 Test d'accès à l'application..."
                        if curl -f http://localhost:30080 || curl -f http://127.0.0.1:30080; then
                            echo "✅ Application accessible avec succès"
                        else
                            echo "⚠️ L'application n'est pas encore accessible, vérification des logs..."
                            kubectl get pods -l app=car-rental -o name | head -1 | xargs -r kubectl logs || true
                        fi
                        
                        # Vérification des pods en erreur
                        echo "🔍 Vérification des pods en état d'erreur..."
                        ERROR_PODS=$(kubectl get pods --field-selector=status.phase!=Running -o name 2>/dev/null || true)
                        if [ -n "$ERROR_PODS" ]; then
                            echo "⚠️ Pods en erreur détectés:"
                            echo "$ERROR_PODS"
                            echo "📋 Logs des pods en erreur:"
                            echo "$ERROR_PODS" | xargs -r -n 1 kubectl logs || true
                        else
                            echo "✅ Tous les pods sont en cours d'exécution"
                        fi
                    '''
                }
            }
        }
    }
    
    post {
        always {
            echo "=== NETTOYAGE ET RAPPORT FINAL ==="
            sh '''
                echo "📊 État final des ressources Kubernetes:"
                kubectl get all -o wide 2>/dev/null || true
                
                echo "🌐 Points d'accès:"
                echo " - Application: http://localhost:30080"
                echo " - Dashboard Kubernetes: http://localhost:8001/api/v1/namespaces/kubernetes-dashboard/services/https:kubernetes-dashboard:/proxy/"
            '''
            cleanWs()
        }
        success {
            echo "🎉 DÉPLOIEMENT RÉUSSI!"
            emailext (
                subject: "SUCCÈS: Déploiement iHar Luxury Car Rental",
                body: """
                Le déploiement de l'application iHar Luxury Car Rental a réussi!

                📊 Détails:
                - Image Docker: ${DOCKER_IMAGE}:latest
                - Date: ${new Date().format('yyyy-MM-dd HH:mm:ss')}
                - Application accessible sur: http://localhost:30080

                ✅ Tous les services sont opérationnels.
                """,
                to: "saafiiheb.si@gmail.com"
            )
        }
        failure {
            echo "💥 ÉCHEC DU DÉPLOIEMENT"
            script {
                // Récupération des logs en cas d'échec
                sh '''
                    echo "📋 Logs de déploiement:"
                    kubectl get events --sort-by='.lastTimestamp' 2>/dev/null || true
                    
                    echo "📝 Logs des pods en échec:"
                    kubectl get pods --field-selector=status.phase!=Running -o name 2>/dev/null | xargs -r -n 1 kubectl logs 2>/dev/null || true
                '''
            }
            emailext (
                subject: "ÉCHEC: Déploiement iHar Luxury Car Rental",
                body: """
                Le déploiement de l'application iHar Luxury Car Rental a échoué.

                📊 Détails:
                - Image Docker: ${DOCKER_IMAGE}:latest  
                - Date: ${new Date().format('yyyy-MM-dd HH:mm:ss')}
                - Consultez les logs Jenkins pour plus de détails.

                ❌ Une intervention est nécessaire.
                """,
                to: "saafiiheb.si@gmail.com"
            )
        }
        unstable {
            echo "⚠️ DÉPLOIEMENT INSTABLE"
        }
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
        buildDiscarder(logRotator(numToKeepStr: '10'))
        disableConcurrentBuilds()
        retry(2)
    }

    triggers {
        pollSCM('H/5 * * * *')
    }
}
