pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "car-rental:${GIT_COMMIT}"
    }

    stages {
        stage('Checkout') {
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', branch: 'master'
            }
        }

        stage('Build Docker Image') {
            steps {
                // Construction de l'image Docker localement sur l'hôte
                sh 'docker build -t $DOCKER_IMAGE .'
            }
        }

        stage('Push Docker Image (Optional)') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'dockerhub-cred', 
                                                  usernameVariable: 'DOCKER_USER', 
                                                  passwordVariable: 'DOCKER_PASS')]) {
                    sh '''
                        echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin
                        docker tag $DOCKER_IMAGE $DOCKER_USER/$DOCKER_IMAGE
                        docker push $DOCKER_USER/$DOCKER_IMAGE
                    '''
                }
            }
        }

        stage('Deploy to Minikube') {
            steps {
                withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                    sh 'kubectl apply -f deployment.yaml'
                }
            }
        }

        stage('Smoke Test') {
            steps {
                sh 'curl -f http://localhost:8080 || exit 1'
            }
        }
    }

    post {
        always {
            echo '✅ Pipeline terminé !'
        }
        failure {
            echo '❌ Quelque chose a échoué dans le pipeline !'
        }
    }
}
