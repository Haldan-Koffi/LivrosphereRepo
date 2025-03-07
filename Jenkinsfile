pipeline {
    agent any

    environment {
        GIT_REPO = "https://github.com/Haldan-Koffi/LivrosphereRepo.git"
        GIT_BRANCH = "main"
        DEPLOY_DIR = "web004"
    }

    stages {
        stage('Cloner le dépôt') {
            steps {
                sh "rm -rf ${DEPLOY_DIR}" // Nettoyage du précédent build
                sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${DEPLOY_DIR}"
            }
        }

        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --optimize-autoloader'
                }
            }
        }

        stage('Configuration de l\'environnement') {
            steps {
                script {
                    def envLocal = """
                    APP_ENV=prod
                    APP_DEBUG=1
                    DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4
                    """.stripIndent()

                    writeFile file: "${DEPLOY_DIR}/.env.local", text: envLocal
                }
            }
        }
        

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod'
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
                }
            }
        }
        // Nouveau stage pour créer la base de test
        stage('Création de la base de test') {
            steps {
                dir("${DEPLOY_DIR}") {
                    // Assure-toi que le fichier phpunit.xml.dist (ou .env.test) configure DATABASE_URL pour pointer sur "web004_test"
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=test'
                    // sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=test'
                }
            }
        }

        stage('Exécution des tests') {
            steps {
                dir("${DEPLOY_DIR}") {
            // Lancement de PHPUnit avec le fichier de config .dist
                    sh 'vendor/bin/phpunit --configuration=phpunit.xml.dist'
            
            // Si tu veux générer un rapport JUnit, tu peux faire :
            // sh 'vendor/bin/phpunit --configuration=phpunit.xml.dist --log-junit junit.xml'
                }
            }
        }

        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:clear --env=prod'
                    sh 'php bin/console cache:warmup'
                }
            }
        }

        stage('Déploiement') {
            steps {
                sh "rm -rf /var/www/html/${DEPLOY_DIR}" // Supprime le dossier de destination
                sh "mkdir /var/www/html/${DEPLOY_DIR}" // Recréé le dossier de destination
                sh "cp -rT ${DEPLOY_DIR} /var/www/html/${DEPLOY_DIR}"
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/var"

                        // 3. Ajustement des permissions
                //   - On autorise aussi l'écriture sur le dossier uploads
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/public/uploads"
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi !'
        }
        failure {
            echo 'Erreur lors du déploiement.'
        }
    }
}