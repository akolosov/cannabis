<?php
if (defined("MESSAGE")) {
    print "<div class=\"message\">".MESSAGE."</div>\n";
} elseif (defined("SEARCH")) {
    print "<div class=\"search\">".SEARCH."</div>\n";
}
?>
