// Pipeline déclarative, version finale corrigée

pipeline {
    agent none

    environment {
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // VÉRIFIEZ QUE CET ID EST CORRECT
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
                docker { image 'lachlanevenson/k8s-kubectl:v1.23.3' }
            }
            steps {
                echo "Déploiement sur le cluster Kubernetes..."
                sh '''
                    echo "--> Déploiement de MySQL..."
                    kubectl apply -f k8s/mysql.yaml
                    
                    echo "--> Déploiement de l'application Car Rental..."
                    kubectl apply -f k8s/deployment.yaml
                    
                    echo "--> Exposition du service Car Rental..."
                    kubectl apply -f k8s/service.yaml
                    
                    echo "--> Vérification du statut du déploiement..."
                    kubectl rollout status deployment/car-rental-deployment
                '''
            }
        }
    }
    
    post {
        always {
            // CORRECTION: On a enlevé la ligne "agent any" qui était ici.
            // Les étapes de ce bloc s'exécutent sur l'agent Jenkins par défaut.
            steps {
                 echo "Pipeline terminée. Nettoyage de l'espace de travail."
                 cleanWs()
            }
        }
    }
}
