<?php
    if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) {
        return false;
    }
    $sources = ConnectionManager::sourceList();
    if (!isset($logs)) {
        $logs = array();
        foreach ($sources as $source) {
            $db =& ConnectionManager::getDataSource($source);
            if (!$db->isInterfaceSupported('getLog')) {
                continue;
            }
            $logs[$source] = $db->getLog();
        }
    }
?>
<style type="text/css" media="screen">
 .element-sql-dump {
   margin: 10px 20px;
   background-color: #ffffff;
   color: #000000;
 }
 table.cake-sql-log tr td, table.cake-sql-log tr:nth-child(2n) td{
   vertical-align: top;
   padding: 4px 8px;
   font-family: Monaco,  Consolas,  "Courier New",  monospaced;
   border-bottom: 1px solid #ddd;
 }
 table.cake-sql-log tr:nth-child(2n) td {
   background: #f5f5f5
 }
 table.cake-sql-log tr.sql-update td, table.cake-sql-log tr:nth-child(2n).sql-update td, #updatelink{
   background-color: #FFE0A8;
   font-weight: bold;
 }
 table.cake-sql-log tr.sql-insert td, table.cake-sql-log tr:nth-child(2n).sql-insert td, #insertlink{
   color: white;
   background-color: #AEAEFF;
   font-weight: bold;
 }
 table.cake-sql-log tr.sql-delete td, table.cake-sql-log tr:nth-child(2n).sql-delete td, #deletelink{
   background-color: #FBB;
   font-weight: bold;
 }
 .sql-keyword {
   font-weight: bold;
   color: #069;
 }
 .sql-function {
   font-weight: bold;
   color: #FF1493;
 }
 .sql-string {
   color: #00F;
 }
 .sql-schema {
   color: green;
 }
</style>

<script type="text/javascript">
  if (typeof jQuery != "undefined") {
      $(function () {
          $('#firstlink').focus();
          $('#alllink').bind("click", function () { $(".cake-sql-log tr").show(); });
          $('#selectlink').bind("click", function () { $(".cake-sql-log tr").show();$(".cake-sql-log tr:not(.sql-select)").hide(); });
          $('#updatelink').bind("click", function () { $(".cake-sql-log tr").show();$(".cake-sql-log tr:not(.sql-update)").hide(); });
          $('#insertlink').bind("click", function () { $(".cake-sql-log tr").show();$(".cake-sql-log tr:not(.sql-insert)").hide(); });
          $('#deletelink').bind("click", function () { $(".cake-sql-log tr").show();$(".cake-sql-log tr:not(.sql-delete)").hide(); });
          if ($(".cake-sql-log tr.sql-SELECT")[0]) {
              $('#selectlink').val("SELECT("+$(".cake-sql-log tr.sql-SELECT").length+")");
          } else {
              $('#selectlink').hide();
          }
          if ($(".cake-sql-log tr.sql-UPDATE")[0]) {
              $('#updatelink').val("UPDATE("+$(".cake-sql-log tr.sql-UPDATE").length+")");
          } else {
              $('#updatelink').hide();
          }
          if ($(".cake-sql-log tr.sql-INSERT")[0]) {
              $('#insertlink').val("INSERT("+$(".cake-sql-log tr.sql-INSERT").length+")");
          } else {
              $('#insertlink').hide();
          }
          if ($(".cake-sql-log tr.sql-DELETE")[0]) {
              $('#deletelink').val("DELETE("+$(".cake-sql-log tr.sql-DELETE").length+")");
          } else {
              $('#deletelink').hide();
          }
      });
  }
</script>
<div class="element-sql-dump">

  <input type="button" id="alllink" onclick="return false" value="ALL QUERY" />
  <input type="button" id="selectlink" onclick="return false" value="SELECT" />
  <input type="button" id="updatelink" onclick="return false" value="UPDATE" />
  <input type="button" id="insertlink" onclick="return false" value="INSERT" />
  <input type="button" id="deletelink" onclick="return false" value="DELETE" />

  <?php
      $errorcount = 0;
      foreach ($logs as $source => $logInfo) {
          foreach ($logInfo['log'] as $k => $i) {
              if (!empty($i['error'])) {
                  $errorcount++;
              }
          }
      }
  ?>
  <?php if ($errorcount > 0) { ?>
    <div class="alert alert-danger">
      <?php echo $errorcount ?> SQL Error(s) was found.
    </div><?php /* .alert alert-danger */?>
  <?php }  ?>
  <?php
      foreach ($logs as $source => $logInfo) {
          $text = $logInfo['count'] > 1 ? 'queries' : 'query';
  ?>
    <table class="table cake-sql-log" id="cakeSqlLog_%s" summary="Cake SQL Log" cellspacing="0" border="0" style="font-size:small;">
      <caption>(<?php echo $source ?>) <?php echo $logInfo['count'] ?> <?php echo $text ?> took <?php echo $logInfo['time'] ?> ms</caption>
      <thead>
        <tr>
          <th>Nr</th>
          <th>Query</th>
          <th>Error</th>
          <th>Affected</th>
          <th>Num. rows</th>
          <th>Took (ms)</th>
          <!-- <th>Results</th> -->
        </tr>
      </thead>
      <tbody>
        <?php
            foreach ($logInfo['log'] as $k => $i) { ?>
          <?php
              $sql = $i['query'];
              $class = "";
              if (preg_match("@^UPDATE@s", $sql)) {
                  $class .= " sql-update";
              } elseif (preg_match("@^DELETE@s", $sql)) {
                  $class .= " sql-delete";
              } elseif (preg_match("@INSERT@s", $sql)) {
                  $class .= " sql-insert";
              } elseif (preg_match("@SHOW@s", $sql)) {
                  $class .= " sql-show";
              }
              if (!empty($i['error'])) {
                  $class .=  " danger";
              }
              $sql = preg_replace("@( WHERE| FROM| LIMIT| ORDER| SET| VALUES| GROUP| LEFT JOIN)@i", "<br />\n$1", $sql);
              $sql = preg_replace("@(CREATE TABLE|ALTER TABLE|SELECT|UPDATE|INSERT|DELETE|SHOW|FROM|AS|LEFT JOIN|WHERE|GROUP BY|ORDER BY|ASC|DESC|asc|desc|LIMIT|START TRANSACTION|COMMIT)@i", "<span class=\"sql-keyword\">$1</span>", $sql);
              $sql = preg_replace("@COUNT\((.*?)\)@i", "<span class=\"sql-function\">COUNT($1)</span>", $sql);
              $sql = preg_replace("@DATE_FORMAT\(([^)]*?)\)@i", "<span class=\"sql-function\">DATE_FORMAT($1)</span>", $sql);
              $sql = preg_replace("@'(.*?)'@i", "<span class=\"sql-string\">'$1'</span>", $sql);
              $sql = preg_replace("@`(.*?)`@i", "<span class=\"sql-schema\">`$1`</span>", $sql);
              //pr($sql);
          ?>
          <tr class="<?php echo $class ?>">
            <td><?php echo ($k + 1) ?></td>
            <td><?php echo $sql ?></td>
            <td><?php echo $i['error'] ?></td>
            <td style="text-align: right"><?php echo $i['affected'] ?></td>
            <td style="text-align: right"><?php echo $i['numRows'] ?></td>
            <td style="text-align: right"><?php echo $i['took'] ?></td>
            <!-- <td></td> -->
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php }?>
</div>

<?php
    /* Local Variables: */
    /* coding: utf-8 */
    /* End: */

