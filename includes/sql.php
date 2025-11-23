<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
function find_by_groupLevel($level) {
  global $db;
  $level = $db->escape($level);

  $sql  = "SELECT * FROM user_groups WHERE group_level = '{$level}' LIMIT 1";
  $result = find_by_sql($sql);

  if ($result) {
    return $result[0]; // devuelve el primer registro como array
  } else {
    return false;
  }
}

  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn()):
            $session->msg('d','Por favor Iniciar sesión...');
            redirect('index.php', false);
      //if Group status Deactive
     elseif(!$login_level || $login_level['group_status'] === '0'):
           $session->msg('d','Este nivel de usuario esta inactivo!');
           redirect('home.php',false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "¡Lo siento!  no tienes permiso para ver la página.");
            redirect('home.php', false);
        endif;

     }
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
function join_product_table(){
    global $db;
    $sql  = "SELECT p.id, p.brand, p.buy_price, p.sale_price, p.media_id, p.model, p.date, ";
    $sql .= "c.name AS categorie, m.file_name AS image ";
    $sql .= "FROM products p ";
    $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
    $sql .= "LEFT JOIN media m ON m.id = p.media_id ";
    $sql .= "ORDER BY p.id ASC";
    return find_by_sql($sql);
}



  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

  //  function find_product_by_title($product_name){
  //    global $db;
  //    $p_name = remove_junk($db->escape($product_name));
  //    $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
  //    $result = find_by_sql($sql);
  //    return $result;
  //  }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
/* Function for Finding product info with variants by product title
/* Request coming from ajax.php
/*--------------------------------------------------------------*/




  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
function find_recent_product_added($limit){
  global $db;
  $sql  = "SELECT p.id, p.brand, p.model, p.sale_price, p.media_id, ";
  $sql .= "c.name AS categorie, m.file_name AS image ";
  $sql .= "FROM products p ";
  $sql .= "LEFT JOIN categories c ON c.id = p.categorie_id ";
  $sql .= "LEFT JOIN media m ON m.id = p.media_id ";
  $sql .= "ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);

  return find_by_sql($sql);
}


/* ==========================================================
   PRODUCTO MÁS VENDIDO (NUEVO SISTEMA CON detalle_venta)
   ========================================================== */
function find_higest_saleing_product($limit) {
    global $db;

    $limit = (int)$limit;

    $sql = "SELECT 
              dv.producto_id,
              CONCAT(p.brand,' ',p.model) AS name,
              c.name AS category_name,
              SUM(dv.cantidad) AS totalQty,
              SUM(dv.cantidad * dv.precio_unitario) AS totalSold
            FROM detalle_venta dv
            LEFT JOIN products p ON dv.producto_id = p.id
            LEFT JOIN categories c ON p.categorie_id = c.id
            GROUP BY dv.producto_id
            ORDER BY totalSold DESC
            LIMIT {$limit}";

    return find_by_sql($sql);
}


 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;

  $sql = "
    SELECT 
      dv.id,
      dv.cantidad,
      dv.precio_unitario,
      s.date,
      CONCAT(p.brand,' ',p.model) AS product_name,
      c.name AS category_name,
      (dv.cantidad * dv.precio_unitario) AS total_sale
    FROM detalle_venta dv
    LEFT JOIN sales s ON dv.venta_id = s.id
    LEFT JOIN products p ON dv.producto_id = p.id
    LEFT JOIN categories c ON p.categorie_id = c.id
    ORDER BY s.date DESC
    LIMIT " . $db->escape((int)$limit);

  return find_by_sql($sql);
}



/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date, $end_date) {
  global $db;

  $start_date = date("Y-m-d", strtotime($start_date));
  $end_date   = date("Y-m-d", strtotime($end_date));

  $sql = "SELECT 
            s.id AS venta_id,
            s.date,
            p.id AS producto_id,
            p.brand,
            p.model,
            p.buy_price,
            p.sale_price,
            d.cantidad,
            (d.cantidad * p.sale_price) AS total_linea,
            (d.cantidad * p.buy_price) AS total_compra_linea
          FROM sales s
          INNER JOIN detalle_venta d ON s.id = d.venta_id
          INNER JOIN products p ON p.id = d.producto_id
          WHERE DATE(s.date) BETWEEN '{$start_date}' AND '{$end_date}'
          ORDER BY s.date DESC, s.id DESC";

  return find_by_sql($sql);
}


/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
  $sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
  return find_by_sql($sql);
}


function find_last_sales_full($limit = 5) {
    global $db;
    $limit = (int)$limit;

    $sql = "SELECT
                s.id AS sale_id,
                s.date,
                dv.cantidad,
                dv.precio_unitario,
                CONCAT(p.brand, ' ', p.model) AS product_name,
                c.name AS category_name
            FROM sales s
            LEFT JOIN detalle_venta dv ON s.id = dv.venta_id
            LEFT JOIN products p ON dv.producto_id = p.id
            LEFT JOIN categories c ON p.categorie_id = c.id
            ORDER BY s.date DESC, s.id DESC";

    $result = find_by_sql($sql);

    // AGRUPAR POR VENTA
    $ventas = [];
    foreach ($result as $row) {
        $id = $row['sale_id'];

        if (!isset($ventas[$id])) {
            $ventas[$id] = [
                'sale_id' => $id,
                'date' => $row['date'],
                'items' => [],
                'total' => 0
            ];
        }

        // Agregar producto
        $ventas[$id]['items'][] = [
            'name' => $row['product_name'],
            'category' => $row['category_name'],
            'cantidad' => $row['cantidad'],
            'precio' => $row['precio_unitario'],
            'subtotal' => $row['cantidad'] * $row['precio_unitario']
        ];

        // Acumular total
        $ventas[$id]['total'] += $row['cantidad'] * $row['precio_unitario'];
    }

    // Tomar solo las últimas 5 ventas completas
    return array_slice($ventas, 0, $limit, true);
}


?>
