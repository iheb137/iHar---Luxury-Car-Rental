pipeline {
    agent none

    environment {
        IMAGE_NAME = "car-rental"
    }

    stages {

        stage('Checkout') {
            agent any
            steps {
                git url: 'https://github.com/iheb137/iHar---Luxury-Car-Rental.git', branch: 'master'
            }
        }

        stage('Build Docker Image') {
            agent {
                docker {
                    image 'docker:20.10.24'
                    args '-v /var/run/docker.sock:/var/run/docker.sock -v $PWD:/workspace -w /workspace'
                }
            }
            steps {
                script {
                    // Build Docker image
                    sh "docker build -t ${IMAGE_NAME}:${GIT_COMMIT} ."
                }
            }
        }

        stage('Push Docker Image to DockerHub') {
            agent any
            steps {
                withCredentials([usernamePassword(credentialsId: 'dockerhub-cred', 
                                                 usernameVariable: 'DOCKER_USERNAME', 
                                                 passwordVariable: 'DOCKER_PASSWORD')]) {
                    sh 'docker login -u $DOCKER_USERNAME -p $DOCKER_PASSWORD'
                    sh "docker tag ${IMAGE_NAME}:${GIT_COMMIT} $DOCKER_USERNAME/${IMAGE_NAME}:${GIT_COMMIT}"
                    sh "docker push $DOCKER_USERNAME/${IMAGE_NAME}:${GIT_COMMIT}"
                }
            }
        }

        stage('Deploy to Minikube') {
            agent any
            steps {
                withCredentials([file(credentialsId: 'minikube-kubeconfig', variable: 'KUBECONFIG')]) {
                    // Apply Kubernetes deployment
                    sh 'kubectl apply -f deployment.yaml'
                }
            }
        }

        stage('Smoke Test') {
            agent any
            steps {
                sh 'curl http://localhost:8080'
            }
        }
    }

    post {
        always {
            echo '✅ Pipeline terminé !'
        }
        success {
            echo '🎉 Déploiement réussi !'
        }
        failure {
            echo '❌ Échec du pipeline.'
        }
    }
}
