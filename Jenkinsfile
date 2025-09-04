pipeline {
    agent none

    stages {
        stage('Checkout') {
            agent any
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', branch: 'master'
            }
        }

        stage('Build Docker Image on Minikube') {
            agent {
                docker { 
                    image 'docker:20.10.24'   // version stable de docker
                    args '-v /var/run/docker.sock:/var/run/docker.sock' // accès au socket Docker hôte
                }
            }
            steps {
                sh 'docker --version'
                sh 'docker build -t car-rental:${GIT_COMMIT} .'
            }
        }

        stage('Deploy to Minikube') {
            agent any
            steps {
                withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                    sh 'kubectl apply -f k8s/deployment.yaml'
                }
            }
        }

        stage('Smoke Test') {
            agent any
            steps {
                sh 'kubectl rollout status deployment/car-rental-deployment'
                sh 'curl http://$(minikube ip):30080' // adapte selon ton Service exposé
            }
        }
    }

    post {
        always {
            echo '✅ Pipeline terminée avec Docker agent !'
        }
    }
}
