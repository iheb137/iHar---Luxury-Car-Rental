pipeline {
    agent any
    environment {
        APP_NAME = 'car-rental'
        K8S_NAMESPACE = 'dev'
    }
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        stage('Build Docker Image on Minikube') {
            steps {
                // Utilise minikube directement pour builder l'image, compatible Windows
                bat """
                minikube -p minikube image build -t ${APP_NAME}:%GIT_COMMIT% .
                """
            }
        }
        stage('Deploy to Minikube') {
            steps {
                withCredentials([file(credentialsId: 'kubeconfig-dev', variable: 'KUBECONFIG')]) {
                    bat """
                    kubectl --kubeconfig=%KUBECONFIG% create namespace ${K8S_NAMESPACE} || echo 'namespace exists'
                    sed "s|IMAGE_PLACEHOLDER|${APP_NAME}:%GIT_COMMIT%|g" k8s/deployment.yaml | kubectl --kubeconfig=%KUBECONFIG% apply -f -
                    kubectl --kubeconfig=%KUBECONFIG% apply -f k8s/service.yaml
                    kubectl --kubeconfig=%KUBECONFIG% rollout status deployment/${APP_NAME} -n ${K8S_NAMESPACE} --timeout=120s
                    """
                }
            }
        }
        stage('Smoke Test') {
            steps {
                withCredentials([file(credentialsId: 'kubeconfig-dev', variable: 'KUBECONFIG')]) {
                    bat """
                    kubectl --kubeconfig=%KUBECONFIG% port-forward -n ${K8S_NAMESPACE} svc/${APP_NAME} 30080:80 > pf.log 2>&1
                    timeout /t 5
                    curl http://127.0.0.1:30080/ || (type pf.log & exit 1)
                    """
                }
            }
        }
    }
}
