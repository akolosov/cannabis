<?php
	include_once($_SERVER['DOCUMENT_ROOT']."/config.php");
	setlocale(LC_ALL, "ru_RU");
	mb_internal_encoding(DEFAULT_CHARSET);

	require_once($_SERVER['DOCUMENT_ROOT']."/".HANDLERS_PATH."/commonFunctions.php");

	require_once($_SERVER['DOCUMENT_ROOT']."/".LIBRARY_PATH."/doctrine/Doctrine.PgSQL.compiled.php");
	$connection = Doctrine_Manager::connection(DATABASE_DSN);
?>
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="content-type" content="text/html; charset=<?= DEFAULT_CHARSET; ?>">
<script language="javascript">
<!--
var oEditor = window.parent.InnerDialogLoaded();
var sSelected = oEditor.FCK.EditorWindow.getSelection();
var oSelected = oEditor.FCKSelection.GetSelectedElement();

if (oSelected instanceof HTMLImageElement) {
	sSelected = "<img border='1' src='"+oSelected.src+"' />";
} else if (sSelected == '') {
	sSelected = null;
}

function onLoad() {
	oEditor.FCKLanguageManager.TranslatePage(document);

	if (sSelected) {
		window.parent.SetOkButton(true);
	} else {
		window.alert(oEditor.FCKLang.DocumentListNoSelection);
	}
}

function Ok() {
	var sValue = document.getElementById('docItems').value;

	if ((sValue) && (sSelected)) {
		oEditor.FCK.InsertHtml("<a href='<?= ($_SERVER['HTTPS']?"https://":"http://").$_SERVER['SERVER_NAME']; ?>"+sValue+"'>"+sSelected+"</a>");
		return true;
	} else {
		window.alert(oEditor.FCKLang.DocumentListNoSelection);
		return false;
	}
}
//-->
</script>
</head>
<body onload='onLoad();'>
	<table cellSpacing="0" cellPadding="0" align="center" border="0" style=" width : 100%; height : 100%; ">
		<tr>
			<td align="left" valign="top"><span fckLang="DocumentListDlgName">Document name</span></td>
		</tr>
		<tr>
			<td align="center" valign="top"><select id="docItems" size="15" style=" width : 100%; height : 100%; ">
			<?php
				$documents = $connection->execute('select * from public_documents_tree where is_active = true')->fetchAll();
				foreach ($documents as $document) {
					print "<option value='/?module=public/documents/edit&action=view&document_id=".$document['id']."&topic_id=".$document['topic_id']."' />".str_pad_html("", $document['level']).trim($document['name']);
				}
			?>
			</select></td>
		</tr>
	</table>
</body>
</html>