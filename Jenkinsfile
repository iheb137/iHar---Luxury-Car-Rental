pipeline {
    agent any

    environment {
        BRANCH_NAME = 'master'
        SERVER_USER = 'ton_user'           // utilisateur SSH sur le serveur
        SERVER_HOST = 'ton_serveur_ip'     // IP ou domaine du serveur
        SERVER_PATH = '/var/www/ihar'      // chemin sur le serveur
        SSH_CREDENTIALS = 'ssh-cred'       // ID Jenkins de tes credentials SSH
    }

    stages {

        stage('Checkout') {
            steps {
                git branch: "${BRANCH_NAME}", url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git'
            }
        }

        stage('Test PHP/JS') {
            steps {
                // Si tu as des tests PHPUnit ou JS, ajoute ici
                echo "✅ Étape de test (à personnaliser si tu as des tests)"
            }
        }

        stage('Deploy to Server') {
            steps {
                script {
                    sshagent(['ssh-credentials-id']) {
                        sh """
                        rsync -avz --delete ./ ${SERVER_USER}@${SERVER_HOST}:${SERVER_PATH}
                        """
                    }
                }
            }
        }
    }

    post {
        always {
            cleanWs()
        }
        success {
            echo "✅ Déploiement sur LAMP réussi !"
        }
        failure {
            echo "❌ Échec du déploiement."
        }
    }
}
