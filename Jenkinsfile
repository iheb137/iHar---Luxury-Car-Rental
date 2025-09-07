// Pipeline déclarative améliorée avec des agents spécifiques par étape
pipeline {
    agent none
    environment {
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // VÉRIFIEZ QUE CET ID EST CORRECT DANS JENKINS
    }
    stages {
        stage('Checkout') {
            agent any
            steps {
                echo "Récupération du code..."
                checkout scm
            }
        }
        stage('Build & Push Docker Image') {
            agent {
                docker { image 'docker:20.10.17' }
            }
            steps {
                script {
                    echo "Construction de l'image Docker: ${DOCKER_IMAGE}:latest"
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    echo "Connexion et publication sur Docker Hub..."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }
        stage('Deploy to Kubernetes') {
            agent {
                kubernetes {
                    cloud 'kubernetes'
                    yaml '''
apiVersion: v1
kind: Pod
spec:
  containers:
  - name: kubectl
    image: lachlanevenson/k8s-kubectl:v1.23.3
    command: ["sleep"]
    args: ["99d"]
'''
                }
            }
            steps {
                container('kubectl') {
                    echo "Déploiement sur le cluster Minikube..."
                    sh '''
                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        echo "--> Déploiement de l'application Car Rental..."
                        kubectl apply -f k8s/deployment.yaml
                        echo "--> Exposition du service Car Rental..."
                        kubectl apply -f k8s/service.yaml
                        echo "--> Déploiement terminé. Attente de la stabilisation des pods..."
                        kubectl rollout status deployment/car-rental-deployment
                    '''
                }
            }
        }
    }
    post {
        always {
            agent {
                docker { image 'docker:20.10.17' }
            }
            steps {
                echo "Nettoyage : Déconnexion de Docker Hub."
                sh 'docker logout'
            }
        }
    }
}