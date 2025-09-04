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
                    image 'docker:20.10.24'
                    args '-v /var/run/docker.sock:/var/run/docker.sock -v $PWD:/workspace -w /workspace'
                }
            }
            steps {
                sh 'docker build -t car-rental:${GIT_COMMIT} .'
            }
        }
        stage('Deploy to Minikube') {
            agent any
            steps {
                withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                    sh 'kubectl apply -f deployment.yaml'
                }
            }
        }
        stage('Smoke Test') {
            agent any
            steps {
                sh 'curl http://localhost:8080'
            }
        }
    }
    post {
        always {
            echo '✅ Pipeline terminé !'
        }
    }
}
