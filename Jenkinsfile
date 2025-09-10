pipeline {
    agent any

    environment {
        DOCKER_IMAGE_NAME = "iheb99/luxury-car-rental"
        DOCKER_IMAGE_TAG = "${env.BUILD_NUMBER}"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBE_DEPLOYMENT_NAME = 'ihar-deployment'
        KUBE_NAMESPACE = 'ihar'
        // AJOUT DE L'ADRESSE DU SERVEUR KUBERNETES
        KUBE_SERVER_URL = 'https://host.docker.internal:6443'
    }

    stages {
        stage('1. Checkout Code' ) {
            steps {
                echo 'Récupération du code source...'
                checkout scm
            }
        }

        stage('2. Build and Push Docker Image') {
            steps {
                withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    echo "Construction de l'image : ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                    sh "docker build -t ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} ."
                    
                    echo "Connexion à Docker Hub..."
                    sh "echo ${DOCKER_PASS} | docker login -u ${DOCKER_USER} --password-stdin"
                    
                    echo "Publication de l'image..."
                    sh "docker push ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                }
            }
        }

        stage('3. Deploy to Kubernetes') {
            steps {
                echo "Déploiement vers le serveur Kubernetes à l'adresse ${KUBE_SERVER_URL}..."
                // MODIFICATION CI-DESSOUS : Ajout de --server et --insecure-skip-tls-verify
                sh """
                    kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} app=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE} \
                    --server=${KUBE_SERVER_URL} \
                    --insecure-skip-tls-verify=true
                """
            }
        }
    }

    post {
        always {
            echo 'Nettoyage de l\'espace de travail...'
            cleanWs()
        }
        success {
            echo '✅ PIPELINE TERMINÉ AVEC SUCCÈS !'
        }
        failure {
            echo '❌ ÉCHEC DU DÉPLOIEMENT.'
        }
    }
}
