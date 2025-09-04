pipeline {
    agent any
    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', credentialsId: 'iheb137/******', branch: 'master'
            }
        }
        stage('Build Docker Image on Minikube') {
            steps {
                script {
                    // Utiliser un agent Docker si Minikube n'est pas disponible directement
                    sh 'docker build -t car-rental:${GIT_COMMIT} .'
                }
            }
        }
        stage('Deploy to Minikube') {
            steps {
                withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                    sh 'kubectl apply -f deployment.yaml'
                }
            }
        }
        stage('Smoke Test') {
            steps {
                sh 'curl http://localhost:8080' // Ajuster selon ton endpoint
            }
        }
    }
    post {
        always {
            echo 'Pipeline terminé !'
        }
    }
}
