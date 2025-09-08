// =================================================================
// == JENKINSFILE FINAL (AVEC CREDENTIALS KUBERNETES SÉPARÉS) ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        // On définit les IDs de nos 3 nouveaux credentials
        MINIKUBE_CA_CERT_ID = 'minikube-ca-cert'
        MINIKUBE_CLIENT_CERT_ID = 'minikube-client-cert'
        MINIKUBE_CLIENT_KEY_ID = 'minikube-client-key'
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
                // withCredentials va charger nos 3 fichiers secrets et stocker leur
                // chemin dans les variables CA_CERT, CLIENT_CERT, et CLIENT_KEY.
                withCredentials([
                    file(credentialsId: MINIKUBE_CA_CERT_ID, variable: 'CA_CERT'),
                    file(credentialsId: MINIKUBE_CLIENT_CERT_ID, variable: 'CLIENT_CERT'),
                    file(credentialsId: MINIKUBE_CLIENT_KEY_ID, variable: 'CLIENT_KEY')
                ]) {
                    echo "Déploiement sur le cluster (authentifié via certificats séparés)..."
                    sh '''
                        # On utilise 'minikube' comme nom d'hôte car ils sont sur le même réseau Docker.
                        export KUBESERVER="https://minikube:8443"

                        # Les variables $CA_CERT, $CLIENT_CERT, $CLIENT_KEY sont fournies par withCredentials.

                        echo "--> Déploiement de MySQL..."
                        kubectl apply --server=$KUBESERVER --certificate-authority=$CA_CERT --client-key=$CLIENT_KEY --client-certificate=$CLIENT_CERT -f k8s/mysql.yaml
                        
                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply --server=$KUBESERVER --certificate-authority=$CA_CERT --client-key=$CLIENT_KEY --client-certificate=$CLIENT_CERT -f k8s/deployment.yaml
                        
                        echo "--> Exposition du service..."
                        kubectl apply --server=$KUBESERVER --certificate-authority=$CA_CERT --client-key=$CLIENT_KEY --client-certificate=$CLIENT_CERT -f k8s/service.yaml
                        
                        echo "--> Vérification du statut du déploiement..."
                        kubectl rollout status --server=$KUBESERVER --certificate-authority=$CA_CERT --client-key=$CLIENT_KEY --client-certificate=$CLIENT_CERT deployment/car-rental-deployment
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
