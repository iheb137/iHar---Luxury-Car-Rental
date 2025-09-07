// Pipeline déclarative améliorée avec des agents spécifiques par étape

pipeline {
    // 'agent none' au niveau global force chaque étape à définir son propre environnement d'exécution.
    // C'est une bien meilleure pratique.
    agent none

    environment {
        // Le nom de votre image sur Docker Hub.
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
        // L'ID de vos identifiants Docker Hub stockés dans Jenkins.
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
    }

    stages {

        // --- ÉTAPE 1: Checkout ---
        // Cette étape s'exécute sur n'importe quel agent disponible pour simplement récupérer le code.
        stage('Checkout') {
            agent any
            steps {
                echo "Récupération du code depuis la branche master..."
                // 'checkout scm' est la manière standard de récupérer le code
                // configuré dans le job Jenkins. C'est plus simple et plus propre.
                checkout scm
            }
        }

        // --- ÉTAPE 2: Build & Push Image ---
        // Cette étape s'exécutera dans un conteneur Docker qui contient les outils Docker.
        // Cela résout le problème "docker: not found".
        stage('Build & Push Docker Image') {
            agent {
                // Jenkins va démarrer un conteneur à partir de l'image 'docker:latest'
                // et exécuter les étapes suivantes à l'intérieur.
                docker { image 'docker:20.10.17' }
            }
            steps {
                script {
                    echo "Construction de l'image Docker: ${DOCKER_IMAGE}:latest"
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."

                    echo "Connexion et publication sur Docker Hub..."
                    // Utilisation des identifiants stockés dans Jenkins.
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        // --- ÉTAPE 3: Deploy to Kubernetes ---
        // Cette étape s'exécutera dans un conteneur qui contient les outils Kubernetes (kubectl).
        // Cela résout le problème "kubectl: not found".
        stage('Deploy to Kubernetes') {
            agent {
                // On utilise une image publique qui contient kubectl.
                kubernetes {
                    cloud 'kubernetes' // Nom du cloud Kubernetes configuré dans Jenkins
                    yaml '''
apiVersion: v1
kind: Pod
metadata:
  labels:
    jenkins-agent: kubectl
spec:
  containers:
  - name: kubectl
    image: lachlanevenson/k8s-kubectl:v1.23.3
    command:
    - sleep
    args:
    - 99d
'''
                }
            }
            steps {
                container('kubectl') {
                    echo "Déploiement sur le cluster Minikube..."
                    sh '''
                        # Appliquer d'abord la base de données, puis l'application
                        kubectl apply -f k8s/mysql-deployment.yaml
                        kubectl apply -f k8s/app-deployment.yaml
                    '''
                }
            }
        }
    }

    post {
        // 'always' s'exécute toujours, que la pipeline réussisse ou échoue.
        always {
            echo "Nettoyage de l'espace de travail."
            // C'est une bonne pratique de se déconnecter de Docker Hub.
            // On le fait dans un agent Docker pour avoir la commande 'docker'.
            agent {
                docker { image 'docker:20.10.17' }
            }
            steps {
                echo "Déconnexion de Docker Hub."
                sh 'docker logout'
            }
            cleanWs()
        }
    }
}
