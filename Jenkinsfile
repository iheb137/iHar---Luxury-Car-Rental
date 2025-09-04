pipeline {
    agent any

    environment {
        GIT_REPO = 'git@github.com:iheb137/iHar---Luxury-Car-Rental.git'
        BRANCH = 'master'
        DEPLOY_USER = 'saafi'
        DEPLOY_HOST = '10.167.80.144'
        DEPLOY_PATH = '/var/www/iHar'
        SSH_CREDENTIALS = 'jenkins-ssh-key' // Le credential SSH que tu as ajouté dans Jenkins
    }

    stages {
        stage('Clone Repository') {
            steps {
                git branch: "${BRANCH}", url: "${GIT_REPO}"
            }
        }

        stage('Deploy to Server') {
            steps {
                sshagent([SSH_CREDENTIALS]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} 'mkdir -p ${DEPLOY_PATH}'
                        rsync -avz --delete ./ ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/
                        ssh ${DEPLOY_USER}@${DEPLOY_HOST} 'cd ${DEPLOY_PATH} && composer install || echo "Composer not found"'
                    """
                }
            }
        }
    }

    post {
        success {
            echo 'Deployment succeeded!'
        }
        failure {
            echo 'Deployment failed!'
        }
    }
}
