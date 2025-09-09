pipeline {
    agent any

    environment {
        DOCKERHUB_CRED = 'dockerhub-cred'
        IMAGE = 'iheb99/luxury-car-rental'
        KUBE_CRED_ID = 'kubeconfig-host'
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    docker.withRegistry('', DOCKERHUB_CRED) {
                        def img = docker.build("${IMAGE}:${env.BUILD_NUMBER}", ".")
                        img.push()
                        img.push("latest")
                    }
                }
            }
        }

        stage('Test Kubernetes Connection') {
            steps {
                withKubeConfig([credentialsId: KUBE_CRED_ID]) {
                    sh """
                      echo '>>> Testing Kubernetes connection'
                      kubectl cluster-info
                      kubectl get nodes
                    """
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                withKubeConfig([credentialsId: KUBE_CRED_ID]) {
                    sh """
                      echo '>>> Deploying MySQL (if needed)'
                      kubectl apply -f k8s/mysql.yaml || echo 'MySQL may already be deployed'
                      
                      echo '>>> Deploying the PHP application'
                      kubectl apply -f k8s/deployment.yaml
                      
                      echo '>>> Applying service'
                      kubectl apply -f k8s/service.yaml
                      
                      echo '>>> Rolling status'
                      kubectl rollout status deployment/luxury-car-rental
                    """
                }
            }
        }
    }

    post {
        success { echo '✅ Pipeline terminé avec succès.' }
        failure { echo '❌ Pipeline en échec. Vérifie les logs.' }
    }
}
