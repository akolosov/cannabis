<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><?php echo $lang_common['Jump to'] ?>

					<br /><select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="Разработка и внедрение ПО">
							<option value="11"<?php echo ($forum_id == 11) ? ' selected="selected"' : '' ?>>Разработка новых программ</option>
						</optgroup>
						<optgroup label="Экономические вопросы - Бюджетирование">
							<option value="12"<?php echo ($forum_id == 12) ? ' selected="selected"' : '' ?>>Общие вопросы бюджетирования</option>
						</optgroup>
						<optgroup label="Разговоры на прочие темы">
							<option value="10"<?php echo ($forum_id == 10) ? ' selected="selected"' : '' ?>>Б.О.Р. Избранное</option>
							<option value="13"<?php echo ($forum_id == 13) ? ' selected="selected"' : '' ?>>Дизайн как интерпретация вербальной кинематики</option>
					</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
