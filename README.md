Proyecto de pasarela de pagos  para la empresa  rincon creativo
Para inicializar el proyecto se debe contar con php y composer minimamente
El primer comando que se debe ejecutar en la terminal para instalar las dependencias es:
        composer install 
El archivo .env para los  parametros de environment no deberia estar listo, puedes crearlo con:
        cp .env.example .env
y por ultimo, se debe generar una clave para la aplicacion con el siguiente comando:
        php artisan key:generate

En el .env debes copiar lo siguiente:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

Reemplazando los valores por los reales que se compartiran de manera privada por el grupo interno de whatsapp

Luego, se realizan las migraciones de la base de datos si es que hubiera alguna con: 
            php artisan migrate

Comando para ejecutar el servidor:
            php artisan serve 

la aplicacion deberia correr en http://localhost:8000

