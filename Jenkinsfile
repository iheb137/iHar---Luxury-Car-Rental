pipeline {
  agent any
  environment {
    APP_NAME = 'car-rental'
    K8S_NAMESPACE = 'dev'
  }

  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Docker Build + Load into Minikube') {
      steps {
        sh '''
          set -e
          IMAGE=${APP_NAME}:$(git rev-parse --short HEAD)
          echo "==> Build image: $IMAGE"
          docker build -t $IMAGE .
          minikube image load $IMAGE
        '''
      }
    }

    stage('Deploy to Minikube') {
      steps {
        sh '''
          set -e
          IMAGE=${APP_NAME}:$(git rev-parse --short HEAD)
          kubectl create namespace ${K8S_NAMESPACE} || true
          sed "s|IMAGE_PLACEHOLDER|${IMAGE}|g" k8s/deployment.yaml | kubectl apply -n ${K8S_NAMESPACE} -f -
          kubectl apply -n ${K8S_NAMESPACE} -f k8s/service.yaml
          kubectl rollout status deployment/${APP_NAME} -n ${K8S_NAMESPACE} --timeout=120s
        '''
      }
    }

    stage('Smoke Test') {
      steps {
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

  post {
    always {
      echo "Pipeline terminé avec statut: ${currentBuild.currentResult}"
    }
  }
}
