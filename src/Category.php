<?php 
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