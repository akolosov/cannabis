<div class="caption">Отчеты по документам</div>
<ul class="tree" id="reports_tree" style=" display : none; ">
	<li class="roottreeitem"><a href="#"></a>Отчеты по Служебным запискам:
		<ul>
			<br /><li class="treeitem bold action" title="Расширенный отчет о ходе выполнения Служебных записок по времени выполнения, отклонению от плана" onClick="document.location.href = '/?module=runtime/reporters/fourth/list&report_name=fourth';">
			Расширенный отчет о ходе выполнения Служебных записок
			</li>
			<br /><li class="treeitem bold action" title="Учет выполнения Служебных записок по времени выполнения, отклонению от плана" onClick="document.location.href = '/?module=runtime/reporters/first/list&report_name=first';">
			Учет выполнения Служебных записок
			</li>
			<br /><li class="treeitem bold action" title="Учет сроков исполнения Служебных записок по отклонению от плана" onClick="document.location.href = '/?module=runtime/reporters/third/list&report_name=third';">
			Учет сроков исполнения Служебных записок
			</li>
			</li>
			<br /><li class="treeitem bold action" title="Отчет о состоянии текущих задач по Служебным запискам" onClick="document.location.href = '/?module=runtime/reporters/fifth/list&report_name=fifth';">
			Отчет о состоянии текущих задач
			<br />
		</ul>
	</li>
	<li class="roottreeitem"><a href="#"></a>Отчеты по Телефонным заявкам:
		<ul>
			<br /><li class="treeitem bold action" title="Отчет о состоянии текущих задач по Телефонным заявкам" onClick="document.location.href = '/?module=runtime/reporters/sixth/list&report_name=sixth';">
			Отчет о состоянии текущих задач по Телефонным заявкам
			</li>
			<br />
		</ul>
	</li>
	<li class="roottreeitem"><a href="#"></a>Отчеты по прочим документам:
		<ul>
			<br /><li class="treeitem bold action" title="Учет выполнения Процедур документооборота по времени и количеству выполнения" onClick="document.location.href = '/?module=runtime/reporters/second/list&report_name=second';">
			Учет выполнения Процедур документооборота
			</li>
			<br />
		</ul>
	</li>
</ul>

<script>
<!--
	var reports_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 2,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var reports_tree = new CompleteMenuSolution;
	reports_tree.initMenu('reports_tree', reports_tree_options);
//-->
</script>