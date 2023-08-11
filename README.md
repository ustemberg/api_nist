<h1 align="center">
  <p align="center">API REST: NIST - Mercado Libre</p>
</h1>
 <div align="center">
     <img src = "https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/NIST_logo.svg/1280px-NIST_logo.svg.png" alt = 'nist' style = 'width:18%;'>&nbsp;&nbsp;&nbsp;
     <img src = "https://logodownload.org/wp-content/uploads/2018/10/mercado-libre-logo.png" alt = 'meli' style = 'width:20%;'>
 </div><br><br>

## Introducción
<div class = "container">
    Documentación de la API REST cuya información es extraida del <b>NIST</b> (National Institute of Standards and Technology), simulando un posible feature, útil para gestionar las vulnerabilidades existentes que pueden atentar en la seguridad de los diferentes sistemas de <b>Mercado Libre</b>.
</div>

## Arquitectura
<div class="container">
    La API fue desarrollada mediante el uso de <b>PHP</b> y el framework <a href = "https://laravel.com/docs/10.x" target = '_blank'><b>Laravel</b></a>, uno de los más utilizados en el desarrollo backend. Este se basa en un     
    modelo <b>MVC</b> (modelo-vista-controlador) aunque para este desarrollo fue practicamente todo hecho en el controlador. Éste es el encargado de recibir las peticiones      a partir de las rutas especificadas (en este caso, las de los endpoints de la API) y devolver la información al usuario, sea de forma directa o vía el modelo, que representa una fila en la 
    tabla de la base de datos, donde cada fila es representada como un objeto de la clase/modelo. Es decir, el controlador en algunas funciones se comunica con el
    modelo para traer o guardar información en la DB. <br>Se utilizó <b>MySQL</b> para la DB. <br><br>
    El testeo de la API se dio mediante la ruta local que nos "sirve" Laravel, <b>http://127.0.0.1:8000</b> y se usó Postman para enviar y recibir solicitudes. <br><br>
    A su vez, se trabajó con <a href = "https://www.laravelia.com/post/laravel-10-jwt-complete-api-authentication-tutorial" target = '_blank'><b>Laravel 10 JWT</b></a>, uno de los sistemas más potentes para <b>autenticación de APIs</b> con Laravel. De esta forma, trabajar con la API requiere un usuario, algo que resulta crucial a la hora de acceder a información que no queremos que sea accedible por cualquiera, necesitando un Token para consultar cada endpoint, el cual se obtiene al loguearse, para lo cual primero hay que registrarse. En este caso cualquiera puede registrarse y acceder, pero naturalmente esos usuarios saldrían de una base de datos. De esta forma, hay varias rutas adicionales, que son las que nos permiten hacer <i>logout</i> y cerrar la sesión, o hacer <i>refresh</i> y modificar nuestro token.<br><br>
En conclusión acerca de la arquitectura, si bien no se desarrolló con Go, Docker o servicios de Cloud, se buscó que el sistema sea lo más robusto posible, y se puede asegurar que es completamente adaptable a esas tecnologías.
</div>

## Ejecución
- **Clonar repositorio**
```bash
git clone https://github.com/ustemberg/api_nist_meli.git
```
- **Acceder a la carpeta con los archivos**
```bash
cd api_nist_meli
```
- **Instalar las dependencias correspondientes. En caso de recibir un error, descargar composer desde la web en primer lugar y luego retomar este proceso. composer es un poderoso gestor de paquetes para PHP.**
```bash
composer install
```
- **Configurar el Archivo .env, para empezar, copiar el .env.example al .env**
```bash
cp .env.example .env
``` 
- **Ahora es necesario configurar el entorno de base de datos. Si se cuenta con un Xampp instalado, por ejemplo, alcanza con iniciar el MySQL y crear una base de datos con el nombre api_nist (o el que se desee) desde el motor de PHPMyAdmin. Así quedaría configurado el apartado de base de datos del archivo .env:**
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_nist
DB_USERNAME=root
DB_PASSWORD=
```
- **Otra opción en este punto es crear en la carpeta database un archivo llamado database.sqlite, vacío, y luego, en el archivo .env, solo quedarse con esta línea:**
```bash
DB_CONNECTION=sqlite
```  
- **Generar Clave de Aplicación**
```bash
php artisan key:generate
```
- **Hacer la migración para crear la tabla que se utiliza para la API**
```bash
php artisan migrate
```
- **Ejecutar en el servidor local. Esto es crucial, si no está en ejecución la app no se puede probar en la plataforma que se use para testear la API.**
```bash
php artisan serve
```
- **Abrir Postman o la plataforma de uso de APIs preferida. En el siguiente item se explica mejor qué hace la aplicación y como probarla. En principio, hay que pegar (en el Postman o la plataforma que se use) la ruta http://127.0.0.1:8000/api que es de donde salen todos los endpoints.**

## Funcionamiento - Archivos importantes
En el archivo <b>routes/api.php</b>, se encuentran todas las rutas con los endpoints de la API sumado a las utilizadas para la autenticación explicada anteriormente.<br>
Como en principio es necesario autenticarse, las rutas del sistema de auth son:
```php
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
});
```
Partiendo de la ruta base mencionada anteriormente se debe comenzar con el <i>/register</i> (la ruta completa sería <b>http://127.0.0.1:8000/api/register</b>).  Hay que realizar la petición <b>post</b> con los parámetros (que van en Body, en Postman) 'name', 'email' y 'password'. Una vez creada la cuenta, loguearse en el endpoint de <i>/login</i> con los parámetros 'email' y 'password'. <br><br>
<i>login</i> en Postman:<br><br>
![image](https://github.com/ustemberg/api_nist_meli/assets/103837698/f5aad2bb-3c43-42c1-b486-699c7ca9c8c6)
<br> Si el usuario se ingreso correctamente, eso brindará un token en el JSON de respuesta: <br><br> 
![image](https://github.com/ustemberg/api_nist_meli/assets/103837698/a9611c9b-40cb-4beb-8bde-b5fa9509fa55)
<br> Es obligación copiarlo, y pegarlo en el parámetro <b>Token</b> en la parte de Authorization de Postman. Con ese Token se podrá efectivamente, ya logueado, utilizar la API. <br>Se puede hacer la prueba de acceder sin login o sin token válido a los endpoints que aparecen a continuación, pero la respuestá sera un <b>No autorizado</b>. Todo el código asociado a los endpoints de autenticación se encuentra en el controlador <b>AuthController</b> en <i>app/Http/Controllers</i><br><br>
Mientras que las demás rutas son efectivamente los endpoints solicitados para la API REST. Las primeras 4 representan las solicitadas en la consigna, todas de tipo GET (en este caso, no necesitan ningún parámetro) y la última, es la ruta a utilizar para guardar vulnerabilidades corregidas, por eso es de tipo POST:
```php
Route::controller(FixedController::class)->group(function(){
    Route::get('/all', 'all');
    Route::get('/all_by_severity', 'all_by_severity');
    Route::get('/fixed', 'fixed');
    Route::get('/all_by_severity_notfixed', 'all_by_severity_notfixed');
    Route::post('/fix', 'fix');
});
```
<b>*RECORDAR EL USO DEL TOKEN PARA PODER LLEVAR A CABO TODAS LAS PETICIONES*</b><br>
Veamos cada endpoint. El código de todos ellos se encuentra en el controlador <b>FixedController</b>, dentro de <i>app/Http/Controllers</i>.

- **http://127.0.0.1:8000/api/all**<br>
  Es el endpoint que lista <b>todas las vulnerabilidades</b>. Dado que la información sale directamente de la API del NIST, la idea fue no almacenar en la base de datos propia las vulnerabilidades en sí, ya que es información "estática". La única tabla que se creó es la tabla <b>'fixeds'</b> (si bien la palabra no existe, por cuestiones de Laravel fue necesario) donde se almacenan los CVE-IDS de las vulnerabilidades corregidas. Eso es lo único realmente "dinámico". Si bien pudo resultar en un código más largo que compacto, fue por esa razón de lo estático y dinámico.<br><br>
Por lo tanto, se puede ver en esta y en el resto de funciones para los endpoints que se traen las primeras 1000 vulnerabilidades de la API del NIST. En caso de querer cambiarlo, se puede entrar a la función <i>bring_vulnerabilities()</i> en el controlador mencionado, y modificar la cantidad que trae por petición. Luego, en lugar de listar todos los campos que ofrece el NIST, se mantuvieron algunos, el <b>ID del CVE</b>, <b>la severidad</b>, la <b>descripción</b> de la vulnerabilidad, y los puntajes de impacto y explotabilidad, como datos de color. Estos campos son los que traen todos los endpoints.<br><br>

- **http://127.0.0.1:8000/api/all_by_severity**<br>
  Es el endpoint que lista todas las vulnerabilidades <b>sumarizadas por severidad</b>. En esencia, el comportamiento es igual al endpoint <i>all</i>, con la diferencia que en lugar de devolver todas las vulnerabilidades una debajo de la otra, aparecen primero todas las vulnerabilidades de severidad <b>"HIGH"</b>, luego <b>"MEDIUM"</b> y por último <b>"LOW"</b>. <br><br>
  
- **http://127.0.0.1:8000/api/fixed**<br>
  Es el endpoint que lista <b>todas las vulnerabilidades corregidas</b>. Es un <i>SELECT ALL</i> de la tabla <b>fixeds</b> creada. Cuando entra en juego la base de datos, es porque a su vez está en el medio el modelo, en este caso llamado Fixed,  que se encuentra guardado en <i>app/Models</i>. En objetos del tipo de la clase se guardan todos los registros que devuelve la tabla.<br>
  Nos trae los IDs que como usuarios autenticados fuimos guardando para marcar que la vulnerabilidad de ese CVE-ID <b>ya fue corregida.</b><br><br>
  
- **http://127.0.0.1:8000/api/all_by_severity_notfixed**<br>
  Es el endpoint que lista todas las vulnerabilidades <b>sumarizadas por severidad exceptuando las corregidas</b>. <br>
  Este endpoint se retroalimenta directamente de los últimos dos, tomando todas las vulnerabilidades divididas por la severidad y a su vez, todas las corregidas, recorriendo las del primer grupo y quitando las que se encuentren en el segundo para quedarse solamente con las <b>no corregidas</b>, para devolver precisamente esas.<br> <br>
  
- **http://127.0.0.1:8000/api/fix**<br>
  Es el endpoint que <b>almacena las vulnerabilidades corregidas</b>. Para marcar vulnerabilidades como corregidas, es necesario cambiar el método a POST, si se venían probando los otros métodos, y en la parte del Body, pasar un único parámetro, que en el código de la función en el controlador se aclara que su nombre es 'cveIDs', el cual recibe separados por coma, sin espacios, los IDs de todas las vulnerabilidades que se quieran poner como corregidas. Si se envía correctamente el parámetro con el o los IDs a marcar como vulnerabilidades corregidas, se mostrará un mensaje de éxito.<br>
Ejemplo de uso: <br><br>
![image](https://github.com/ustemberg/api_nist_meli/assets/103837698/09a93df6-86d9-4371-88c5-db65dafa9925)
