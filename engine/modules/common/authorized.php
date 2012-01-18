<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<h3 align="center">Краткое описание Системы:</h3>
<!-- 
<h4>Базовые понятия:</h4>
<ul>
	<li style=" text-align: justify; "><strong>Проект</strong> - совокупность процессов в рамках конкретного проекта, имеющего конкретные цели и ресурсы.
	В контексте Системы проектом является отдельно взятое линейное подразделение компании и содержит все процессы
	(активные/выполняемые, завершенные), происходящие/происходившие в рамках данного подразделения</li>
	<li style=" text-align: justify; "><strong>Процесс</strong> - совокупность действий (ограниченных временными рамками), направленых на достижение
	определённых целей с использованием ресурсов (люди, деньги, МЦ и т.п.) и получения некого результата (тоже, возможно,
	ресурса для других процессов). Например: прохождение Служебной записки - процесс, имеющий вход (некую проблему/задачу),
	последовательность действий (Написание, Регистрация, Назначение ответственного, Исполнение, Обработка результата)
	и выход (Результат выполнения). Процесс имеет Инициатора (пользователь, создавший процесс),	временные рамки
	(Дата-Время начала и окончания процесса)</li>
	<li style=" text-align: justify; "><strong>Действие</strong> - неделимая единица процесса, интерактивное или неинтерактивное.
	Интерактивные действия подразумевают какое-либо взаимодействие пользователя и компьютера (например набор текста, выбор неких
	элементов из списка и т.п.) от имени текущего пользователя. Например: написание Служебной записки поразумевает следующие интерактивные
	действия:<br />
	 - <i>определение Получателя;</i><br />
	 - <i>описания проблемы или просьбы;</i><br />
	 - <i>отправки дальше по инстанции</i>.<br /><br />
	Если действие <i>интерактивное</i>, то после его запуска на	экране появляется некая форма, позволяющая пользователю ввести нужные
	для текущего действия данные и потом нажать	кнопку "Отправить дальше" для завершения текущего действия и продолжения выполнения процесса.
	<i>Неинтерактивные</i> действия	выполняются Системой автоматически от имени текущего или назначенного пользователя. Каждое действие	имеет
	Испонителя, указывающего какой конкретно пользователь будет выполнять это действие или выполнял (если действие уже завершено)
	и временные рамки (Дата-Время начала и окончания действия)</li>
</ul>
-->
<h4>Описание модулей:</h4>
<ul>
	<li><a href="?module=common/forum" class="navbutton"><img src="images/comment.png" /><strong>Форум</strong></a> - модуль для:
	<ul>
		<li style=" text-align: justify; ">общения пользователей системы</li>
		<li style=" text-align: justify; ">обсуждения текущих процессов</li>
		<li style=" text-align: justify; ">обсуждения вопросов возникших в процессе работы</li> 
		<li style=" text-align: justify; ">обсуждения прочих программ, используемых в рамках компании</li>
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/delegations/list" class="navbutton"><img src="images/delegations.png" /><strong>Делигирование</strong></a> - модуль для:
	<ul>
		<li style=" text-align: justify; ">передачи прав на исполнение (<img src="images/add.png" />) документов другому пользователю на определённый срок
		<li style=" text-align: justify; ">редактирования (<img src="images/edit_icon.png" />) существующих делигирований прав на исполнение документов
		<li style=" text-align: justify; ">отмены (<img src="images/delete_icon.png" />) дальнейшего делигирований прав на исполнение документов
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/today/list" class="navbutton"><img src="images/today.png" /><strong>Текущие</strong></a> - модуль для:
	<ul>
		<li style=" text-align: justify; ">запуска (<img src="images/play.png" />) текущих документов пользователя
		<li style=" text-align: justify; ">приостановки (<img src="images/pause.png" />) текущих документов пользователя
		<li style=" text-align: justify; ">печати (<img src="images/print.png" />) текущих документов пользователя
		<li style=" text-align: justify; ">просмотра информации (<img src="images/template.png" />) о текущих документах пользователя
		<li style=" text-align: justify; ">просмотра хронологии (<img src="images/time.png" />) движения текущих документов пользователя
		<li style=" text-align: justify; ">просмотра истории (<img src="images/date.png" />) движения текущих документов пользователя
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/templates/list" class="navbutton"><img src="images/templates.png" /><strong>Шаблоны</strong></a> - модуль для <i>(с группировкой по предприятиям)</i>:
	<ul>
		<li style=" text-align: justify; ">создания новых (инициации) (<img src="images/add.png" />) документов
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/drafts/list" class="navbutton"><img src="images/drafts.png" /><strong>Черновики</strong></a> - модуль для:
	<ul>
		<li style=" text-align: justify; ">редактирования (<img src="images/play.png" />) новых документов пользователя и их последующей отправки
		<li style=" text-align: justify; ">удаления (<img src="images/trashcan.png" />) новых документов пользователя
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/inboxes/list" class="navbutton"><img src="images/inboxes.png" /><strong>Входящие</strong></a> - модуль для <i>(с группировкой по предприятиям)</i>:
	<ul>
		<li style=" text-align: justify; ">редактирования (<img src="images/play.png" />) текущих документов пользователя и их последующей отправки
		<li style=" text-align: justify; ">печати (<img src="images/print.png" />) текущих документов пользователя
		<li style=" text-align: justify; ">просмотра информации (<img src="images/template.png" />) о текущих документах пользователя
		<li style=" text-align: justify; ">просмотра хронологии (<img src="images/time.png" />) движения текущих документов пользователя
		<li style=" text-align: justify; ">просмотра истории (<img src="images/date.png" />) движения текущих документов пользователя
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/outboxes/list" class="navbutton"><img src="images/outboxes.png" /><strong>Исходящие</strong></a> - модуль для <i>(с группировкой по предприятиям)</i>:
	<ul>
		<li style=" text-align: justify; ">печати (<img src="images/print.png" />) документов пользователя, прошедших через пользователя и находящихся в работе
		<li style=" text-align: justify; ">просмотра информации (<img src="images/template.png" />) о документах пользователя, прошедших через пользователя и находящихся в работе
		<li style=" text-align: justify; ">просмотра хронологии (<img src="images/time.png" />) движения документа, прошедших через пользователя и находящихся в работе
		<li style=" text-align: justify; ">просмотра истории (<img src="images/date.png" />) движения документа, прошедших через пользователя и находящихся в работе
	</ul>
</ul>
<ul>
	<li><a href="?module=runtime/archives/list" class="navbutton"><img src="images/archives.png" /><strong>Архив</strong></a> - модуль для <i>(с группировкой по предприятиям)</i>:
	<ul>
		<li style=" text-align: justify; ">печати (<img src="images/print.png" />) документов пользователя, работа над которыми уже завершена 
		<li style=" text-align: justify; ">просмотра информации (<img src="images/template.png" />) о документах пользователя, работа над которыми уже завершена
		<li style=" text-align: justify; ">просмотра хронологии (<img src="images/time.png" />) движения документа, работа над которыми уже завершена
		<li style=" text-align: justify; ">просмотра истории (<img src="images/date.png" />) движения документа, работа над которыми уже завершена
	</ul>
</ul>
		