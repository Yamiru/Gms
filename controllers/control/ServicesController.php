<?php 

class ServicesController extends BaseController{
    
    public function actionIndex(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Услуги";
    
    if(parent::isAjax()){
        
        
    }else{
    
    $getServices = $this->db->query('SELECT * FROM ga_services');
    $getServices = $getServices->fetchAll();
    
    $content = $this->view->renderPartial("control/services/index", ['services' => $getServices]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    }
    
    }
    
    public function actionAdd(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Добавление новой услуги";
    
    if(parent::isAjax()){
    
    $servicesName = strip_tags($_POST['servicesName']);
    $servicesType = strip_tags($_POST['servicesType']);
    $servicesPeriod = 0;
    if($servicesType != 'razz') $servicesPeriod = strip_tags($_POST['servicesPeriod']);        
        
        
    $servicesPrice = strip_tags($_POST['servicesPrice']);  
        
    $params = '';
    $servicesParams = $_POST['servicesParams'];
    if(!empty($servicesParams)){
        $colors = explode("\n", $servicesParams);
        $params = json_encode($colors);
    }
    
    $this->db->exec("INSERT INTO ga_services (name, type, period, price, params) 
    VALUES('$servicesName', '$servicesType', '$servicesPeriod','$servicesPrice', '$params')");
   
     $answer['status'] = "success";
     $answer['success'] = "Услуга успешно добавлена";
     exit(json_encode($answer)); 
        
    }else{
     
    $content = $this->view->renderPartial("control/services/add", []);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    }
    
    }
    
    
    public function actionEdit(){
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();    
        
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");

    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    
    $title = "Изменение услуги #$id";
     
    $getInfoServices = $this->db->prepare('SELECT * FROM ga_services WHERE id = :id');
    $getInfoServices->execute(array(':id' => $id));
    $getInfoServices = $getInfoServices->fetch();
    if(empty($getInfoServices)) parent::ShowError(404, "Страница не найдена!");
    
    
    if(parent::isAjax()){
        
    $servicesName = strip_tags($_POST['servicesName']);
    $servicesType = strip_tags($_POST['servicesType']);
    $servicesPeriod = 0;
    if($servicesType != 'razz') $servicesPeriod = strip_tags($_POST['servicesPeriod']);        
        
        
    $servicesPrice = strip_tags($_POST['servicesPrice']);  
        
    $params = '';
    $servicesParams = $_POST['servicesParams'];
    if(!empty($servicesParams)){
        $colors = explode("\n", $servicesParams);
        $params = json_encode($colors);
    }


    $sql = "UPDATE ga_services SET name = :name, type = :type, period = :period, price = :price, params = :params WHERE id= :id";
    $update = $this->db->prepare($sql);                                        
    $update->bindParam(':name', $servicesName);   
    $update->bindParam(':type', $servicesType); 
    $update->bindParam(':period', $servicesPeriod);
    $update->bindParam(':price', $servicesPrice);
    $update->bindParam(':params', $params);
    $update->bindParam(':id', $id); 
    $update->execute();     
    
    $answer['status'] = "success";
    $answer['success'] = "Услуга успешно изменена";
    exit(json_encode($answer)); 
        
    }else{
  


    $content = $this->view->renderPartial("control/services/edit", ['data' => $getInfoServices]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    
    }
    }
    
    public function actionRemove(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    if(parent::isAjax()){
    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
    $sql = "DELETE FROM ga_services WHERE id =  :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);   
    $stmt->execute();   
    }
    
        
    }
    
    
    public function actionView(){
    $user = new User();
    if(!$user->isAuth()){
        header("Location: /control/index");
    }
    $getUserPrfile = $user->getProfile();
    if($getUserPrfile['role'] != 'admin')  parent::ShowError(404, "Страница не найдена!");
    
    $getSettings = $this->db->query('SELECT * FROM ga_settings');
    $settings = $getSettings->fetch();
    
    $title = "Просмотр логов услуги";
    
    if(isset($_GET['id'])) $id = (int)$_GET['id']; else $id = '';
     
    $getInfoServices = $this->db->prepare('SELECT * FROM ga_services WHERE id = :id');
    $getInfoServices->execute(array(':id' => $id));
    $getInfoServices = $getInfoServices->fetchAll();
    if(empty($getInfoServices)) parent::ShowError(404, "Страница не найдена!");
    
  

    $getLogsPay = $this->db->query("SELECT * FROM `ga_pay_logs` WHERE `status` = 'paid' and content LIKE ('%id_user\":\"".$id."%')");
    $getLogsPay = $getLogsPay->fetchAll();

    $countLogs = $this->db->query("SELECT * FROM `ga_pay_logs` WHERE `status` = 'paid' and content LIKE ('%id_services\":\"6%')");
    $count = $countLogs->rowCount();
    echo $count;
    die();
    $pagination = new Pagination();
    $per_page = 15;
    $result = $pagination->create(array('per_page' => $per_page, 'count' => $count));
   
    
    $sumPrice = 0;
    foreach($getLogsPay as $row){
        $decode = json_decode($row['content'], true);
        $logs[] = array("id" => $row['id'], 'type' => $decode['type'] ,'id_services' => $decode['id_services'], 'price' => $decode['price'], 'date' => $row['date_create'], 'id_server' => $decode['id_server']);
        $sumPrice =+ $sumPrice+$row['price']; 
      
    }
    


    
    $content = $this->view->renderPartial("control/services/view", ['data' => $getInfoServices, 'logsPay' => $logs, 'sumPrice' => $sumPrice]);
 
    $this->view->render("control/main", ['content' => $content, 'title' => $title]);   
    
    
    }
}