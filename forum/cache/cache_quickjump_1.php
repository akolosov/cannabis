<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><?php echo $lang_common['Jump to'] ?>

					<br /><select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="Разработка и внедрение ПО">
							<option value="11"<?php echo ($forum_id == 11) ? ' selected="selected"' : '' ?>>Разработка новых программ</option>
							<option value="1"<?php echo ($forum_id == 1) ? ' selected="selected"' : '' ?>>Система документооборота</option>
							<option value="2"<?php echo ($forum_id == 2) ? ' selected="selected"' : '' ?>>Система КУ</option>
							<option value="3"<?php echo ($forum_id == 3) ? ' selected="selected"' : '' ?>>Система 1С:Предприятие</option>
							<option value="5"<?php echo ($forum_id == 5) ? ' selected="selected"' : '' ?>>Программы пользователей</option>
						</optgroup>
						<optgroup label="Системное администрирование">
							<option value="4"<?php echo ($forum_id == 4) ? ' selected="selected"' : '' ?>>Компьютеры и сети</option>
							<option value="8"<?php echo ($forum_id == 8) ? ' selected="selected"' : '' ?>>Принтеры и ксероксы</option>
						</optgroup>
						<optgroup label="Экономические вопросы - Бюджетирование">
							<option value="12"<?php echo ($forum_id == 12) ? ' selected="selected"' : '' ?>>Общие вопросы бюджетирования</option>
						</optgroup>
						<optgroup label="Разговоры на прочие темы">
							<option value="10"<?php echo ($forum_id == 10) ? ' selected="selected"' : '' ?>>Б.О.Р. Избранное</option>
							<option value="13"<?php echo ($forum_id == 13) ? ' selected="selected"' : '' ?>>Дизайн как интерпретация вербальной кинематики</option>
							<option value="7"<?php echo ($forum_id == 7) ? ' selected="selected"' : '' ?>>Курилка</option>
							<option value="6"<?php echo ($forum_id == 6) ? ' selected="selected"' : '' ?>>О работе форума</option>
					</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
