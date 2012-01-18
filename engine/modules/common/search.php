<form  width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&<?= getURIParams(); ?>" method="POST">
<table width="100%" align="center">
<caption class="caption">Поиск</caption>
<tr>
<td align="right">Поиск по:</td>
<td align="center"><select name="x_search_by" style=" width : 100%; ">
<option value="1" <?= ((X_SEARCH_BY == 1)?"selected":""); ?> />По всем
<option value="2" <?= ((X_SEARCH_BY == 2)?"selected":""); ?> />По наименованию
<option value="3" <?= ((X_SEARCH_BY == 3)?"selected":""); ?> />По содержанию
</select></td>
<td align="right">Что искать:</td>
<td align="center"><input type="text" name="x_search_value" value="<?= ((defined("X_SEARCH_VALUE"))?X_SEARCH_VALUE:""); ?>" size="35" style=" width : 100%; " /></td>
<td><input title="Искать по реквизитам" type="submit" name="submit" value=" Искать " style=" width : 100%; " /></td>
</tr>
</table>
</form>
<?php
	$where = NULL;

	if ((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> '')) {
		if (X_SEARCH_BY == '1') {
			$where .= "((upper(name) like '%".mb_strtoupper(X_SEARCH_VALUE)."%') or (upper(description) like '%".mb_strtoupper(X_SEARCH_VALUE)."%'))";
		} elseif (X_SEARCH_BY == '2') {
			$where .= "(upper(name) like '%".mb_strtoupper(X_SEARCH_VALUE)."%')";
		} elseif (X_SEARCH_BY == '3') {
			$where .= "(upper(description) like '%".mb_strtoupper(X_SEARCH_VALUE)."%')";
		}
	}

?>