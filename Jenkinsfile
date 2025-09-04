pipeline {
    agent any

    environment {
        // Credential SSH configuré dans Jenkins
        SSH_CRED = 'ssh-cred'
        DEPLOY_USER = 'iheb'            // Ton utilisateur sur le serveur LAMP
        DEPLOY_HOST = 'IP_OU_HOSTNAME'  // Remplace par l'IP ou le nom de ton serveur LAMP
        DEPLOY_PATH = '/var/www/html/laap' // Chemin sur le serveur LAMP où copier les fichiers
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'master',
                    url: 'git@github.com:TON_UTILISATEUR/TON_REPO.git'
            }
        }

        stage('Build') {
            steps {
                echo 'Pas de build nécessaire pour HTML/CSS/JS/PHP'
            }
        }

        stage('Deploy') {
            steps {
                echo 'Déploiement sur le serveur LAMP via SSH'
                sshagent([SSH_CRED]) {
                    sh """
                        rsync -avz --delete ./ ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}
                    """
                }
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi ✅'
        }
        failure {
            echo 'Échec du déploiement ❌'
        }
    }
}
