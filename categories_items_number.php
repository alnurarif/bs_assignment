<?php
include('index.php');
$db = new DB();

$allCategories = Category::getAllWithItemsNumberDescending($db);
$categoriesToShow = [];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="margin:0 auto; width:900px;padding:0px">
        <div style="display: block;height: 30px;width:100%; text-align:center; clear:both; margin: 20px 0px 20px 0px">
            <a href="project_tree.php" style="line-height:30px; background:#cfcfcf;float:left; margin-right:20px;text-decoration:none; color: #000; padding:5px 15px 5px 15px">Category Tree</a>
            <a href="categories_items_number.php" style="line-height:30px; background:#cfcfcf;float:left; margin-left: 20px;text-decoration:none; color: #000; padding:5px 15px 5px 15px">Category Items Number</a>
        </div>
        <div style="display: block;clear:both; width:100%; margin:10px 0px 10px 0px;">
            <table style="width:100%">
                <thead>
                    <tr style="background:#cfcfcf">
                        <th>Category Name</th>
                        <th>Total Items</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                foreach($allCategories as $singleCategories){?>
                    <tr style="text-align:center; background:#e9e9e9">
                        <td><?= $singleCategories->category_name; ?></td>
                        <td><?= $singleCategories->item_number; ?></td>
                    </tr>
                <?php } ?>
                    
                </tbody>
            </table>
        </div>
    </div>
    
</body>
</html>