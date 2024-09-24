<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
 use App\Http\Controllers\RoleController;
 use App\Http\Controllers\StoreController;
 use App\Http\Controllers\PermissionController;
 use App\Http\Controllers\ItemCategoryController;
 use App\Http\Controllers\ItemController;
 use App\Http\Controllers\PurchaseController;
 use App\Http\Controllers\DarSHowRoomController;
use App\Http\Controllers\DodomaSHowRoomController;
use App\Http\Controllers\DarSaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentsInDarShowRoom;
use App\Http\Controllers\PaymentsInDodomaShowRoom;
use App\Http\Controllers\DodomaSalesController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\PurchasesReportController;
use App\Http\Controllers\StockHistory;
use App\Http\Controllers\SalesController;


// Public Routes
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized user! Please login to access the API'], 401);
})->name('login');


// Authentication Routes
Route::post('/add_user', [AuthController::class, 'register']);
Route::post('/login_v2', [AuthController::class, 'login']);
Route::post('/log-activity', [AuthController::class, 'logUserActivity']);
Route::post('/logout', [AuthController::class, 'logout']);

// Protected Routes
Route::middleware(['auth:sanctum', 'token.expiration'])->group(function () {

    Route::get('/get_login_username', [AuthController::class, 'getLoggedUserName']);
     Route::get('/users', [AuthController::class, 'users']);
    Route::get('/user_profile', [AuthController::class, 'getLoggedUserProfile']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('/user/permissions', [PermissionController::class, 'getUserPermissions']);

    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
     // Fetch user by ID
    Route::get('/users/{user_id}', [AuthController::class, 'getUserById']);
    // Update user by ID
    Route::put('/users/{user_id}', [AuthController::class, 'updateUser']);
    // Delete user by ID
    Route::delete('/users/{user_id}', [AuthController::class, 'deleteUser']);


    //store route
Route::apiResource('stores', StoreController::class);



//Item category route
Route::apiResource('itemCategories', ItemCategoryController::class);
Route::get('/countCategory', [ItemCategoryController::class, 'countCategory']);

//items route
Route::apiResource('/all_items', ItemController::class);
Route::post('items/searchv2', [ItemController::class, 'searchByNamev2']);
// Route to for get item report by date between
Route::post('itemsReport', [ItemController::class, 'filterByDateRangev2']);
Route::get('/countDistribution', [ItemController::class, 'countItemsWithDistribution']);
Route::get('/countAllItems', [ItemController::class, 'countAllItems']);



//All sales
Route::apiResource('/sales', SalesController::class);
 Route::get('creditSales', [SalesController::class, 'creditSales']);
Route::get('cashSales', [SalesController::class, 'cashSales']);
Route::get('salesReports', [SalesController::class, 'salesReportByDate']);

//Sales in dar salaaam
Route::apiResource('/sales-dar', SalesController::class);
 Route::get('creditSales-dar', [DarSaleController::class, 'creditSalesDar']);
Route::get('cashSales-dar', [DarSaleController::class, 'cashSalesDar']);
//route Filter sales report dar_showRoom by date
Route::get('darReport', [DarSaleController::class, 'filterByDateDar']);

//Sales in dodoma salaaam
Route::apiResource('/sales-dodoma', SalesController::class);
 Route::get('creditSales-dodoma', [DodomaSalesController::class, 'creditSalesDodoma']);
Route::get('cashSales-dodoma', [DodomaSalesController::class, 'cashSalesDodoma']);
//route Filter sales report dar_showRoom by date
Route::get('dodomaReport', [DarSaleController::class, 'filterByDateDodoma']);



//item dar show room
Route::apiResource('/darshwroom', DarSHowRoomController::class);
// Route to search items by name
Route::post('items/search', [DarSHowRoomController::class, 'searchByName']);
// Route to for get item report by date between in dar
Route::post('itemsReporDar', [DarSHowRoomController::class, 'filterByDateRange']);



//item dodoma showRoom
Route::apiResource('/dodomashworoom', DodomaSHowRoomController::class);
// Route to search items by name
Route::post('items/searchv1', [DodomaShowRoomController::class, 'searchByNamev1']);
// Route to for get item report by date between in dodoma
Route::post('itemsReporDodoma', [DodomaShowRoomController::class, 'filterByDateRangev1']);


//purchases route
Route::apiResource('purchases', PurchaseController::class);
Route::get('/countPurchases', [PurchaseController::class, 'purchaseCount']);
// Route to get purchase report by date between
Route::post('purchases/filter-by-date', [PurchaseController::class, 'filterByDateRangev3']);
// Route to filter purchases by optional attributes
Route::post('purchases/filter-byname', [PurchaseController::class, 'filterByAttributesv3']);



});
