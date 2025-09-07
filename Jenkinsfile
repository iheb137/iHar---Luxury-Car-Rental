pipeline {
    agent any

    environment {
        GIT_CREDENTIALS = 'iheb'          // clé SSH GitHub que tu as ajoutée
        DOCKER_CREDENTIALS = 'dockerhub-cred' // login/pwd DockerHub
        DOCKER_IMAGE = "iheb137/luxury-car-rental"
    }

    stages {
        stage('Checkout') {
            steps {
                git branch: 'master',
                    url: 'git@github.com:iheb137/iHar---Luxury-Car-Rental.git',
                    credentialsId: "${GIT_CREDENTIALS}"
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    sh 'docker build -t $DOCKER_IMAGE:latest .'
                }
            }
        }

        stage('Login & Push to DockerHub') {
            steps {
                withCredentials([usernamePassword(credentialsId: "${DOCKER_CREDENTIALS}", usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    sh """
                        echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin
                        docker push $DOCKER_IMAGE:latest
                    """
                }
            }
        }

        stage('Deploy to Minikube') {
            steps {
                script {
                    sh '''
                        kubectl config use-context minikube
                        kubectl apply -f k8s/deployment.yaml
                        kubectl apply -f k8s/service.yaml
                    '''
                }
            }
        }
    }
}
