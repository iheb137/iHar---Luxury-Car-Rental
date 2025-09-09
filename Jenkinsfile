// =================================================================
// == JENKINSFILE FINAL POUR L'IMAGE PERSONNALISÉE ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBECONFIG_CREDENTIALS_ID = 'kubeconfig-host' 
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
                withKubeConfig([credentialsId: KUBECONFIG_CREDENTIALS_ID]) {
                    sh '''
                        echo "Vérification de la connexion au cluster..."
                        kubectl cluster-info
                        
                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml
                        
                        echo "--> Vérification du statut des déploiements..."
                        kubectl rollout status deployment/mysql-deployment
                        kubectl rollout status deployment/car-rental-deployment
                    '''
                }
            }
        }
    }
    
    post {
        always {
            cleanWs()
        }
    }
}
