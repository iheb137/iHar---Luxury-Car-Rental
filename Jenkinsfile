pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "iheb99/luxury-car-rental"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBERNETES_TOKEN_ID = 'kubernetes-token'
        KUBECONFIG_FILE_ID = 'kubeconfig-host'
    }

    stages {
        stage('Build & Push Docker Image') {
            steps {
                script {
                    // Désactiver BuildKit pour éviter blocages
                    sh 'DOCKER_BUILDKIT=0 docker build -t ${DOCKER_IMAGE}:latest .'

                    withCredentials([usernamePassword(
                        credentialsId: DOCKER_CREDENTIALS_ID,
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh "echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                withCredentials([
                    string(credentialsId: KUBERNETES_TOKEN_ID, variable: 'KUBE_TOKEN'),
                    file(credentialsId: KUBECONFIG_FILE_ID, variable: 'KUBECONFIG')
                ]) {
                    echo "Déploiement sur Kubernetes Desktop..."
                    sh '''
                        export KUBECONFIG=$KUBECONFIG

                        echo "--> Déploiement de MySQL..."
                        kubectl apply -f k8s/mysql.yaml

                        echo "--> Mise à jour et déploiement de l'application..."
                        sed -i "s|image: .*|image: ${DOCKER_IMAGE}:latest|g" k8s/deployment.yaml
                        kubectl apply -f k8s/deployment.yaml

                        echo "--> Exposition du service..."
                        kubectl apply -f k8s/service.yaml

                        echo "--> Vérification du déploiement..."
                        kubectl rollout status deployment/car-rental-deployment
                    '''
                }
            }
        }
    }

    post {
        always {
            echo "Pipeline terminée ✅"
            cleanWs()
        }
    }
}
