<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
   <table class="access_list">
      <tr>
      <td>Username</td>
      <td>Description</td>
      </tr>
<?    foreach ($users as $user): ?>
         <tr>
         <td><a href="<?=$path;?>/UserEdit?name=<?=$user['name'];?>"><?=$user['name'];?></a></td>
         <td><?=$user['desc'];?></td>
         </tr>
<?    endforeach; ?>
   </table>
   <hr>
   <form method="GET" action="<?=$path;?>/UserAdd">
      <p class="centered"><input type="submit" value="Add User"></p>
   </form>
