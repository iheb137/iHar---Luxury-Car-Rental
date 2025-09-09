// =================================================================
// == JENKINSFILE FINAL (POUR JENKINS SUR KUBERNETES) ==
// =================================================================

pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
<<<<<<< HEAD
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
=======
        DOCKER_CREDENTIALS_ID = "dockerhub-cred"
        KUBERNETES_TOKEN_ID = "kubernetes-token"
        KUBECONFIG_FILE_ID = "kubeconfig-host"
>>>>>>> 8319482763cc763e80dce3e47a97b9a8490921f5
    }

    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', branch: 'master'
            }
        }

        stage('Build & Push Docker Image') {
            steps {
                script {
<<<<<<< HEAD
                    sh "docker build -t ${DOCKER_IMAGE}:latest ."
                    withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
=======
                    // Activer BuildKit pour plus de performance
                    sh 'DOCKER_BUILDKIT=1 docker build -t ${DOCKER_IMAGE}:latest .'

                    withCredentials([usernamePassword(
                        credentialsId: DOCKER_CREDENTIALS_ID,
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh '''
                            echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin
                            docker push ${DOCKER_IMAGE}:latest
                        '''
>>>>>>> 8319482763cc763e80dce3e47a97b9a8490921f5
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
<<<<<<< HEAD
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
=======
                withCredentials([
                    string(credentialsId: KUBERNETES_TOKEN_ID, variable: 'KUBE_TOKEN'),
                    file(credentialsId: KUBECONFIG_FILE_ID, variable: 'KUBECONFIG_FILE')
                ]) {
                    script {
                        echo "🚀 Déploiement sur Kubernetes Desktop..."

                        sh '''
                            export KUBECONFIG=$KUBECONFIG_FILE

                            echo "--> Déploiement MySQL..."
                            kubectl apply -f k8s/mysql.yaml

                            echo "--> Mise à jour de l'image de l'application..."
                            sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml

                            kubectl apply -f k8s/deployment.yaml
                            kubectl apply -f k8s/service.yaml

                            echo "--> Vérification du déploiement..."
                            kubectl rollout status deployment/car-rental-deployment
                        '''
                    }
                }
>>>>>>> 8319482763cc763e80dce3e47a97b9a8490921f5
            }
        }
    }

    post {
        always {
            cleanWs()
        }
    }
}