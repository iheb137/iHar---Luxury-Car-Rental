pipeline {
    agent any

    environment {
        // Nom de votre image sur Docker Hub
        DOCKER_IMAGE_NAME     = "iheb99/luxury-car-rental"
        
        // Tag unique pour chaque build, basé sur le numéro du build Jenkins
        DOCKER_IMAGE_TAG      = "${env.BUILD_NUMBER}"
        
        // L'ID de vos identifiants Docker Hub dans Jenkins
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' 
        
        // Le nom de votre déploiement dans Kubernetes
        KUBE_DEPLOYMENT_NAME  = 'ihar-deployment'
        
        // Le namespace (espace de nom) dans Kubernetes où déployer
        KUBE_NAMESPACE        = 'ihar'
        
        // Le chemin absolu vers le fichier de config DANS le conteneur
        KUBECONFIG_PATH       = '/kube-config/config'
        
        // L'URL du serveur Kubernetes à contacter depuis le conteneur
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
                // Utilisation du nom de conteneur correct 'luxury-car-rental'
                sh """
                    kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} luxury-car-rental=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE} \
                    --kubeconfig=${KUBECONFIG_PATH} \
                    --server=${KUBE_SERVER_URL} \
                    --insecure-skip-tls-verify=true
                """
            }
        }
    }

    post {
        // Envoi d'un email à la fin du build, quel que soit le résultat
        always {
            echo 'Envoi de la notification par email...'
            mail to: 'iheb.saafigroup@tek-up.de', // Adresse email configurée
                 subject: "Build ${currentBuild.fullDisplayName}: ${currentBuild.currentResult}",
                 body: """<p>Le build <b>${env.JOB_NAME} #${env.BUILD_NUMBER}</b> s'est terminé avec le statut : <b>${currentBuild.currentResult}</b>.</p>
                          <p>Consultez les logs ici : <a href='${env.BUILD_URL}'>${env.BUILD_URL}</a></p>"""
        }
        success {
            echo '✅ PIPELINE CI/CD TERMINÉ AVEC SUCCÈS !'
        }
        failure {
            echo '❌ ÉCHEC DU PIPELINE. Vérifiez les logs ci-dessus.'
        }
    }
}
