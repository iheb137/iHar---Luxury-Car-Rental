pipeline {
  agent any
  environment {
    APP_NAME = 'car-rental'
    K8S_NAMESPACE = 'dev'
    DOCKERHUB_USER = 'iheb99'   // adapte avec ton vrai username DockerHub
  }

  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Docker Build & Push') {
      steps {
        script {
          def IMAGE_TAG = "${APP_NAME}:${env.BUILD_NUMBER}"
          def IMAGE_REMOTE = "${DOCKERHUB_USER}/${APP_NAME}:${env.BUILD_NUMBER}"

          sh """
            echo "==> Build local image: ${IMAGE_TAG}"
            docker build -t ${IMAGE_TAG} .
            
            echo "==> Tag image for DockerHub"
            docker tag ${IMAGE_TAG} ${IMAGE_REMOTE}
          """

          withCredentials([usernamePassword(credentialsId: 'dockerhub-cred', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
            sh """
              echo "==> Login DockerHub"
              echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin
              
              echo "==> Push image to DockerHub"
              docker push ${IMAGE_REMOTE}
              
              docker logout
            """
          }
        }
      }
    }

    stage('Deploy to Minikube') {
      steps {
        withKubeConfig([credentialsId: 'kubeconfig-dev']) {
          sh '''
            set -e
            IMAGE_REMOTE='iheb99/car-rental:'${BUILD_NUMBER}

            echo "==> Deploy image ${IMAGE_REMOTE} to Minikube"
            kubectl create namespace ${K8S_NAMESPACE} || true
            sed "s|IMAGE_PLACEHOLDER|${IMAGE_REMOTE}|g" k8s/deployment.yaml | kubectl apply -n ${K8S_NAMESPACE} -f -
            kubectl apply -n ${K8S_NAMESPACE} -f k8s/service.yaml

            echo "==> Wait for rollout"
            kubectl rollout status deployment/${APP_NAME} -n ${K8S_NAMESPACE} --timeout=120s
          '''
        }
      }
    }

    stage('Smoke Test') {
      steps {
        withKubeConfig([credentialsId: 'kubeconfig-dev']) {
          sh '''
            set -e
            NODE_IP=$(minikube ip)
            URL="http://$NODE_IP:30080/"
            echo "Testing app at $URL"
            curl -f $URL || (echo "❌ App not reachable"; exit 1)
            echo "✅ App reachable at $URL"
          '''
        }
      }
    }
  }

  post {
    always {
      echo "Pipeline terminé avec statut: ${currentBuild.currentResult}"
    }
  }
}
