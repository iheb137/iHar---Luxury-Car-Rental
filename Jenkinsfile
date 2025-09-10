pipeline {
    agent any

    environment {
        DOCKER_IMAGE_NAME     = "iheb99/luxury-car-rental"
        DOCKER_IMAGE_TAG      = "${env.BUILD_NUMBER}"
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // Assurez-vous que cet ID est correct
        KUBE_DEPLOYMENT_NAME  = 'ihar-deployment'
        KUBE_NAMESPACE        = 'ihar'
        // On définit le chemin du fichier de config DANS le conteneur
        KUBECONFIG_PATH       = '/home/jenkins/.kube/config'
        // On définit l'URL du serveur à contacter
        KUBE_SERVER_URL       = 'https://host.docker.internal:6443'
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
                    sh "docker build -t ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} ."
                    sh "echo ${DOCKER_PASS} | docker login -u ${DOCKER_USER} --password-stdin"
                    sh "docker push ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                }
            }
        }

        stage('3. Deploy to Kubernetes') {
            steps {
                echo "Déploiement vers Kubernetes..."
                // Approche "ceinture et bretelles" : on spécifie TOUT.
                // 1. On dit où est le fichier de config (pour les certificats).
                // 2. On dit où est le serveur (pour éviter le proxy).
                // 3. On désactive la vérification TLS (car le nom ne correspond pas).
                sh """
                    kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} app=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE} \
                    --kubeconfig=${KUBECONFIG_PATH} \
                    --server=${KUBE_SERVER_URL} \
                    --insecure-skip-tls-verify=true
                """
            }
        }
    }

    post {
        always {
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
