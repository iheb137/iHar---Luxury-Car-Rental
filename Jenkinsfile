pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                sshagent(credentials: ['ssh-cred']) {
                    git branch: 'master',
                        url: 'git@github.com:iheb137/iHar---Luxury-Car-Rental.git'
                }
            }
        }

        stage('Build') {
            steps {
                echo 'Build step - ici tu peux lancer composer, npm, etc.'
            }
        }

        stage('Deploy') {
            steps {
                echo 'Deploy step - ici tu peux mettre rsync ou scp vers ton serveur'
            }
        }
    }
}
