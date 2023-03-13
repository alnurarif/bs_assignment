<?php
include('start.php');
$db = new DB();

$allCategories = Category::getEverything($db);
$categoriesToShow = [];
foreach($allCategories as $singleCategory){
    if(!Category::hasParent($db, $singleCategory->Id)){
        $categoriesToShow[] = new Category($db, $singleCategory->Id);
    }
}

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
            <a href="index.php" style="line-height:30px; background:#cfcfcf;float:left; margin-right:20px;text-decoration:none; color: #000; padding:5px 15px 5px 15px">Category Tree</a>
            <a href="categories_items_number.php" style="line-height:30px; background:#cfcfcf;float:left; margin-left: 20px;text-decoration:none; color: #000; padding:5px 15px 5px 15px">Category Items Number</a>
        </div>
        <div style="display: block;clear:both; width:100%; margin:10px 0px 10px 0px;">
        <?php 
        function loopObjects($objects, $i, $indent = 0) {
            $l = 0;
            foreach ($objects as $object) {
                
                echo "<p style='margin-left:".($indent*25)."px; margin-top:0px; margin-bottom: 0px; line-height:22px; font-size:16px'>".str_repeat("  ", $indent) ."<img src='./images/arrow.png' style='width:15px;'/>". $object->Name ." - (".($object->totalItemNumber + $object->getEveryItemNumberIncludingChild()).")</p>". PHP_EOL;

                if (!empty($object->subCategories)) {
                    loopObjects($object->subCategories, $object->Id, $indent + 1);
                }
            }
        }
        
        foreach($categoriesToShow as $categoryToShow){
            $indent = 0; 
            echo "<p style='margin-left:".($indent*25)."px; margin-top:0px; margin-bottom: 0px; line-height:22px; font-size:16px'>".str_repeat("  ", $indent) ."<img src='./images/arrow.png' style='width:15px;'/>". $categoryToShow->Name." - (".$categoryToShow->totalItemNumber+$categoryToShow->getEveryItemNumberIncludingChild().")</p>". PHP_EOL;

            loopObjects($categoryToShow->subCategories,1, ++$indent);
        }
        
        
        ?>
        </div>
        
    </div>
</body>
</html>