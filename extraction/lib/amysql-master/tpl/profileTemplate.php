<div class="amysqlProfile">
    <div class="amysqlProfileTitle">MySQL query information:</div>
    <?php if ($profiler['queriesData']): ?>
    <table class="queryInfoTable">
        <thead>
            <tr>
                <th class="amysqlNo">No.</th>
                <th class="amysqlQuery">Query</th>
                <th class="amysqlTime">Time</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($profiler['queriesData'] as $index => $row): ?>
        <tr class="amysqlQueryRow amysqlSingleRow amysql-row-<?php echo $index ?>">
            <td class="amysqlNo"><?php echo ($index + 1) ?></td>
            <td class="amysqlQuery"><?php echo htmlspecialchars($row['query'], ENT_QUOTES, $encoding) ?></td>
            <td class="amysqlTime"><?php printf('%.6f', $row['time']) ?></td>
        </tr>
        <?php endforeach ?>
        <tr class="amysqlQueryRow amysqlTotalRow amysql-row-<?php echo $index ?>">
            <td class="amysqlNo">Total</td>
            <td class="amysqlQuery">&nbsp;</td>
            <td class="amysqlTime"><?php printf('%.6f', $profiler['totalTime']) ?></td>
        </tr>
        </tbody>
    </table>
    <?php else: ?>
    <div class="amysqlNoQueries">No MySQL queries were run</div>
    <?php endif ?>
</div>
