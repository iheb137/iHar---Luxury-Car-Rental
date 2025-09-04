pipeline {
  agent any
  environment {
    APP_NAME = 'car-rental'
    K8S_NAMESPACE = 'dev'

    # Docker TLS pour Minikube
    DOCKER_HOST = 'tcp://127.0.0.1:52440'
    DOCKER_TLS_VERIFY = '1'
    DOCKER_CERT_PATH = '/var/jenkins_home/.minikube/certs'
  }

  stages {
    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Build Docker image on Minikube') {
      steps {
        sh '''
          set -e
          IMAGE=${APP_NAME}:$(git rev-parse --short HEAD)
          echo "==> Building image on Minikube Docker daemon: $IMAGE"

          docker --tlsverify \
            --tlscacert=$DOCKER_CERT_PATH/ca.pem \
            --tlscert=$DOCKER_CERT_PATH/cert.pem \
            --tlskey=$DOCKER_CERT_PATH/key.pem \
            -H $DOCKER_HOST build -t $IMAGE .
          
          echo "==> Images on Minikube:"
          docker --tlsverify \
            --tlscacert=$DOCKER_CERT_PATH/ca.pem \
            --tlscert=$DOCKER_CERT_PATH/cert.pem \
            --tlskey=$DOCKER_CERT_PATH/key.pem \
            -H $DOCKER_HOST images | grep ${APP_NAME} || true
        '''
      }
    }

    stage('Deploy to Minikube') {
      steps {
        withCredentials([file(credentialsId: 'kubeconfig-dev', variable: 'KUBECONFIG')]) {
          sh '''
            set -e
            IMAGE=${APP_NAME}:$(git rev-parse --short HEAD)
            echo "==> Deploying $IMAGE to Kubernetes namespace ${K8S_NAMESPACE}"

            kubectl --kubeconfig=$KUBECONFIG create namespace ${K8S_NAMESPACE} || true
            sed "s|IMAGE_PLACEHOLDER|${IMAGE}|g" k8s/deployment.yaml | kubectl --kubeconfig=$KUBECONFIG apply -n ${K8S_NAMESPACE} -f -
            kubectl --kubeconfig=$KUBECONFIG apply -n ${K8S_NAMESPACE} -f k8s/service.yaml
            kubectl --kubeconfig=$KUBECONFIG rollout status deployment/${APP_NAME} -n ${K8S_NAMESPACE} --timeout=120s
          '''
        }
      }
    }

    stage('Smoke Test') {
      steps {
        withCredentials([file(credentialsId: 'kubeconfig-dev', variable: 'KUBECONFIG')]) {
          sh '''
            set -e
            NODE_IP=$(kubectl --kubeconfig=$KUBECONFIG get nodes -o jsonpath='{.items[0].status.addresses[?(@.type=="InternalIP")].address}')
            URL="http://$NODE_IP:30080/"
            echo "Testing app at $URL"
            curl -f $URL || (echo "❌ App not reachable"; kubectl --kubeconfig=$KUBECONFIG get pods -n ${K8S_NAMESPACE}; kubectl --kubeconfig=$KUBECONFIG logs -l app=${APP_NAME} -n ${K8S_NAMESPACE} --tail=200; exit 1)
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
