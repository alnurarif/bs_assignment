<?php
class DB {
    protected $host = 'localhost';
    protected $username = 'root';
    protected $password = '';
    protected $dbname = 'bs_23';
    protected $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
}

class Category {
    private $db;
    private $isParent = false;
    private $table = 'category';
    public $Id;
    public $Name;
    public $Number;
    public $SystemKey;
    public $Note;
    public $Priority;
    public $Disabled;
    public $subCategories = [];
    public $subCategoriesNumber = 0;
    public $totalItemNumber = 0;
    public $itemNumberIncludingSubCategories = 0;
    public $parentId = 0;
    public function __construct($db, $id) {
        $this->db = $db;
        $this->getById($id);
        $this->setSubCategories();
        $this->setSubCategoriesNumber();
        $this->setTotalItemNumber();
        $this->setParentId();

    }
    public static function getEverything($db){
        $query = "SELECT * FROM category";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public static function getAllWithItemsNumberDescending($db){
        $query = "SELECT c.Name category_name, count(icr.Id) item_number FROM category c 
        LEFT JOIN item_category_relations icr ON icr.categoryId = c.Id 
        GROUP BY c.Id
        ORDER BY count(icr.Id) DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public static function getOnlyRoots($db){
        $query = "SELECT * FROM category";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function hasParent($db, $id){
        $query = "SELECT * FROM catetory_relations WHERE categoryId = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        return (count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) ? true : false;
    }
    public static function checkIsParent($db, $id){
        $query = "SELECT * FROM catetory_relations WHERE categoryId = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        return (count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) ? false : true;
    }
    public function getById($id){
        $query = "SELECT * FROM {$this->table} WHERE Id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $obj = $stmt->fetch(PDO::FETCH_OBJ);
        $this->setObj($obj);
        return $obj;
    }
    private function setObj($obj){
        $this->Id = $obj->Id;
        $this->Name = $obj->Name;
        $this->Number = $obj->Number;
        $this->SystemKey = $obj->SystemKey;
        $this->Note = $obj->Note;
        $this->Priority = $obj->Priority;
        $this->Disabled = $obj->Disabled;
        return;
    }
    
    public function getId(){
        return $this->Id;
    }
    public function getName(){
        return $this->Name;
    }
    public function getNumber(){
        return $this->Number;
    }
    public function getSystemKey(){
        return $this->SystemKey;
    }
    public function getNote(){
        return $this->Note;
    }
    public function getPriority(){
        return $this->Priority;
    }
    public function getDisabled(){
        return $this->Disabled;
    }
    public function setSubCategories($parent_id = 0) {
        $categories = array();

        $query = "SELECT c.Id category_id, c.Name category_name, cr.ParentcategoryId parent_category_id, COUNT(ic.id) total_item FROM catetory_relations cr 
        LEFT JOIN category c ON c.Id = cr.categoryId
        LEFT JOIN item_category_relations ic ON ic.categoryId = cr.categoryId
        WHERE ParentcategoryId = ? GROUP BY c.Id;";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->Id]);
        $result =  $stmt->fetchAll(PDO::FETCH_OBJ);
        $total_item_per_category = 0;

        foreach ($result as $mainCategory) {
            array_push($this->subCategories, new Category($this->db, $mainCategory->category_id));
        }
    }
    private function setSubCategoriesNumber(){
        $this->subCategoriesNumber = count($this->subCategories);
    }
    private function setTotalItemNumber(){
        $query = "SELECT COUNT(ic.id) total_item FROM item_category_relations ic WHERE ic.categoryId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->Id]);
        $result =  $stmt->fetch(PDO::FETCH_OBJ);
        $this->totalItemNumber = $result->total_item;
    }
    private function setParentId(){
        $query = "SELECT ParentcategoryId FROM catetory_relations cr WHERE cr.categoryId = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->Id]);
        $result =  $stmt->fetch(PDO::FETCH_OBJ);
        $this->parentId = (isset($result->ParentcategoryId)) ? $result->ParentcategoryId : 0;
    }
    public function getEveryItemNumberIncludingChild(){
        $total = 0;
        foreach($this->subCategories as $subCategory){
            if(is_array($subCategory->subCategories) && count($subCategory->subCategories) > 0){

                $total += $subCategory->totalItemNumber + $subCategory->getEveryItemNumberIncludingChild();
            }else{
                $total += $subCategory->totalItemNumber;

            }

        }
        return $total;
    }
}

class CategoryTreeWithItemNumberGenerator{
    protected $categories;

    public function __construct($categories){
        $this->categories = $categories;

    }
    public function settleWithItemNumber($categories){
        // foreach($categories as $category){
        //     $category->molla = 3;
        // }
        if ($categories) {
            foreach ($this->categories as $category) {
                if (is_array($category)) {
                    echo $category['total_item']."\n";
                    //
                    $this->settleWithItemNumber($category);
                    // $this->settleWithItemNumber($value, $indent . '--');
                } else {
                    echo $category['total_item']."\n";
                    //  Output
                    // echo "$indent $value \n";
                }
            }
        }
    }
    public function show(){
        echo "<pre>";
        echo json_encode($this->categories);
        echo "</pre>";
        
    }
}

class CategoryTree {
    private $db;
    private $isParent = false;
    public function __construct($db) {
        $this->db = $db;
    }
    private function checkIsParent($id){
        $query = "SELECT * FROM catetory_relations WHERE categoryId = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return (count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) ? false : true;
    }
    private function makeCategory($mainCategory){
        $category['id'] = $mainCategory['category_id'];
        $category['name'] = $mainCategory['category_name'];
        $category['parent_id'] = $mainCategory['parent_category_id'];
        $category['sub_categories'] = $this->getCategoryTreeForParentId($category['id']);
        $category['total_item'] = $mainCategory['total_item'] + $category['sub_categories']['total_item_per_category'];

        return $category;

    }
    public function getCategoryTreeForParentId($parent_id = 0) {
        $categories = array();

        $query = "SELECT c.Id category_id, c.Name category_name, cr.ParentcategoryId parent_category_id, COUNT(ic.id) total_item FROM catetory_relations cr 
        LEFT JOIN category c ON c.Id = cr.categoryId
        LEFT JOIN item_category_relations ic ON ic.categoryId = cr.categoryId
        WHERE ParentcategoryId = ? GROUP BY c.Id;";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$parent_id]);
        $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_item_per_category = 0;

        foreach ($result as $mainCategory) {
            $category = array();
            $category = $this->makeCategory($mainCategory);

            $total_item_per_category += $mainCategory['total_item'];

            $categories[$mainCategory['category_id']] = $category;
        }

        $sum_of_total = $this->getSumOfTotal($categories);

        foreach($categories as $category){
            $sum_of_total += $category['total_item'];
        } 
        $categories['total_item_per_category'] = $sum_of_total;
        return $categories;
    }
    private function getSumOfTotal($categories){
        $sum_of_total = 0;

        foreach($categories as $category){
            $sum_of_total += $category['total_item'];
        } 
        return $sum_of_total;
    }
}



$db = new DB();



// $category = new Category($db, 1);

// echo "<pre>";
// echo json_encode($category->subCategories);
// echo "</pre>"; 
// exit;


// Create a new instance of the CategoryTree class with a PDO database connection
$categoryTree = new CategoryTree($db);

// Get the category tree with item counts starting from the top-level categories
// $tree = $categoryTree->getCategoryTreeForParentId(2);




// $categoryAll = Category::getEverything($db);

// foreach($categoryAll as $singleCategory){
    
//     if(!Category::hasParent($db,$singleCategory['Id'])){
        
//         $tree = $categoryTree->getCategoryTreeForParentId($singleCategory['Id']);
        
//         echo "<pre>";
//         echo json_encode($tree);
//         echo "</pre>";
//     }
// }

