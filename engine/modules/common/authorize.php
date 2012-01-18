    <?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" action="<?= (defined('BACKTO')?urldecode(BACKTO):"/?module=common/authorized"); ?>" method="POST">
         <table align="center" class="form">
          <tr>
           <td class="form" width="20%" align="right">Пользователь:</td>
           <td class="form" align="left"><select name="user_code" style=" width : 100%; ">
           <?php
           	$users = $connection->execute('select * from accounts_without_groups_list where is_active = true')->fetchAll();
           	foreach ($users as $user) {
           		print "<option value=\"".$user['id']."\" ".($user['id'] == USER_CODE?"selected":"")." />".$user['name'];
           	}
           ?>
           </select></td>
	  </tr>
	  <tr>
	   <td class="form" align="right">Пароль:</td>
	   <td class="form" align="left"><input type="password" name="user_passwd" value="" size="15" style=" width : 100%; " /></td>
	  </tr>
	  <tr>
	   <td class="form" align="left" colspan="2"><input title="Войти в систему" type="submit" name="submit" value="Войти в систему" style=" width : 100%; " class="button" /></td>
	  </tr>
	 </table>
	</form>
