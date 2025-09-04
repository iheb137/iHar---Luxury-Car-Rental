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
    stage('Smoke test') {
      steps {
        sh '''
          set -e
          kubectl port-forward -n dev svc/${APP_NAME} 8080:80 >/tmp/pf.log 2>&1 &
          PF_PID=$!
          sleep 5
          curl -f http://127.0.0.1:8080/ || (cat /tmp/pf.log; exit 1)
          kill $PF_PID || true
        '''
      }
    }
  }
}
