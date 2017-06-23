<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8;" http-equiv="Content-Type">
    <title>Drugdu Member Control Panel</title>
    <meta name="viewport" content="width=device-width,initial-scale=0.6,minimum-scale=0.6,maximum-scale=1,user-scalable=no" />
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-siteapp">
</head>
<body>

    <table border="1">
        <tr>
            <th>邮箱</th>
            <th>销售内容</th>
        </tr>
        <?php
        foreach($list as $d => $v){
        ?>
        <tr>
            <td><?php echo $v['email'];?></td>
            <td><?php echo $v['sell'];?></td>
        </tr>
            <?php
        }?>
    </table>
</body>
</html>
