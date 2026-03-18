<?php

declare(strict_types=1);

// Start output buffering to allow header redirects in included pages
if (ob_get_level() === 0) {
    ob_start();
}

// Define constant to indicate this is included in dashboard
define('DASHBOARD_INCLUDED', true);

// dashboard.php
require_once 'header.php';
require_once 'slidebar.php';
?>

<div class="container-fluid <?php echo ($page == 'edit_category' || $page == 'add_category') ? 'full-width-content' : 'd-flex justify-content-center'; ?>" style="<?php echo ($page == 'edit_category' || $page == 'add_category') ? 'width:100%; margin-left:0; padding: 40px;' : 'margin-left:270px; width:calc(100% - 270px);'; ?> min-height: 100vh;">
    <?php
    $page = $_GET['page'] ?? 'home';
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    ?>

    <?php
    $allowed_pages = [
        'home',
        'users',
        'user_order',
        'category',
        'report',
        'setting',
        'add_category',
        'edit_category',
        'manage_category',
        'user_order',
        'total_order',
        'register',
        'users_register',
        'users_list'
    ];

    // Handle manage_category actions (add, update, delete) before displaying any page
    if ($page == 'manage_category' && !empty($action)) {
        require_once "../peges/manage_category.php";
        // After processing, redirect to category page - using header for proper POST handling
        exit;
    }

    if (in_array($page, $allowed_pages)) {
        if ($page == 'category') {
            require_once "../peges/category_food.php";
        } elseif ($page == 'add_category') {
            require_once "../peges/add_category.php";
        } elseif ($page == 'edit_category' && !empty($id)) {
            require_once "../peges/edit_category.php";
        } elseif ($page == 'user_order') {
            require_once "../peges/user_order.php";
        } elseif ($page == 'total_order') {
            require_once "../peges/total_order.php";
        } elseif ($page == 'register' || $page == 'users_register') {
            require_once "../peges/users_register.php";
        } elseif ($page == 'users_list') {
            require_once "../peges/users_list.php";
        } else {
            require_once "../peges/$page.php";
        }
    } else {
        require_once "../peges/home.php";
    }
    ?>

</div>

<!--begin::Footer-->
<div class="justify-content-center">
<?php require_once 'footer.php'; ?>
</div>