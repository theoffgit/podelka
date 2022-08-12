<?php
function defaultProjectInfoPage()
{
    include_once __DIR__."/classes/AlefCore.php";
    if (defined("DIESEL_SERVER") && DIESEL_SERVER == 'client' && $_REQUEST['pass']!="alefalef") {
        die("В доступе отказано");
    }

    $html = <<<'EOTEOTEOTEOT'
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<link rel="shortcut icon" href="http://l.alef.im/img/fav.png" type="image/png">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>AQ-Белогуб (1.0)</title>

<!-- Bootstrap -->
<link href="resources/info/css/bootstrap.min.css" rel="stylesheet">
<link href="resources/info/css/styles.css" rel="stylesheet">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
  <header>
	   <div class="row row-first">
		   <div class="col-lg-2 col-md-3 col-sm-3 col-xs-7"><img src="resources/info/Logo.png" class="img-responsive logo" alt=""></div>
		   <div class="title">
			   <h1>Документация API<br>
				   <span>AQ-Белогуб (1.0)</span>
			   </h1>
		   </div>
	   </div>
  </header>
  <div class="main">
   <div class="row row-second">
	   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2>ОБЩАЯ ИНФОРМАЦИЯ</h2>
			<p>Это API сгенерировано автоматически на основании описания на <b>AlefApiScript</b>. Редактор доступен по адресу: <a
				   href="https://aq.alef.im">https://aq.alef.im</a></p>
		   <p>Клиентские библиотеки могут быть созданы в этом же генераторе, с использованием <b>AlefApiScript</b> данного протокола, который вы можете скопировать, нажав <a href="#" class="copyAAS">сюда</a>.</p>
		   <p>Вы можете воспользоваться функцией "красивое отображение ответа сервера", если добавить к имени метода восклицательный знак. Например, если метод API доступен по ссылке
			   <a href="#">?alef_action=test</a>, то вызвав <a href="#">?alef_action=test!</a> вы получите ответ в форматированном виде, с читаемыми русскими символами и с включенным отображением ошибок и предупреждений PHP.</p>
		<p>Так же для отладки сервера вы можете использовать:<br/> 
		<a href="engine/apitester.php%ALEF_PASS%">API Tester</a> — скрипт, который поозволяет удобно формировать запросы к серверу (включая загрузку файлов).<br/>
		<a href="engine/tamper.php%ALEF_PASS%">Tamper</a> — инструмент, позволяющий подставлять индивидуальные ответы на запросы.</p>

	   </div>
   </div>
   <div class="row row-second">
	   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2>ОПИСАНИЕ ПРОЕКТА</h2>
			Сервер для ППС
	   </div>
   </div>
   <div class="row row-second">
	   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<h2>ИСТОРИЯ ВЕРСИЙ</h2>
			<ul><li><b>1.0</b> — Первоначальная версия</li></ul>
	   </div>
   </div>

<pre id='aas_pre' style='display:none;'>{"name":"AQ-Белогуб","api_fcm_server_key":"","api_android_application_id":"","api_android_inapp_client_id":"","api_android_inapp_client_secret":"","api_android_inapp_client_refresh_token":"","api_ios_inapp_password":"","url":"dev https://belogub.alef.dev","description":"Сервер для ППС","settings":"","version":"1.0","security":0,"git":{"old_server":"","new_server":"https://bitbucket.org/alefdevelopment/aq-belogub.git # /api","objc":"","swift":"","java":"","kotlin":""},"version_history":[{"version":"1.0","description":"Первоначальная версия"}],"models":[],"requests":[{"name":"Получение температуры в Москве","action_id":"getMoscowTemperature","description":"Возвращает текущую температуру в Москве в градусах Цельсия","example_request":"?alef_action=getMoscowTemperature","ttl":"0","example_response":[{"status":0,"temperature":1},{"status":7,"user_message":"Ошибка получения температуры"}],"auth":"1","method":"get","regular_or_login_or_logout":"0","params":[]},{"name":"Деавторизация","action_id":"signOut","description":"Разлогинивает пользователя в системе","example_request":"?alef_action=signOut","ttl":"0","example_response":{"status":0},"auth":"0","method":"post","regular_or_login_or_logout":"2","params":[]},{"name":"Авторизация","action_id":"signIn","description":"Проверяет введённые данные и авторизует пользователя в системе","example_request":"?alef_action=signIn&email=info@alef.im&password=123456","ttl":"0","example_response":[{"status":0},{"status":2,"user_message":"E-mail не может быть пустым"},{"status":3,"user_message":"Пароль не может быть пустым"},{"status":4,"user_message":"Указан некорректный E-mail"},{"status":6,"user_message":"Указан неверный E-mail или пароль"}],"auth":"0","method":"post","regular_or_login_or_logout":"1","params":[{"name":"email","type":"string","description":"E-mail","example":"info@alef.im"},{"name":"password","type":"string","description":"Пароль","example":"123456"}]},{"name":"Регистрация","action_id":"signUp","description":"Проверяет введённые данные и регистрирует пользователя в системе или выдаёт ошибку, если пользователь с таким E-mail уже зарегистрирован","example_request":"?alef_action=signUp&name=Иван&email=info@alef.im&password=123456","ttl":"0","example_response":[{"status":0},{"status":1,"user_message":"Имя не может быть пустым"},{"status":2,"user_message":"E-mail не может быть пустым"},{"status":3,"user_message":"Пароль не может быть пустым"},{"status":4,"user_message":"Указан некорректный E-mail"},{"status":5,"user_message":"Пользователь с таким E-mail уже зарегистрирован"}],"auth":"0","method":"post","regular_or_login_or_logout":"0","params":[{"name":"name","type":"string","description":"Имя","example":"Иван"},{"name":"email","type":"string","description":"E-mail","example":"info@alef.im"},{"name":"password","type":"string","description":"Пароль","example":"123456"}]}   ,{"name":"Создание новости","action_id":"createNews","description":"Создает новость","example_request":"?alef_action=createNews&title=Новость&text=Текст Новости","ttl":"0","example_response":[{"status":0},{"status":1,"user_message":"Название новости не может быть пустым"},{"status":2,"user_message":"Текст новости не может быть пустым"}],"auth":"0","method":"post","regular_or_login_or_logout":"0","params":[{"name":"title","type":"string","description":"Название новости","example":"Название новости"},{"name":"text","type":"string","description":"Текст новости","example":"Текст новости"}]}]}</pre>


</div>
	  <div class="row bottom-row">
		  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			  <h2>ЗАПРОСЫ</h2>
			  <ul class="items">
				
						  <li><span>Получение температуры в Москве</span><span>getMoscowTemperature()</span></li>
	                      <div class="in_li">
	                          <p><b>Требует авторизации</b>: Да</p>
	                          <p><b>Метод</b>: get</p>
	                          <p><b>Пример запроса</b>: <a href="?alef_action=getMoscowTemperature">?alef_action=getMoscowTemperature</a></p>
	                          <p><b>Описание</b>: Возвращает текущую температуру в Москве в градусах Цельсия</p>
							  <p class="request"><b>Параметры</b>: Нет</p>
	                          
	                          <p class="request"><b>Пример(-ы) ответа</b>:</p>
	                          <div class="code-div">
	                          	<pre>[
    {
        &quot;status&quot;: 0,
        &quot;temperature&quot;: 1
    },
    {
        &quot;status&quot;: 7,
        &quot;user_message&quot;: &quot;Ошибка получения температуры&quot;
    }
]</pre>
							  </div>

	                      </div>
						  <li><span>Деавторизация</span><span>signOut()</span></li>
	                      <div class="in_li">
	                          <p><b>Требует авторизации</b>: Нет</p>
	                          <p><b>Метод</b>: post</p>
	                          <p><b>Пример запроса</b>: <a href="?alef_action=signOut">?alef_action=signOut</a></p>
	                          <p><b>Описание</b>: Разлогинивает пользователя в системе</p>
							  <p class="request"><b>Параметры</b>: Нет</p>
	                          
	                          <p class="request"><b>Пример(-ы) ответа</b>:</p>
	                          <div class="code-div">
	                          	<pre>{
    &quot;status&quot;: 0
}</pre>
							  </div>

	                      </div>
						  <li><span>Авторизация</span><span>signIn($email, $password)</span></li>
	                      <div class="in_li">
	                          <p><b>Требует авторизации</b>: Нет</p>
	                          <p><b>Метод</b>: post</p>
	                          <p><b>Пример запроса</b>: <a href="?alef_action=signIn&amp;email=info@alef.im&amp;password=123456">?alef_action=signIn&email=info@alef.im&password=123456</a></p>
	                          <p><b>Описание</b>: Проверяет введённые данные и авторизует пользователя в системе</p>
							  <p class="request"><b>Параметры</b>: </p>
	                          						  <div class='code-div'><pre>email (string); // E-mail. Пример: info@alef.im
password (string); // Пароль. Пример: 123456</pre></div> 
	                          <p class="request"><b>Пример(-ы) ответа</b>:</p>
	                          <div class="code-div">
	                          	<pre>[
    {
        &quot;status&quot;: 0
    },
    {
        &quot;status&quot;: 2,
        &quot;user_message&quot;: &quot;E-mail не может быть пустым&quot;
    },
    {
        &quot;status&quot;: 3,
        &quot;user_message&quot;: &quot;Пароль не может быть пустым&quot;
    },
    {
        &quot;status&quot;: 4,
        &quot;user_message&quot;: &quot;Указан некорректный E-mail&quot;
    },
    {
        &quot;status&quot;: 6,
        &quot;user_message&quot;: &quot;Указан неверный E-mail или пароль&quot;
    }
]</pre>
							  </div>

	                      </div>
						  <li><span>Регистрация</span><span>signUp($name, $email, $password)</span></li>
	                      <div class="in_li">
	                          <p><b>Требует авторизации</b>: Нет</p>
	                          <p><b>Метод</b>: post</p>
	                          <p><b>Пример запроса</b>: <a href="?alef_action=signUp&amp;name=Иван&amp;email=info@alef.im&amp;password=123456">?alef_action=signUp&name=Иван&email=info@alef.im&password=123456</a></p>
	                          <p><b>Описание</b>: Проверяет введённые данные и регистрирует пользователя в системе или выдаёт ошибку, если пользователь с таким E-mail уже зарегистрирован</p>
							  <p class="request"><b>Параметры</b>: </p>
	                          						  <div class='code-div'><pre>name (string); // Имя. Пример: Иван
email (string); // E-mail. Пример: info@alef.im
password (string); // Пароль. Пример: 123456</pre></div> 
	                          <p class="request"><b>Пример(-ы) ответа</b>:</p>
	                          <div class="code-div">
	                          	<pre>[
    {
        &quot;status&quot;: 0
    },
    {
        &quot;status&quot;: 1,
        &quot;user_message&quot;: &quot;Имя не может быть пустым&quot;
    },
    {
        &quot;status&quot;: 2,
        &quot;user_message&quot;: &quot;E-mail не может быть пустым&quot;
    },
    {
        &quot;status&quot;: 3,
        &quot;user_message&quot;: &quot;Пароль не может быть пустым&quot;
    },
    {
        &quot;status&quot;: 4,
        &quot;user_message&quot;: &quot;Указан некорректный E-mail&quot;
    },
    {
        &quot;status&quot;: 5,
        &quot;user_message&quot;: &quot;Пользователь с таким E-mail уже зарегистрирован&quot;
    }
]</pre>
							  </div>

	                      </div>
			 </ul>
		  </div>
	  </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="resources/info/js/bootstrap.min.js"></script>
<script src="resources/info/js/main.js"></script>

</body>
</html>
EOTEOTEOTEOT;

    if (isset($_REQUEST['pass'])) {
        $html = str_replace("%ALEF_PASS%", "?pass=".$_REQUEST['pass'], $html);
    } else {
        $html = str_replace("%ALEF_PASS%", "", $html);
    }
    echo $html;
    exit;
};
