pipeline {
    agent any
    environment {
        IMAGE_NAME = "car-rental"
    }
    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', branch: 'master'
            }
        }

        stage('Build Docker Image on Minikube') {
            steps {
                script {
                    withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                        sh """
                        echo "Building Docker image on Minikube..."
                        eval \$(minikube -p minikube docker-env)
                        docker build -t \$IMAGE_NAME:\$(git rev-parse --short HEAD) .
                        """
                    }
                }
            }
        }

        stage('Deploy to Minikube') {
            steps {
                script {
                    withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                        sh """
                        echo "Deploying to Minikube..."
                        kubectl --kubeconfig=\$KUBECONFIG apply -f k8s/deployment.yaml
                        kubectl --kubeconfig=\$KUBECONFIG apply -f k8s/service.yaml
                        """
                    }
                }
            }
        }

        stage('Smoke Test') {
            steps {
                sh """
                echo "Running basic smoke test..."
                # Exemple : curl sur le service
                kubectl --kubeconfig=\$KUBECONFIG get pods
                """
            }
        }
    }
    post {
        success {
            echo "Pipeline terminé avec succès !"
        }
        failure {
            echo "Pipeline échoué !"
        }
    }
}
