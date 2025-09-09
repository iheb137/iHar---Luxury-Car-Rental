// =================================================================
// == JENKINSFILE ULTIME (AVEC AUTHENTIFICATION PAR TOKEN K8S) ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBERNETES_TOKEN_ID = 'kubernetes-token'
        // CORRECTION : On utilise l'adresse interne 'host.docker.internal'
        KUBERNETES_SERVER = 'https://host.docker.internal:6443'
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                withCredentials([string(credentialsId: KUBERNETES_TOKEN_ID, variable: 'KUBE_TOKEN')]) {
                    echo "Déploiement sur le cluster (authentifié via Service Account Token)..."
                    sh '''
                        echo "--> Déploiement de MySQL..."
                        kubectl apply --server=${KUBERNETES_SERVER} --token=${KUBE_TOKEN} --insecure-skip-tls-verify=true -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply --server=${KUBERNETES_SERVER} --token=${KUBE_TOKEN} --insecure-skip-tls-verify=true -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply --server=${KUBERNETES_SERVER} --token=${KUBE_TOKEN} --insecure-skip-tls-verify=true -f k8s/service.yaml
                        
                        echo "--> Attente de la fin des déploiements..."
                        sleep 15
                        kubectl rollout status --server=${KUBERNETES_SERVER} --token=${KUBE_TOKEN} --insecure-skip-tls-verify=true deployment/mysql-deployment
                        kubectl rollout status --server=${KUBERNETES_SERVER} --token=${KUBE_TOKEN} --insecure-skip-tls-verify=true deployment/car-rental-deployment
                    '''
                }
            }
        }
    }
    
    post {
        always {
            echo "Pipeline terminée."
            cleanWs()
        }
    }
}
