// =================================================================
// == JENKINSFILE FINAL (POUR JENKINS SUR KUBERNETES) ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
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
                // Jenkins utilise automatiquement son Service Account !
                // Plus besoin de withKubeConfig ou de tokens.
                sh '''
                    echo "Déploiement sur le cluster (authentification native)..."
                    kubectl cluster-info

                    echo "--> Déploiement de MySQL..."
                    kubectl apply -f k8s/mysql.yaml

                    echo "--> Mise à jour et déploiement de l'application..."
                    sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                    kubectl apply -f k8s/deployment.yaml

                    echo "--> Exposition du service..."
                    kubectl apply -f k8s/service.yaml

                    echo "--> Vérification du statut des déploiements..."
                    kubectl rollout status deployment/mysql-deployment --timeout=120s
                    kubectl rollout status deployment/car-rental-deployment --timeout=120s
                '''
            }
        }
    }

    post {
        always {
            cleanWs()
        }
    }
}