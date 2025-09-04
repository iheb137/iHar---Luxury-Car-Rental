pipeline {
    agent any

    environment {
        IMAGE = "car-rental:${GIT_COMMIT}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image on Minikube') {
            steps {
                sh '''
                    set -e
                    echo "==> Building Docker image on Minikube..."
                    docker --tlsverify \
                        --tlscacert=/var/jenkins_home/.minikube/certs/ca.pem \
                        --tlscert=/var/jenkins_home/.minikube/certs/cert.pem \
                        --tlskey=/var/jenkins_home/.minikube/certs/key.pem \
                        -H tcp://127.0.0.1:52440 build -t ${IMAGE} .
                '''
            }
        }

        stage('Deploy to Minikube') {
            steps {
                sh '''
                    kubectl --kubeconfig=/var/jenkins_home/.kube/config apply -f k8s-deployment.yaml
                '''
            }
        }
    }
}
