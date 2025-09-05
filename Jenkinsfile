pipeline {
    agent any

    environment {
        DOCKER_TLS_VERIFY = "1"
        DOCKER_HOST = "tcp://host.docker.internal:52440"
        DOCKER_CERT_PATH = "/var/jenkins_home/.minikube/certs"
        IMAGE_NAME = "car-rental"
    }

    stages {
        stage('Checkout') {
            steps {
                sshagent(credentials: ['ssh-cred']) {
                    git branch: 'master',
                        url: 'git@github.com:iheb137/iHar---Luxury-Car-Rental.git'
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    echo "==> Building Docker image..."
                    docker build -t $IMAGE_NAME:$BUILD_NUMBER .
                '''
            }
        }

        stage('Deploy to Minikube') {
            steps {
                sh '''
                    echo "==> Deploying to Minikube..."
                    kubectl apply -f k8s/deployment.yaml
                    kubectl apply -f k8s/service.yaml
                '''
            }
        }

        stage('Smoke Test') {
            steps {
                sh '''
                    echo "==> Running Smoke Test..."
                    kubectl rollout status deployment/car-rental-deployment
                '''
            }
        }
    }
}
