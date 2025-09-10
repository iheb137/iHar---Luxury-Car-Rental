pipeline {
    agent any

    environment {
        DOCKER_IMAGE_NAME = "iheb99/luxury-car-rental"
        DOCKER_IMAGE_TAG = "${env.BUILD_NUMBER}"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred'
        KUBE_DEPLOYMENT_NAME = 'ihar-deployment'
        KUBE_NAMESPACE = 'ihar'
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
                // Utilise les identifiants pour se connecter à Docker Hub via la ligne de commande
                withCredentials([usernamePassword(credentialsId: DOCKER_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    
                    echo "Construction de l'image : ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                    // Appel direct à la commande 'docker build'
                    sh "docker build -t ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} ."
                    
                    echo "Connexion à Docker Hub..."
                    // Appel direct à la commande 'docker login'
                    sh "echo ${DOCKER_PASS} | docker login -u ${DOCKER_USER} --password-stdin"
                    
                    echo "Publication de l'image..."
                    // Appel direct à la commande 'docker push'
                    sh "docker push ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                }
            }
        }

        stage('3. Deploy to Kubernetes') {
            steps {
                echo "Déploiement de la nouvelle version sur Kubernetes..."
                // Appel direct à la commande 'kubectl'
                sh "kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} app=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE}"
            }
        }
    }

    post {
        always {
            echo 'Nettoyage de l\'espace de travail...'
            cleanWs()
        }
        success {
            echo '✅ Déploiement terminé avec succès !'
        }
        failure {
            echo '❌ ÉCHEC DU DÉPLOIEMENT.'
        }
    }
}
