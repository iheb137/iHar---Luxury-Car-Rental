pipeline {
    agent any

    environment {
        DOCKER_IMAGE_NAME     = "iheb99/luxury-car-rental"
        DOCKER_IMAGE_TAG      = "${env.BUILD_NUMBER}"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // Assurez-vous que cet ID est correct
        KUBE_DEPLOYMENT_NAME  = 'ihar-deployment'
        KUBE_NAMESPACE        = 'ihar'
    }

    stages {
        stage('1. Checkout Code') {
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
                echo "Déploiement vers Kubernetes..."
                // On définit no_proxy pour s'assurer que kubectl ne soit pas intercepté
                sh """
                    export no_proxy=host.docker.internal
                    kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} app=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE}
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
            echo '✅ PIPELINE TERMINÉ AVEC SUCCÈS ! Mission accomplie !'
        }
        failure {
            echo '❌ ÉCHEC DU DÉPLOIEMENT.'
        }
    }
}
