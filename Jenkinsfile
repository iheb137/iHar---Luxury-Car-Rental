pipeline {
    agent any

    environment {
        // Le nom de votre image sur Docker Hub.
        // Remplacez 'iheb99' par votre nom d'utilisateur Docker Hub si différent.
        DOCKER_IMAGE_NAME = "iheb99/luxury-car-rental"
        
        // Un tag unique pour chaque build, basé sur le numéro du build Jenkins.
        // Exemple : iheb99/luxury-car-rental:1, iheb99/luxury-car-rental:2, etc.
        DOCKER_IMAGE_TAG = "${env.BUILD_NUMBER}"
        
        // L'ID de vos identifiants Docker Hub dans Jenkins.
        // Vous devez créer cet identifiant dans "Manage Jenkins" > "Credentials".
        DOCKER_CREDENTIALS_ID = 'dockerhub-cred' // IMPORTANT: À créer dans Jenkins
        
        // Le nom de votre déploiement dans Kubernetes.
        KUBE_DEPLOYMENT_NAME = 'ihar-deployment'
        
        // Le namespace (espace de nom) dans Kubernetes où déployer.
        KUBE_NAMESPACE = 'ihar'
    }

    stages {
        stage('1. Checkout Code') {
            steps {
                echo 'Récupération du code source...'
                // Récupère le code depuis le dépôt Git configuré dans le job Jenkins
                checkout scm
            }
        }

        stage('2. Build Docker Image') {
            steps {
                script {
                    echo "Construction de l'image : ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG}"
                    // Construit l'image Docker en utilisant le Dockerfile à la racine du projet
                    docker.build(DOCKER_IMAGE_NAME, "--tag ${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} .")
                }
            }
        }

        stage('3. Push Docker Image') {
            steps {
                script {
                    echo "Publication de l'image sur Docker Hub..."
                    // Utilise les identifiants Docker Hub configurés dans Jenkins pour se connecter et pousser l'image
                    docker.withRegistry('https://registry.hub.docker.com', DOCKER_CREDENTIALS_ID ) {
                        docker.image(DOCKER_IMAGE_NAME).push(DOCKER_IMAGE_TAG)
                    }
                }
            }
        }

        stage('4. Deploy to Kubernetes') {
            steps {
                echo "Déploiement de la nouvelle version sur Kubernetes..."
                // Met à jour l'image du déploiement dans Kubernetes avec la nouvelle image que nous venons de pousser.
                // C'est la commande la plus sûre pour mettre à jour une application existante.
                sh "kubectl set image deployment/${KUBE_DEPLOYMENT_NAME} app=${DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_TAG} -n ${KUBE_NAMESPACE}"
            }
        }
    }

    post {
        always {
            // Cette section s'exécute toujours, que le build réussisse ou échoue.
            echo 'Nettoyage de l\'espace de travail...'
            cleanWs() // Nettoie l'espace de travail pour le prochain build
        }
        success {
            // S'exécute uniquement si toutes les étapes ont réussi.
            echo '✅ Déploiement terminé avec succès !'
        }
        failure {
            // S'exécute uniquement si une étape a échoué.
            echo '❌ ÉCHEC DU DÉPLOIEMENT. Vérification des logs...'
            // Tente de récupérer les logs des pods pour aider au débogage.
            sh "kubectl get pods -n ${KUBE_NAMESPACE}"
            sh "kubectl logs deployment/${KUBE_DEPLOYMENT_NAME} -n ${KUBE_NAMESPACE} --tail=50"
        }
    }
}
