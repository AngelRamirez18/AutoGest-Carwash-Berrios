use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\ServicioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta principal
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard general
    Route::get('/dashboard', function () {
        $user = Auth::user();
        switch ($user->rol) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'empleado':
                return redirect()->route('empleado.dashboard');
            case 'cliente':
                return redirect()->route('cliente.dashboard');
            default:
                return redirect('/');
        }
    })->name('dashboard');

    // Vehículos
    Route::resource('vehiculos', VehiculoController::class);

    // Servicios
    Route::prefix('servicios')->name('servicios.')->group(function () {
        Route::get('/', [ServicioController::class, 'index'])->name('index');
        Route::get('/categoria/{categoria}', [ServicioController::class, 'porCategoria'])->name('categoria');
        Route::get('/{id}', [ServicioController::class, 'show'])->name('show');
    });

    // Notificaciones
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        // Listar notificaciones
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        
        // Notificaciones por usuario
        Route::get('/usuario/{usuarioId}', [NotificacionController::class, 'porUsuario'])->name('porUsuario');
        
        // Marcar notificaciones
        Route::put('/{id}/leida', [NotificacionController::class, 'marcarLeida'])->name('marcarLeida');
        Route::put('/{id}/noleida', [NotificacionController::class, 'marcarNoLeida'])->name('marcarNoLeida');
        Route::put('/usuario/{usuarioId}/leer-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('marcarTodasLeidas');
        
        // Conteo de notificaciones
        Route::get('/usuario/{usuarioId}/contar-no-leidas', [NotificacionController::class, 'contarNoLeidas'])->name('contarNoLeidas');
        
        // Notificaciones de citas
        Route::post('/cita/creada', [NotificacionController::class, 'crearCita'])->name('crearCita');
        Route::post('/cita/confirmada', [NotificacionController::class, 'confirmarCita'])->name('confirmarCita');
        Route::post('/cita/cancelada', [NotificacionController::class, 'cancelarCita'])->name('cancelarCita');
        Route::post('/cita/recordatorio', [NotificacionController::class, 'recordatorioCita'])->name('recordatorioCita');
    });

    // Perfil
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'edit'])->name('edit');
        Route::post('/actualizar', [PerfilController::class, 'update'])->name('update');
        Route::post('/actualizar-ajax', [PerfilController::class, 'updateAjax'])->name('update-ajax');
    });

    // Configuración
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [PerfilController::class, 'configuracion'])->name('index');
        Route::post('/actualizar-email', [PerfilController::class, 'updateEmail'])->name('update-email');
        Route::post('/actualizar-password', [PerfilController::class, 'updatePassword'])->name('update-password');
    });
});

// Rutas de Admin
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard-data', [AdminController::class, 'getDashboardData'])->name('dashboard.data');

        // Usuarios
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [AdminController::class, 'usuarios'])->name('index');
            Route::post('/', [AdminController::class, 'storeUsuario'])->name('store');
            Route::get('/all', [AdminController::class, 'getAllUsers'])->name('all');
            Route::put('/{usuario}', [AdminController::class, 'update'])->name('update');
            Route::delete('/{usuario}', [AdminController::class, 'destroy'])->name('destroy');
            Route::get('/{usuario}/registros', [AdminController::class, 'getUserRecords'])->name('registros');
            Route::post('/bulk-activate', [AdminController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [AdminController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/bulk-delete', [AdminController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // Citas
        Route::prefix('citas')->name('citas.')->group(function () {
            Route::get('/create', [AdminController::class, 'createCita'])->name('create');
            Route::post('/', [AdminController::class, 'storeCita'])->name('store');
        });

        // Reportes
        Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');

        // Servicios
        Route::prefix('servicios')->name('servicios.')->group(function () {
            Route::get('/', [ServicioController::class, 'adminIndex'])->name('index');
            Route::get('/crear', [ServicioController::class, 'create'])->name('create');
            Route::post('/', [ServicioController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [ServicioController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ServicioController::class, 'update'])->name('update');
            Route::delete('/{id}', [ServicioController::class, 'destroy'])->name('destroy');
        });
    });

// Rutas de Empleado
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':empleado'])
    ->prefix('empleado')
    ->name('empleado.')
    ->group(function () {
        Route::get('/dashboard', [EmpleadoController::class, 'dashboard'])->name('dashboard');
        Route::get('/citas', [EmpleadoController::class, 'citas'])->name('citas');
        Route::get('/servicios', [ServicioController::class, 'empleadoIndex'])->name('servicios.index');
    });

// Rutas de Cliente
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':cliente'])
    ->prefix('cliente')
    ->name('cliente.')
    ->group(function () {
        Route::get('/dashboard', [ClienteController::class, 'dashboard'])->name('dashboard');
        Route::get('/vehiculos', [ClienteController::class, 'vehiculos'])->name('vehiculos');
        Route::get('/citas', [ClienteController::class, 'citas'])->name('citas');
        Route::get('/mis-vehiculos', [ClienteController::class, 'misVehiculosAjax'])->name('mis-vehiculos-ajax');
        Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    });

// Rutas de prueba
Route::get('/debug', function () {
    $user = App\Models\Usuario::first();
    return [
        'user' => $user->toArray(),
        'vehiculos' => $user->vehiculos->toArray(),
        'citas' => $user->citas->toArray()
    ];
});

Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'user' => Auth::user(),
        'session_config' => [
            'driver' => config('session.driver'),
            'cookie' => config('session.cookie'),
            'secure' => config('session.secure')
        ]
    ];
});

Route::get('/test-middleware', function () {
    return response()->json([
        'message' => 'Middleware test passed',
        'user' => Auth::user(),
        'role' => Auth::user()->rol ?? null
    ]);
})->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':cliente']);